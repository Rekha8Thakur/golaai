<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Services\YouTubeService;
use App\Services\GeminiService;
use App\Services\OpenAIService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Exception;

class VideoController extends Controller
{
    protected YouTubeService $youtubeService;
    protected GeminiService $geminiService;
    protected OpenAIService $openaiService;

    public function __construct(
        YouTubeService $youtubeService,
        GeminiService $geminiService,
        OpenAIService $openaiService
    ) {
        $this->youtubeService = $youtubeService;
        $this->geminiService = $geminiService;
        $this->openaiService = $openaiService;
    }

    /**
     * Renders the home screen listing previously analyzed videos.
     */
    public function index()
    {
        $videos = Video::orderBy('created_at', 'desc')->get();
        return view('videos.index', compact('videos'));
    }

    /**
     * Accepts YouTube URL, extracts transcript, calls Gemini/OpenAI APIs, stores data, and redirects.
     */
    public function store(Request $request)
    {
        // Increase maximum execution time to 5 minutes to allow APIs and Python script execution to finish
        set_time_limit(300);

        $request->validate([
            'url' => 'required|string',
        ]);

        $input = $request->input('url');
        
        // Handle AJAX/JSON requests (one URL processed per request)
        if ($request->expectsJson()) {
            $videoId = $this->youtubeService->extractVideoId($input);

            if (!$videoId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Could not extract YouTube video ID from the provided URL.'
                ], 400);
            }

            // Check if video already exists in db
            $existingVideo = Video::find($videoId);
            if ($existingVideo) {
                return response()->json([
                    'status' => 'exists',
                    'video_id' => $videoId,
                    'title' => $existingVideo->title
                ]);
            }

            try {
                // 1. Fetch transcript using Python CLI script
                $transcript = $this->youtubeService->fetchTranscript($videoId);

                // 2. Fetch metadata (Title & Thumbnail)
                $metadata = $this->youtubeService->getVideoMetadata($videoId);

                // 3. Generate summary via Gemini API
                $summary = $this->geminiService->generateSummary($transcript);

                // 4. Generate Q&A, MCQs, Notes, and Action Items via OpenAI API
                $materials = $this->openaiService->generateStudyMaterials($transcript, $summary);

                // 5. Save everything in database
                Video::create([
                    'video_id' => $videoId,
                    'title' => $metadata['title'],
                    'thumbnail_url' => $metadata['thumbnail_url'],
                    'transcript' => $transcript,
                    'summary' => $summary,
                    'notes' => $materials['notes'] ?? '',
                    'qa' => $materials['qa'] ?? [],
                    'mcqs' => $materials['mcqs'] ?? [],
                    'action_items' => $materials['action_items'] ?? [],
                ]);

                return response()->json([
                    'status' => 'success',
                    'video_id' => $videoId,
                    'title' => $metadata['title']
                ]);

            } catch (Exception $e) {
                \Illuminate\Support\Facades\Log::error($e);
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 500);
            }
        }

        // --- Synchronous Fallback (original non-JSON behavior) ---
        // Split input by newlines, commas, or whitespace, and filter out empty elements
        $rawUrls = preg_split('/[\n,\s]+/', $input);
        $urls = array_unique(array_filter(array_map('trim', $rawUrls)));

        if (empty($urls)) {
            return redirect()->back()->with('error', 'No valid YouTube URL or Video ID provided.')->withInput();
        }

        $results = [
            'success' => [],
            'retrieved' => [],
            'failed' => []
        ];

        foreach ($urls as $url) {
            $videoId = $this->youtubeService->extractVideoId($url);

            if (!$videoId) {
                $results['failed'][] = [
                    'url' => $url,
                    'error' => 'Could not extract YouTube video ID.'
                ];
                continue;
            }

            // Check if video already exists in db
            $existingVideo = Video::find($videoId);
            if ($existingVideo) {
                $results['retrieved'][] = [
                    'video_id' => $videoId,
                    'title' => $existingVideo->title,
                ];
                continue;
            }

            try {
                // 1. Fetch transcript using Python CLI script
                $transcript = $this->youtubeService->fetchTranscript($videoId);

                // 2. Fetch metadata (Title & Thumbnail)
                $metadata = $this->youtubeService->getVideoMetadata($videoId);

                // 3. Generate summary via Gemini API
                $summary = $this->geminiService->generateSummary($transcript);

                // 4. Generate Q&A, MCQs, Notes, and Action Items via OpenAI API
                $materials = $this->openaiService->generateStudyMaterials($transcript, $summary);

                // 5. Save everything in database
                Video::create([
                    'video_id' => $videoId,
                    'title' => $metadata['title'],
                    'thumbnail_url' => $metadata['thumbnail_url'],
                    'transcript' => $transcript,
                    'summary' => $summary,
                    'notes' => $materials['notes'] ?? '',
                    'qa' => $materials['qa'] ?? [],
                    'mcqs' => $materials['mcqs'] ?? [],
                    'action_items' => $materials['action_items'] ?? [],
                ]);

                $results['success'][] = [
                    'video_id' => $videoId,
                    'title' => $metadata['title'],
                ];

            } catch (Exception $e) {
                \Illuminate\Support\Facades\Log::error($e);
                $results['failed'][] = [
                    'url' => $url,
                    'error' => $e->getMessage()
                ];
            }
        }

        // If only one URL was inputted:
        if (count($urls) === 1) {
            if (!empty($results['success'])) {
                $video_id = $results['success'][0]['video_id'];
                return redirect()->route('videos.show', $video_id)->with('success', 'Study materials generated successfully!');
            }
            if (!empty($results['retrieved'])) {
                $video_id = $results['retrieved'][0]['video_id'];
                return redirect()->route('videos.show', $video_id)->with('info', 'Retrieved existing analysis from database.');
            }
            // If failed
            $errorMsg = $results['failed'][0]['error'];
            return redirect()->back()->with('error', 'Pipeline Error: ' . $errorMsg)->withInput();
        }

        // For multiple URLs, redirect back to home index and display detailed status info
        return redirect()->route('videos.index')->with('batch_results', $results);
    }

    /**
     * Shows the workspace for a processed video.
     */
    public function show(string $videoId)
    {
        $video = Video::findOrFail($videoId);
        return view('videos.show', compact('video'));
    }

    /**
     * Generates and downloads a beautifully styled PDF study guide.
     */
    public function downloadPdf(string $videoId)
    {
        $video = Video::findOrFail($videoId);

        // Generate PDF using Laravel DomPDF
        $pdf = Pdf::loadView('pdf.study_guide', compact('video'))
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->download("study-guide-{$videoId}.pdf");
    }
}
