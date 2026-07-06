@extends('layouts.app')

@section('title', 'Gola AI - YouTube Study Guide Generator')

@section('styles')
<style>
    .hero-section {
        text-align: center;
        margin-bottom: 3.5rem;
        padding-top: 1rem;
    }

    .hero-title {
        font-family: 'Outfit', sans-serif;
        font-size: 2.75rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #ffffff 40%, #9ca3af 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .hero-title span {
        background: linear-gradient(135deg, #6366f1, #10b981);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .hero-subtitle {
        color: var(--text-secondary);
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
    }

    .search-container {
        max-width: 700px;
        margin: 0 auto 3rem auto;
        position: relative;
    }

    .search-form {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        background: rgba(22, 31, 48, 0.4);
        border: 1px solid var(--border-color);
        padding: 0.75rem;
        border-radius: var(--radius-md);
        box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(10px);
        transition: var(--transition-smooth);
    }

    .search-form:focus-within {
        border-color: var(--primary);
        box-shadow: 0 10px 40px -10px rgba(99, 102, 241, 0.25);
    }

    .search-textarea {
        width: 100%;
        min-height: 90px;
        background: transparent;
        border: none;
        outline: none;
        color: var(--text-primary);
        font-size: 1.05rem;
        padding: 0.5rem 0.75rem;
        resize: vertical;
        font-family: 'Inter', sans-serif;
    }

    .search-textarea::placeholder {
        color: var(--text-muted);
    }

    .search-button-container {
        display: flex;
        justify-content: flex-end;
        padding-right: 0.25rem;
        padding-bottom: 0.25rem;
    }

    .search-button {
        background: linear-gradient(135deg, var(--primary), var(--primary-hover));
        color: white;
        border: none;
        outline: none;
        padding: 0.75rem 1.75rem;
        font-weight: 600;
        font-size: 0.95rem;
        border-radius: var(--radius-md);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: var(--transition-smooth);
    }

    .search-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .search-button:active {
        transform: translateY(0);
    }

    .import-file-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.25rem 0.75rem 0.5rem 0.75rem;
        border-top: 1px solid rgba(255, 255, 255, 0.04);
        margin-top: 0.5rem;
        padding-top: 0.75rem;
    }

    .import-file-label {
        font-size: 0.85rem;
        color: var(--text-secondary);
        cursor: pointer;
        padding: 0.4rem 0.8rem;
        border-radius: var(--radius-sm);
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border-color);
        transition: var(--transition-smooth);
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .import-file-label:hover {
        background: rgba(99, 102, 241, 0.1);
        border-color: rgba(99, 102, 241, 0.3);
        color: var(--text-primary);
    }

    .import-file-name {
        font-size: 0.8rem;
        color: var(--text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 250px;
    }

    /* Batch Results Banner styling */
    .batch-results-container {
        max-width: 700px;
        margin: 0 auto 3rem auto;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .batch-results-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .batch-results-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
    }

    .batch-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-sm);
        background: rgba(255, 255, 255, 0.02);
    }

    .batch-item.success {
        border-left: 3px solid var(--success);
        background: rgba(16, 185, 129, 0.03);
    }

    .batch-item.info {
        border-left: 3px solid #3b82f6;
        background: rgba(59, 130, 246, 0.03);
    }

    .batch-item.failed {
        border-left: 3px solid var(--danger);
        background: rgba(239, 68, 68, 0.03);
    }

    .batch-badge {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        white-space: nowrap;
    }

    .batch-badge.success {
        background: var(--success-glow);
        color: var(--success);
    }

    .batch-badge.info {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }

    .batch-badge.failed {
        background: var(--danger-glow);
        color: var(--danger);
    }

    .batch-details {
        flex: 1;
    }

    .batch-video-title {
        font-weight: 600;
        color: var(--text-primary);
    }

    .batch-video-url {
        font-family: monospace;
        color: var(--text-secondary);
        word-break: break-all;
    }

    /* Live Progress Tracker styling */
    .live-progress-container {
        max-width: 700px;
        margin: 0 auto 3rem auto;
        background: var(--bg-secondary);
        border: 1px solid var(--primary);
        border-radius: var(--radius-md);
        padding: 1.5rem;
        box-shadow: 0 10px 40px -10px rgba(99, 102, 241, 0.2);
        display: none;
    }

    .live-progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.75rem;
    }

    .live-progress-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .live-progress-counter {
        font-size: 0.9rem;
        font-weight: 600;
        background: var(--primary-glow);
        color: var(--primary);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        border: 1px solid rgba(99, 102, 241, 0.3);
    }

    .live-progress-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        max-height: 350px;
        overflow-y: auto;
        margin-bottom: 1.25rem;
        padding-right: 0.25rem;
    }

    .progress-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        border-radius: var(--radius-sm);
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border-color);
        transition: var(--transition-smooth);
    }

    .progress-item.active {
        border-color: var(--primary);
        background: rgba(99, 102, 241, 0.03);
    }

    .progress-item.success {
        border-color: var(--success);
        background: rgba(16, 185, 129, 0.03);
    }

    .progress-item.failed {
        border-color: var(--danger);
        background: rgba(239, 68, 68, 0.03);
    }

    .progress-item-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex: 1;
        min-width: 0;
    }

    .progress-item-status-icon {
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    .progress-item-text {
        font-size: 0.9rem;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: var(--text-secondary);
    }

    .progress-item.active .progress-item-text {
        color: var(--text-primary);
        font-weight: 600;
    }

    .progress-item.success .progress-item-text {
        color: var(--text-primary);
    }

    .progress-item-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
    }

    .progress-item-badge.pending {
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-muted);
    }

    .progress-item-badge.processing {
        background: var(--primary-glow);
        color: var(--primary);
        animation: pulse 1.5s infinite;
    }

    .progress-item-badge.success {
        background: var(--success-glow);
        color: var(--success);
    }

    .progress-item-badge.failed {
        background: var(--danger-glow);
        color: var(--danger);
    }

    .live-progress-footer {
        display: flex;
        justify-content: flex-end;
        border-top: 1px solid var(--border-color);
        padding-top: 1rem;
    }

    .reload-library-btn {
        background: linear-gradient(135deg, var(--success), #059669);
        color: white;
        border: none;
        outline: none;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        font-size: 0.9rem;
        border-radius: var(--radius-md);
        cursor: pointer;
        display: none;
        align-items: center;
        gap: 0.5rem;
        transition: var(--transition-smooth);
    }

    .reload-library-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .reload-library-btn:active {
        transform: translateY(0);
    }

    /* Small dot animation for active items */
    .loading-dot-spinner {
        display: inline-block;
        width: 10px;
        height: 10px;
        border: 2px solid transparent;
        border-top-color: currentColor;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    .section-header {
        font-family: 'Outfit', sans-serif;
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-header::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border-color);
    }

    .grid-layout {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .video-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        text-decoration: none;
        color: inherit;
        transition: var(--transition-smooth);
        position: relative;
    }

    .video-card:hover {
        transform: translateY(-4px);
        border-color: rgba(99, 102, 241, 0.3);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
    }

    .thumbnail-wrapper {
        position: relative;
        aspect-ratio: 16/9;
        overflow: hidden;
        background: #111;
    }

    .thumbnail-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition-smooth);
    }

    .video-card:hover .thumbnail-img {
        scale: 1.05;
    }

    .play-badge {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 44px;
        height: 44px;
        background: rgba(11, 15, 25, 0.75);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: var(--transition-smooth);
    }

    .video-card:hover .play-badge {
        opacity: 1;
    }

    .play-badge svg {
        margin-left: 3px;
        fill: white;
    }

    .card-content {
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .card-title {
        font-family: 'Inter', sans-serif;
        font-size: 0.95rem;
        font-weight: 600;
        line-height: 1.4;
        margin-bottom: 0.75rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        color: var(--text-primary);
    }

    .card-meta {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Loading Overlay styling */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(11, 15, 25, 0.95);
        backdrop-filter: blur(12px);
        z-index: 1000;
        display: none;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .spinner-container {
        position: relative;
        width: 80px;
        height: 80px;
        margin-bottom: 2rem;
    }

    .spinner {
        box-sizing: border-box;
        display: block;
        position: absolute;
        width: 80px;
        height: 80px;
        border: 4px solid transparent;
        border-radius: 50%;
        animation: spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        border-top-color: var(--primary);
    }
    
    .spinner-inner {
        box-sizing: border-box;
        display: block;
        position: absolute;
        width: 60px;
        height: 60px;
        margin: 10px;
        border: 4px solid transparent;
        border-radius: 50%;
        animation: spin-reverse 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        border-top-color: #10b981;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes spin-reverse {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(-360deg); }
    }

    .loading-text {
        font-family: 'Outfit', sans-serif;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--text-primary);
    }

    .loading-subtext {
        font-size: 0.95rem;
        color: var(--text-secondary);
        max-width: 400px;
        text-align: center;
        animation: pulse 2s infinite ease-in-out;
    }

    @keyframes pulse {
        0%, 100% { opacity: 0.5; }
        50% { opacity: 1; }
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: var(--bg-card);
        border: 1px dashed var(--border-color);
        border-radius: var(--radius-md);
        color: var(--text-secondary);
    }

    .empty-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: var(--text-muted);
    }

    /* Library row list layout styles */
    .library-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .library-row {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: var(--transition-smooth);
    }

    .library-row:hover {
        border-color: rgba(99, 102, 241, 0.3);
        background: rgba(22, 31, 48, 0.9);
        transform: translateX(4px);
    }

    .library-number {
        font-family: 'Outfit', sans-serif;
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--primary);
        background: var(--primary-glow);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(99, 102, 241, 0.2);
        flex-shrink: 0;
    }

    .library-info {
        flex: 1;
        min-width: 0;
    }

    .library-title {
        font-family: 'Inter', sans-serif;
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--text-primary);
        text-decoration: none;
        display: block;
        line-height: 1.4;
        margin-bottom: 0.25rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: var(--transition-smooth);
    }

    .library-title:hover {
        color: var(--primary);
    }

    .library-meta {
        font-size: 0.8rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .meta-dot {
        color: rgba(255, 255, 255, 0.15);
    }

    .library-action {
        flex-shrink: 0;
    }

    .download-pdf-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(16, 185, 129, 0.15);
        color: var(--success);
        border: 1px solid rgba(16, 185, 129, 0.25);
        padding: 0.5rem 1rem;
        border-radius: var(--radius-sm);
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 700;
        transition: var(--transition-smooth);
    }

    .download-pdf-btn:hover {
        background: linear-gradient(135deg, var(--success), #059669);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
</style>
@endsection

@section('content')
<div class="hero-section">
    <h1 class="hero-title">Study Smart. <span>Learn Faster.</span></h1>
    <p class="hero-subtitle">Transform any YouTube video lecture into summaries, comprehensive study notes, interactive quizzes, Q&As, and downloadable PDFs instantly.</p>
</div>

<div class="search-container">
    <form action="{{ route('videos.store') }}" method="POST" class="search-form" id="generator-form">
        @csrf
        <textarea 
            name="url" 
            placeholder="Paste one or more YouTube video URLs or IDs (one per line, or separated by commas)..." 
            class="search-textarea"
            required
        >{{ old('url') }}</textarea>
        <div class="search-button-container">
            <button type="submit" class="search-button">
                Generate Study Guides
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </button>
        </div>
        <div class="import-file-row">
            <label for="url-file-input" class="import-file-label">
                <span>📁</span> Import URLs from Text File (.txt)
            </label>
            <input type="file" id="url-file-input" accept=".txt" style="display: none;">
            <span id="import-file-name" class="import-file-name"></span>
        </div>
    </form>
</div>

<!-- Live Progress Tracker -->
<div class="live-progress-container" id="live-progress-container">
    <div class="live-progress-header">
        <h3 class="live-progress-title">
            <span>⚙️</span> Batch Processing Queue
        </h3>
        <span class="live-progress-counter" id="live-progress-counter">0 / 0 Completed</span>
    </div>
    
    <div class="live-progress-list" id="live-progress-list">
        <!-- Dynamically populated by JavaScript -->
    </div>
    
    <div class="live-progress-footer">
        <button id="reload-library-btn" class="reload-library-btn" onclick="window.location.reload();">
            <span>📚</span> Go to Learning Library
        </button>
    </div>
</div>

@if(session('batch_results'))
    <div class="batch-results-container">
        <h3 class="batch-results-title">
            <span>📊</span> Batch Processing Results
        </h3>
        <div class="batch-results-list">
            @foreach(session('batch_results')['success'] as $item)
                <div class="batch-item success">
                    <span class="batch-badge success">Success</span>
                    <div class="batch-details">
                        <span class="batch-video-title">
                            <a href="{{ route('videos.show', $item['video_id']) }}" style="color: inherit; text-decoration: underline;">
                                {{ $item['title'] }}
                            </a>
                        </span>
                    </div>
                </div>
            @endforeach

            @foreach(session('batch_results')['retrieved'] as $item)
                <div class="batch-item info">
                    <span class="batch-badge info">Exists</span>
                    <div class="batch-details">
                        <span class="batch-video-title">
                            <a href="{{ route('videos.show', $item['video_id']) }}" style="color: inherit; text-decoration: underline;">
                                {{ $item['title'] }}
                            </a>
                        </span>
                    </div>
                </div>
            @endforeach

            @foreach(session('batch_results')['failed'] as $item)
                <div class="batch-item failed">
                    <span class="batch-badge failed">Failed</span>
                    <div class="batch-details">
                        <div class="batch-video-url">{{ $item['url'] }}</div>
                        <div class="batch-error-msg">{{ $item['error'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="section-header">
    My Learning Library
</div>

@if($videos->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">📚</div>
        <h3>No study guides generated yet</h3>
        <p style="margin-top: 0.5rem; font-size: 0.9rem;">Paste a YouTube link above to generate your first study guide!</p>
    </div>
@else
    <div class="library-list">
        @foreach($videos as $video)
            <div class="library-row">
                <div class="library-number">{{ sprintf('%02d', $loop->iteration) }}</div>
                <div class="library-info">
                    <a href="{{ route('videos.pdf', $video->video_id) }}" class="library-title" title="Download PDF study guide">
                        {{ $video->title }}
                    </a>
                    <div class="library-meta">
                        <span>ID: {{ $video->video_id }}</span>
                        <span class="meta-dot">•</span>
                        <span>{{ $video->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="library-action">
                    <a href="{{ route('videos.pdf', $video->video_id) }}" class="download-pdf-btn" title="Download PDF">
                        <span>PDF</span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif

<!-- Loading Overlay -->
<div class="loading-overlay" id="loading-overlay">
    <div class="spinner-container">
        <div class="spinner"></div>
        <div class="spinner-inner"></div>
    </div>
    <div class="loading-text" id="loading-text">Analyzing Video</div>
    <div class="loading-subtext" id="loading-subtext">Initializing the AI pipeline...</div>
</div>
@endsection

@section('scripts')
<script>
    const form = document.getElementById('generator-form');
    const overlay = document.getElementById('loading-overlay');
    const subtext = document.getElementById('loading-subtext');
    const text = document.getElementById('loading-text');

    // Live Progress Elements
    const progressContainer = document.getElementById('live-progress-container');
    const progressCounter = document.getElementById('live-progress-counter');
    const progressList = document.getElementById('live-progress-list');
    const reloadBtn = document.getElementById('reload-library-btn');

    const steps = [
        { time: 0, text: "Analyzing YouTube Video", sub: "Checking details and extracting metadata..." },
        { time: 3000, text: "Extracting Transcript", sub: "Reading and transcribing video contents (handling multilingual fallback)..." },
        { time: 8000, text: "Summarizing with Gemini", sub: "Google Gemini 1.5 Flash is extracting core takeaways and structuring the summary..." },
        { time: 13000, text: "Generating Study Materials", sub: "GPT-4o-mini is compiling comprehensive notes, interactive MCQs, checklists, and Q&As..." },
        { time: 20000, text: "Finalizing Workspace", sub: "Saving resources to your library database..." }
    ];

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Stop normal form submit
            
            const textarea = form.querySelector('.search-textarea');
            const val = textarea ? textarea.value : '';
            const urls = val.split(/[\n,\s]+/).filter(u => u.trim().length > 0);
            
            if (urls.length === 0) return;

            if (urls.length > 300) {
                alert("You can only process up to 300 videos at a time. Please remove " + (urls.length - 300) + " video(s) and try again.");
                return;
            }

            const csrfToken = form.querySelector('input[name="_token"]').value;

            if (urls.length === 1) {
                // SINGLE URL PROCESSING: Show the beautiful overlay loader and run AJAX
                overlay.style.display = 'flex';
                
                // Start step text cycle
                steps.forEach(step => {
                    setTimeout(() => {
                        if (overlay.style.display === 'flex') {
                            text.innerText = step.text;
                            subtext.innerText = step.sub;
                        }
                    }, step.time);
                });

                // Send AJAX request
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ url: urls[0] })
                })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(errData => {
                            throw new Error(errData.message || `HTTP ${res.status}`);
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.status === 'success' || data.status === 'exists') {
                        // Redirect directly to the show workspace page
                        window.location.href = `/videos/` + data.video_id;
                    } else {
                        overlay.style.display = 'none';
                        alert('Error processing video: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(err => {
                    overlay.style.display = 'none';
                    alert('Request failed: ' + err.message);
                });

            } else {
                // MULTIPLE URL PROCESSING (BATCH): Show live list tracking progress in real time
                
                // Hide search container to focus on progress
                form.closest('.search-container').style.display = 'none';
                
                // Hide any previous session banners
                const prevBanners = document.querySelectorAll('.batch-results-container, .alert');
                prevBanners.forEach(b => b.style.display = 'none');

                // Display live progress container
                progressContainer.style.display = 'block';
                progressCounter.innerText = `0 / ${urls.length} Completed`;
                
                // Initialize progress list DOM
                progressList.innerHTML = '';
                urls.forEach((url, index) => {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'progress-item';
                    itemDiv.id = `progress-item-${index}`;
                    itemDiv.innerHTML = `
                        <div class="progress-item-left">
                            <span class="progress-item-status-icon" id="progress-icon-${index}">⏳</span>
                            <span class="progress-item-text" id="progress-text-${index}" title="${url}">${url}</span>
                        </div>
                        <span class="progress-item-badge pending" id="progress-badge-${index}">Pending</span>
                    `;
                    progressList.appendChild(itemDiv);
                });

                // Start sequential processing
                let completedCount = 0;
                
                function processNext(currentIndex) {
                    if (currentIndex >= urls.length) {
                        // Finished all!
                        progressCounter.innerText = `${urls.length} / ${urls.length} Completed`;
                        reloadBtn.style.display = 'inline-flex';
                        return;
                    }

                    const currentUrl = urls[currentIndex];
                    const itemDiv = document.getElementById(`progress-item-${currentIndex}`);
                    const iconSpan = document.getElementById(`progress-icon-${currentIndex}`);
                    const badgeSpan = document.getElementById(`progress-badge-${currentIndex}`);
                    const textSpan = document.getElementById(`progress-text-${currentIndex}`);

                    // Set state to active/processing
                    itemDiv.className = 'progress-item active';
                    iconSpan.innerHTML = '<span class="loading-dot-spinner"></span>';
                    badgeSpan.className = 'progress-item-badge processing';
                    badgeSpan.innerText = 'Processing';

                    // Send AJAX request
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ url: currentUrl })
                    })
                    .then(res => {
                        if (!res.ok) {
                            return res.json().then(errData => {
                                throw new Error(errData.message || `HTTP ${res.status}`);
                            });
                        }
                        return res.json();
                    })
                    .then(data => {
                        itemDiv.className = 'progress-item';
                        
                        if (data.status === 'success' || data.status === 'exists') {
                            itemDiv.classList.add('success');
                            iconSpan.innerHTML = '✅';
                            badgeSpan.className = 'progress-item-badge success';
                            badgeSpan.innerText = data.status === 'success' ? 'Success' : 'Exists';
                            // Replace URL with title for nicer presentation
                            textSpan.innerText = data.title;
                            textSpan.style.textDecoration = 'underline';
                            textSpan.style.cursor = 'pointer';
                            textSpan.onclick = () => window.open(`/videos/` + data.video_id, '_blank');
                        } else {
                            itemDiv.classList.add('failed');
                            iconSpan.innerHTML = '❌';
                            badgeSpan.className = 'progress-item-badge failed';
                            badgeSpan.innerText = 'Failed';
                            textSpan.innerText = `${currentUrl} (${data.message || 'Error'})`;
                            textSpan.title = data.message || 'Error';
                        }

                        completedCount++;
                        progressCounter.innerText = `${completedCount} / ${urls.length} Completed`;
                        
                        // Proceed to next with a 3-second delay to respect API rate limits
                        setTimeout(() => {
                            processNext(currentIndex + 1);
                        }, 3000);
                    })
                    .catch(err => {
                        itemDiv.className = 'progress-item';
                        itemDiv.classList.add('failed');
                        iconSpan.innerHTML = '❌';
                        badgeSpan.className = 'progress-item-badge failed';
                        badgeSpan.innerText = 'Failed';
                        textSpan.innerText = `${currentUrl} (${err.message})`;
                        
                        completedCount++;
                        progressCounter.innerText = `${completedCount} / ${urls.length} Completed`;
                        
                        // Proceed to next with a 3-second delay to respect API rate limits
                        setTimeout(() => {
                            processNext(currentIndex + 1);
                        }, 3000);
                    });
                }

                // Trigger processing of first item
                processNext(0);
            }
        });
    }

    // File upload handler
    const fileInput = document.getElementById('url-file-input');
    const fileNameSpan = document.getElementById('import-file-name');
    
    if (fileInput && fileNameSpan) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            fileNameSpan.innerText = file.name;
            
            const reader = new FileReader();
            reader.onload = function(evt) {
                const textVal = evt.target.result;
                // Parse URLs
                const rawLines = textVal.split(/[\n,\s]+/);
                const urls = rawLines.filter(u => u.trim().length > 0);
                
                if (urls.length > 0) {
                    const textarea = form.querySelector('.search-textarea');
                    if (textarea) {
                        textarea.value = urls.join('\n');
                        
                        if (urls.length > 10) {
                            alert("Warning: You imported " + urls.length + " URLs.\n\n" +
                                  "Processing more than 10 videos in the browser sequentially may take a very long time and can hit browser or request limits.\n\n" +
                                  "For large lists (like 300 videos), it is highly recommended to run the Artisan command on your machine instead:\n" +
                                  "php artisan videos:process path/to/your/file.txt");
                        }
                    }
                } else {
                    alert("No valid URLs found in the selected file.");
                    fileNameSpan.innerText = "";
                    fileInput.value = "";
                }
            };
            reader.onerror = function() {
                alert("Failed to read the file.");
                fileNameSpan.innerText = "";
                fileInput.value = "";
            };
            reader.readAsText(file);
        });
    }
</script>
@endsection
