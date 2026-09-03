<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class GeminiService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY', '');
    }

    /**
     * Generate a structured, detailed summary of a transcript using Gemini 1.5 Flash.
     */
    public function generateSummary(array $transcript): string
    {
        $transcriptText = "";
        foreach ($transcript as $segment) {
            $transcriptText .= "{$segment['time_str']} {$segment['text']}\n";
        }

        $prompt = "You are an expert academic summarizer. Please write a detailed, comprehensive summary of the following YouTube video transcript.\n" .
                  "Format the summary with clean Markdown headings, clear sections, bullet points, and bold text for emphasis. " .
                  "Ensure that all major themes, technical points, arguments, and conclusions from the transcript are thoroughly covered.\n\n" .
                  "Transcript:\n" . $transcriptText;

        try {
            if (empty($this->apiKey)) {
                throw new Exception("Gemini API key is not configured. Please add GEMINI_API_KEY to your .env file.");
            }

            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $this->apiKey;

            $response = Http::timeout(120)->retry(3, 2000)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
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
                throw new Exception("Gemini API call failed (HTTP " . $response->status() . "): " . $body);
            }

            $data = $response->json();
            $summaryText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$summaryText) {
                throw new Exception("Unexpected response structure from Gemini API: " . json_encode($data));
            }

            return trim($summaryText);

        } catch (Exception $e) {
            // Fallback to OpenAI if key is available
            $openaiKey = config('services.openai.key') ?? env('OPENAI_API_KEY', '');
            if (!empty($openaiKey) && !str_contains($openaiKey, 'your_openai_api_key')) {
                try {
                    $response = Http::timeout(120)->withHeaders([
                        'Authorization' => 'Bearer ' . $openaiKey,
                        'Content-Type' => 'application/json',
                    ])->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-4o-mini',
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'You are an expert academic summarizer.'
                            ],
                            [
                                'role' => 'user',
                                'content' => $prompt
                            ]
                        ],
                        'temperature' => 0.5,
                    ]);

                    if ($response->successful()) {
                        $result = $response->json();
                        $summaryText = $result['choices'][0]['message']['content'] ?? null;
                        if ($summaryText) {
                            return trim($summaryText);
                        }
                    } else {
                        \Illuminate\Support\Facades\Log::error("Gemini summary fallback to OpenAI HTTP failed (" . $response->status() . "): " . $response->body());
                    }
                } catch (Exception $openaiEx) {
                    \Illuminate\Support\Facades\Log::error("Gemini summary fallback to OpenAI exception: " . $openaiEx->getMessage());
                }
            }

            // If fallback failed or wasn't configured, throw the original Gemini exception
            throw $e;
        }
    }
}
