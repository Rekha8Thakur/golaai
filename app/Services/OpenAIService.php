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
     * Generate Q&A, MCQs, Notes, and Action Items from transcript and summary.
     * Uses OpenAI with automatic fallback to Gemini if OpenAI hits a quota or is unavailable.
     */
    public function generateStudyMaterials(array $transcript, string $summary): array
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
                  "Based on the following YouTube video summary and transcript, please generate high-quality learning resources: \n\n" .
                  "1. **Study Notes**: Comprehensive, in-depth study notes formatting with beautiful Markdown (tables, lists, subheadings, key terms). Put this in the 'notes' key.\n" .
                  "2. **Action Items**: A list of key action items, checklist items, or practical takeaways for the learner. Put this in the 'action_items' key (array of strings).\n" .
                  "3. **Q&A**: A list of 5-10 analytical or review questions with detailed, well-explained answers. Put this in the 'qa' key (array of objects with 'question' and 'answer').\n" .
                  "4. **MCQs**: A list of 5-10 multiple-choice questions for self-assessment. Put this in the 'mcqs' key. Each item must have: 'question' (string), 'options' (array of 4 strings), 'answer' (the exact text matching the correct option), and 'explanation' (string explaining why it is correct).\n\n" .
                  "Here is the Summary:\n{$summary}\n\n" .
                  "Here is the Transcript:\n{$transcriptText}\n\n" .
                  "You MUST return a JSON object with this exact structure:\n" .
                  "{\n" .
                  "  \"notes\": \"string (Markdown format)\",\n" .
                  "  \"action_items\": [\"string\", ...],\n" .
                  "  \"qa\": [\n" .
                  "    { \"question\": \"string\", \"answer\": \"string\" }\n" .
                  "  ],\n" .
                  "  \"mcqs\": [\n" .
                  "    { \"question\": \"string\", \"options\": [\"string\", \"string\", \"string\", \"string\"], \"answer\": \"string\", \"explanation\": \"string\" }\n" .
                  "  ]\n" .
                  "}\n\n" .
                  "CRITICAL: Any double quotes (\") used inside your string values (especially inside study notes, Q&A answers, or MCQ explanations) MUST be properly escaped as \\\" (e.g., \\\"Mission Allied 2.0\\\") to keep the JSON syntax valid. Never output raw unescaped double quotes inside a string value.\n" .
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
                throw new Exception("Failed to decode JSON from OpenAI response: " . json_last_error_msg() . "\nContent: " . $content);
            }

            return $decoded;

        } catch (Exception $e) {
            // Fall back to Gemini API if OpenAI fails
            return $this->generateUsingGeminiFallback($prompt);
        }
    }

    /**
     * Fallback to Gemini 2.5 Flash if OpenAI API call fails or quota is exceeded.
     */
    protected function generateUsingGeminiFallback(string $prompt): array
    {
        $geminiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY', '');
        
        if (empty($geminiKey)) {
            throw new Exception("OpenAI study material generation failed (exceeded quota or invalid key), and Gemini API fallback key is not configured.");
        }

        // Use gemini-2.5-flash as the fallback model
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $geminiKey;

        $response = Http::timeout(120)->withHeaders([
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
                'responseMimeType' => 'application/json'
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
            throw new Exception("Failed to decode JSON from Gemini fallback response: " . json_last_error_msg() . "\nContent: " . $content);
        }

        return $decoded;
    }

    /**
     * Clean markdown code block wraps or trailing characters from the JSON string.
     */
    protected function cleanJsonString(string $json): string
    {
        $json = trim($json);
        
        // Remove markdown code fences if present (e.g. ```json ... ``` or ``` ...)
        if (strpos($json, '```') === 0) {
            $json = preg_replace('/^```(?:json)?\s+/i', '', $json);
            $json = preg_replace('/\s+```$/', '', $json);
        }
        
        return trim($json);
    }
}
