<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Exception;

class YouTubeService
{
    /**
     * Extract the 11-character video ID from a YouTube URL.
     */
    public function extractVideoId(string $url): ?string
    {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        
        // If it's already an 11-character string, assume it's the ID
        if (strlen(trim($url)) === 11) {
            return trim($url);
        }

        return null;
    }

    /**
     * Fetch video title and thumbnail using oEmbed.
     */
    public function getVideoMetadata(string $videoId): array
    {
        $url = "https://www.youtube.com/watch?v=" . $videoId;
        try {
            $response = Http::get("https://www.youtube.com/oembed", [
                'url' => $url,
                'format' => 'json'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'title' => $data['title'] ?? 'YouTube Video',
                    'thumbnail_url' => $data['thumbnail_url'] ?? "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg",
                ];
            }
        } catch (Exception $e) {
            // Fallback to standard URL format if oEmbed fails
        }

        return [
            'title' => 'YouTube Video ' . $videoId,
            'thumbnail_url' => "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg",
        ];
    }

    /**
     * Run the python helper script to extract transcript JSON.
     */
    public function fetchTranscript(string $videoId): array
    {
        $scriptPath = base_path('get_transcript_api.py');
        $python = $this->getPythonPath();
        
        $env = array_merge($_ENV, $_SERVER);
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $env['SystemRoot'] = getenv('SystemRoot') ?: 'C:\\Windows';
            $env['PATH'] = getenv('PATH');
            $env['windir'] = getenv('windir') ?: 'C:\\Windows';
        }
        
        $result = Process::env($env)->run([$python, $scriptPath, $videoId]);

        $stdout = mb_convert_encoding($result->output(), 'UTF-8', 'UTF-8');

        if (!$result->successful()) {
            $decoded = json_decode($stdout, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['error'])) {
                throw new Exception("Python script error: " . $decoded['error']);
            }
            
            $errOut = mb_convert_encoding($result->errorOutput() ?: $result->output(), 'UTF-8', 'UTF-8');
            throw new Exception("Failed to execute transcript script [Python: {$python}]: " . $errOut . " (Exit code: " . $result->exitCode() . ")");
        }

        $data = json_decode($stdout, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to parse transcript JSON: " . json_last_error_msg() . ". Output was: " . $stdout);
        }

        if (isset($data['error'])) {
            throw new Exception("Transcript extraction failed: " . $data['error']);
        }

        return $data;
    }

    /**
     * Resolve the absolute path to the real python executable on Windows,
     * avoiding issues with Microsoft Store execution aliases.
     */
    protected function getPythonPath(): string
    {
        if ($envPath = env('PYTHON_PATH')) {
            return $envPath;
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $result = Process::run(['where.exe', 'python']);
            if ($result->successful()) {
                $paths = explode(PHP_EOL, trim($result->output()));
                foreach ($paths as $path) {
                    $path = trim($path);
                    if (!empty($path) && strpos($path, 'WindowsApps') === false && file_exists($path)) {
                        return $path;
                    }
                }
            }
            return 'python';
        }

        // On Linux / Hostinger, check common absolute paths directly first
        $candidatePaths = [
            '/usr/bin/python3',
            '/usr/local/bin/python3',
            '/usr/bin/python',
            '/opt/alt/python312/bin/python3',
            '/opt/alt/python311/bin/python3',
            '/opt/alt/python310/bin/python3',
            '/opt/alt/python39/bin/python3',
            '/opt/alt/python38/bin/python3',
        ];

        foreach ($candidatePaths as $candidate) {
            if (file_exists($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        // Try which command
        $result = Process::run(['which', 'python3']);
        if ($result->successful() && !empty(trim($result->output()))) {
            return trim($result->output());
        }
        
        $result = Process::run(['which', 'python']);
        if ($result->successful() && !empty(trim($result->output()))) {
            return trim($result->output());
        }

        return 'python3';
    }
}
