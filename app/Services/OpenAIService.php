<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class OpenAIService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key') ?? env('OPENAI_API_KEY', '');
    }

    /**
     * Generate MCQs from transcript.
     * Uses OpenAI with automatic fallback to Gemini if OpenAI hits a quota or is unavailable.
     */
    public function generateMCQs(array $transcript): array
    {
        $transcriptText = "";
        foreach ($transcript as $segment) {
            $transcriptText .= "{$segment['time_str']} {$segment['text']}\n";
        }

        // Truncate to stay safely within context limits
        if (strlen($transcriptText) > 100000) {
            $transcriptText = substr($transcriptText, 0, 100000) . "\n... [TRUNCATED] ...";
        }

        $prompt = "You are a world-class instructional designer and learning assistant. " .
                  "Based on the following YouTube video transcript, please generate 5-10 high-quality multiple-choice questions (MCQs) for self-assessment. " .
                  "Put this in the 'mcqs' key. Each item must have: 'question' (string), 'options' (array of 4 strings), 'answer' (the exact text matching the correct option), and 'explanation' (string explaining why it is correct).\n\n" .
                  "Here is the Transcript:\n{$transcriptText}\n\n" .
                  "You MUST return a JSON object with this exact structure:\n" .
                  "{\n" .
                  "  \"mcqs\": [\n" .
                  "    { \"question\": \"string\", \"options\": [\"string\", \"string\", \"string\", \"string\"], \"answer\": \"string\", \"explanation\": \"string\" }\n" .
                  "  ]\n" .
                  "}\n\n" .
                  "CRITICAL: Any double quotes (\") used inside your string values (especially inside MCQ explanations) MUST be properly escaped as \\\" (e.g., \\\"Mission Allied 2.0\\\") to keep the JSON syntax valid. Never output raw unescaped double quotes inside a string value.\n" .
                  "Make sure it is valid JSON and all properties are present.";

        try {
            if (empty($this->apiKey) || str_contains($this->apiKey, 'your_openai_api_key')) {
                throw new Exception("OpenAI API key is missing or is placeholder.");
            }

            $response = Http::timeout(120)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an educational assistant that compiles study materials. Output only valid JSON matching the requested schema.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.4,
            ]);

            if (!$response->successful()) {
                $body = $response->body();
                if (str_contains($response->header('Content-Type'), 'html') || str_contains($body, '<html')) {
                    $body = "[HTML Error Page]";
                } else {
                    $body = mb_convert_encoding(substr($body, 0, 1000), 'UTF-8', 'UTF-8');
                }
                throw new Exception("OpenAI API call failed (HTTP " . $response->status() . "): " . $body);
            }

            $result = $response->json();
            $content = $result['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                throw new Exception("Unexpected response format from OpenAI API: " . json_encode($result));
            }

            $cleanContent = $this->cleanJsonString($content);
            $decoded = json_decode($cleanContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $safeContent = mb_convert_encoding(substr($content, 0, 1000), 'UTF-8', 'UTF-8');
                throw new Exception("Failed to decode JSON from OpenAI response: " . json_last_error_msg() . "\nContent: " . $safeContent);
            }

            return $decoded;

        } catch (Exception $e) {
            // Fall back to Gemini API if OpenAI fails
            return $this->generateMCQsUsingGeminiFallback($prompt);
        }
    }

    /**
     * Fallback to Gemini 2.5 Flash if OpenAI API call fails or quota is exceeded.
     */
    protected function generateMCQsUsingGeminiFallback(string $prompt): array
    {
        $geminiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY', '');
        
        if (empty($geminiKey)) {
            throw new Exception("OpenAI study material generation failed (exceeded quota or invalid key), and Gemini API fallback key is not configured.");
        }

        // Use gemini-flash-latest as the fallback model
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $geminiKey;

        $response = Http::timeout(120)->retry(3, 2000)->withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'mcqs' => [
                            'type' => 'ARRAY',
                            'items' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'question' => ['type' => 'STRING'],
                                    'options' => [
                                        'type' => 'ARRAY',
                                        'items' => ['type' => 'STRING']
                                    ],
                                    'answer' => ['type' => 'STRING'],
                                    'explanation' => ['type' => 'STRING']
                                ],
                                'required' => ['question', 'options', 'answer', 'explanation']
                            ]
                        ]
                    ],
                    'required' => ['mcqs']
                ]
            ]
        ]);

        if (!$response->successful()) {
            $body = $response->body();
            if (str_contains($response->header('Content-Type'), 'html') || str_contains($body, '<html')) {
                $body = "[HTML Error Page]";
            } else {
                $body = mb_convert_encoding(substr($body, 0, 1000), 'UTF-8', 'UTF-8');
            }
            throw new Exception("Gemini fallback also failed (HTTP " . $response->status() . "): " . $body);
        }

        $data = $response->json();
        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$content) {
            throw new Exception("Unexpected response structure from Gemini fallback: " . json_encode($data));
        }

        $cleanContent = $this->cleanJsonString($content);
        $decoded = json_decode($cleanContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $safeContent = mb_convert_encoding(substr($content, 0, 1000), 'UTF-8', 'UTF-8');
            throw new Exception("Failed to decode JSON from Gemini fallback response: " . json_last_error_msg() . "\nContent: " . $safeContent);
        }

        return $decoded;
    }

    /**
     * Clean markdown code block wraps or trailing characters from the JSON string.
     */
    protected function cleanJsonString(string $json): string
    {
        $json = trim($json);
        
        // Extract JSON content from markdown code fences if present
        if (preg_match('/```json\s*(.*?)\s*```/s', $json, $matches)) {
            $json = $matches[1];
        } elseif (preg_match('/```\s*(.*?)\s*```/s', $json, $matches)) {
            $json = $matches[1];
        }
        
        return trim($json);
    }
}
