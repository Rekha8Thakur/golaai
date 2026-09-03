<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessVideoFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'videos:process {file : The path to the text file containing YouTube URLs or IDs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batch processes YouTube URLs/IDs from a text file, generates MCQs, and imports them to the database.';

    /**
     * Execute the console command.
     */
    public function handle(
        \App\Services\YouTubeService $youtubeService,
        \App\Services\OpenAIService $openaiService
    ) {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return Command::FAILURE;
        }

        $content = file_get_contents($filePath);
        $rawUrls = preg_split('/[\n,\s]+/', $content);
        $urls = array_unique(array_filter(array_map('trim', $rawUrls)));

        if (empty($urls)) {
            $this->warn("No URLs found in file: {$filePath}");
            return Command::SUCCESS;
        }

        $total = count($urls);
        $this->info("Found {$total} unique video URLs/IDs to process.");

        $processed = 0;
        $successCount = 0;
        $failedCount = 0;
        $skippedCount = 0;

        foreach ($urls as $index => $url) {
            $processed++;
            $currentNum = $index + 1;
            
            $this->line("");
            $this->info("--------------------------------------------------");
            $this->info("Processing [{$currentNum}/{$total}]: {$url}");

            $videoId = $youtubeService->extractVideoId($url);

            if (!$videoId) {
                $this->error("Invalid URL or video ID: {$url}");
                $failedCount++;
                continue;
            }

            // Check database
            $existingVideo = \App\Models\Video::find($videoId);
            if ($existingVideo) {
                $this->comment("Skipping: Video already analyzed in database: '{$existingVideo->title}'");
                $skippedCount++;
                continue;
            }

            try {
                $this->line("1. Fetching transcript...");
                $transcript = $youtubeService->fetchTranscript($videoId);

                $this->line("2. Fetching metadata...");
                $metadata = $youtubeService->getVideoMetadata($videoId);
                $this->line("   Title: {$metadata['title']}");

                $this->line("3. Generating MCQs via OpenAI (with Gemini fallback)...");
                $materials = $openaiService->generateMCQs($transcript);

                $this->line("4. Saving to database...");
                \App\Models\Video::create([
                    'video_id' => $videoId,
                    'title' => $metadata['title'],
                    'thumbnail_url' => $metadata['thumbnail_url'],
                    'transcript' => $transcript,
                    'summary' => '',
                    'notes' => '',
                    'qa' => [],
                    'mcqs' => $materials['mcqs'] ?? [],
                    'action_items' => [],
                ]);

                $this->info("Success: Processed and saved '{$metadata['title']}'!");
                $successCount++;

                // Slight sleep to avoid API rate limits when doing batch imports
                if ($processed < $total) {
                    $this->line("Pausing 3 seconds before next video...");
                    sleep(3);
                }

            } catch (\Exception $e) {
                $this->error("Failed to process {$url}: " . $e->getMessage());
                $failedCount++;
            }
        }

        $this->line("");
        $this->info("==================================================");
        $this->info("Batch Processing Completed!");
        $this->info("Total: {$total}");
        $this->info("Success: {$successCount}");
        $this->info("Skipped (Already Existed): {$skippedCount}");
        $this->info("Failed: {$failedCount}");
        $this->info("==================================================");

        return Command::SUCCESS;
    }
}
