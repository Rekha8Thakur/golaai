@extends('layouts.app')

@section('title', $video->title . ' - Study Guide')

@section('styles')
<style>
    .workspace-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    @media (min-width: 992px) {
        .workspace-container {
            grid-template-columns: 3fr 1.2fr;
        }
    }

    .workspace-header {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    @media (min-width: 768px) {
        .workspace-header {
            flex-direction: row;
            justify-content: space-between;
            align-items: flex-start;
        }
    }

    .video-info-block {
        flex: 1;
    }

    .video-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.85rem;
        font-weight: 700;
        line-height: 1.3;
        margin-bottom: 0.5rem;
        color: var(--text-primary);
    }

    .video-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        font-size: 0.9rem;
        font-weight: 600;
        border-radius: var(--radius-sm);
        text-decoration: none;
        cursor: pointer;
        transition: var(--transition-smooth);
        border: none;
        outline: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-hover));
        color: white;
    }

    .btn-primary:hover {
        box-shadow: 0 4px 12px var(--primary-glow);
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
    }

    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-1px);
    }

    /* Tabs Component styling */
    .tabs-nav {
        display: flex;
        border-bottom: 1px solid var(--border-color);
        gap: 0.5rem;
        overflow-x: auto;
        margin-bottom: 1.5rem;
        padding-bottom: 2px;
    }

    .tab-button {
        background: transparent;
        border: none;
        outline: none;
        color: var(--text-secondary);
        font-size: 0.95rem;
        font-weight: 600;
        padding: 0.75rem 1.25rem;
        cursor: pointer;
        border-radius: var(--radius-sm) var(--radius-sm) 0 0;
        position: relative;
        white-space: nowrap;
        transition: var(--transition-smooth);
    }

    .tab-button:hover {
        color: var(--text-primary);
        background: rgba(255, 255, 255, 0.03);
    }

    .tab-button.active {
        color: var(--primary);
    }

    .tab-button.active::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        width: 100%;
        height: 3px;
        background-color: var(--primary);
        border-radius: 99px;
    }

    .tab-pane {
        display: none;
        animation: fadeIn 0.4s ease;
    }

    .tab-pane.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Markdown Styling for Study Notes */
    .markdown-view {
        line-height: 1.7;
    }

    .markdown-view h1, .markdown-view h2, .markdown-view h3 {
        font-family: 'Outfit', sans-serif;
        color: var(--text-primary);
        margin-top: 1.75rem;
        margin-bottom: 0.75rem;
        font-weight: 600;
    }

    .markdown-view h1 { font-size: 1.4rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; }
    .markdown-view h2 { font-size: 1.25rem; }
    .markdown-view h3 { font-size: 1.1rem; }

    .markdown-view p {
        margin-bottom: 1.25rem;
        color: #d1d5db;
        text-align: justify;
    }

    .markdown-view ul, .markdown-view ol {
        margin-bottom: 1.25rem;
        padding-left: 1.5rem;
    }

    .markdown-view li {
        margin-bottom: 0.5rem;
        color: #d1d5db;
    }

    .markdown-view table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
        background: rgba(255, 255, 255, 0.01);
        border-radius: var(--radius-sm);
        overflow: hidden;
    }

    .markdown-view th, .markdown-view td {
        border: 1px solid var(--border-color);
        padding: 0.75rem 1rem;
        text-align: left;
    }

    .markdown-view th {
        background-color: rgba(255, 255, 255, 0.04);
        font-weight: 600;
        color: var(--text-primary);
    }

    /* Interactive Quiz (MCQ) styling */
    .quiz-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .quiz-card {
        background: rgba(22, 31, 48, 0.3);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1.5rem;
        transition: var(--transition-smooth);
    }

    .quiz-question {
        font-size: 1.05rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--text-primary);
    }

    .quiz-options {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .option-btn {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        padding: 0.9rem 1.2rem;
        border-radius: var(--radius-sm);
        text-align: left;
        cursor: pointer;
        font-size: 0.95rem;
        transition: var(--transition-smooth);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .option-btn:hover {
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-primary);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .option-btn.selected-correct {
        background: var(--success-glow) !important;
        border-color: var(--success) !important;
        color: #6ee7b7 !important;
        font-weight: 500;
    }

    .option-btn.selected-incorrect {
        background: var(--danger-glow) !important;
        border-color: var(--danger) !important;
        color: #fca5a5 !important;
        font-weight: 500;
    }

    .option-btn.should-have-selected {
        background: var(--success-glow) !important;
        border-color: var(--success) !important;
        color: #6ee7b7 !important;
    }

    .quiz-feedback {
        margin-top: 1rem;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.03);
        border-radius: var(--radius-sm);
        font-size: 0.9rem;
        color: var(--text-secondary);
        display: none;
        animation: fadeIn 0.3s ease;
    }

    .quiz-feedback strong {
        color: var(--text-primary);
    }

    /* Accordion Q&A styling */
    .qa-container {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .qa-card {
        background: rgba(22, 31, 48, 0.3);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        overflow: hidden;
        transition: var(--transition-smooth);
    }

    .qa-trigger {
        width: 100%;
        background: transparent;
        border: none;
        outline: none;
        padding: 1.25rem 1.5rem;
        text-align: left;
        color: var(--text-primary);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }

    .qa-trigger:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    .qa-icon {
        color: var(--text-muted);
        transition: var(--transition-smooth);
    }

    .qa-card.open .qa-icon {
        transform: rotate(180deg);
        color: var(--primary);
    }

    .qa-content {
        padding: 0 1.5rem 1.25rem 1.5rem;
        color: var(--text-secondary);
        line-height: 1.6;
        display: none;
        border-top: 1px solid transparent;
        animation: fadeIn 0.3s ease;
    }

    .qa-card.open .qa-content {
        display: block;
        border-top-color: var(--border-color);
    }

    /* Interactive Action Items (Checklist) */
    .checklist {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .checklist-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        background: rgba(22, 31, 48, 0.2);
        border: 1px solid var(--border-color);
        padding: 1rem;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: var(--transition-smooth);
    }

    .checklist-item:hover {
        background: rgba(255, 255, 255, 0.02);
        border-color: rgba(255, 255, 255, 0.15);
    }

    .checkbox-mock {
        width: 18px;
        height: 18px;
        border: 2px solid var(--text-muted);
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 2px;
        transition: var(--transition-smooth);
        flex-shrink: 0;
    }

    .checklist-item.checked .checkbox-mock {
        background: var(--success);
        border-color: var(--success);
    }

    .checkbox-mock svg {
        opacity: 0;
        transition: var(--transition-smooth);
        color: white;
    }

    .checklist-item.checked .checkbox-mock svg {
        opacity: 1;
    }

    .checklist-text {
        font-size: 0.95rem;
        color: var(--text-secondary);
        transition: var(--transition-smooth);
    }

    .checklist-item.checked .checklist-text {
        text-decoration: line-through;
        color: var(--text-muted);
    }

    /* Transcript section styling */
    .transcript-box {
        max-height: 550px;
        overflow-y: auto;
        border: 1px solid var(--border-color);
        background: rgba(11, 15, 25, 0.5);
        border-radius: var(--radius-md);
        padding: 1.25rem;
    }

    .transcript-segment {
        display: flex;
        gap: 1rem;
        padding: 0.75rem 0.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        align-items: flex-start;
    }

    .transcript-segment:last-child {
        border-bottom: none;
    }

    .transcript-segment:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    .transcript-time {
        font-family: 'Outfit', sans-serif;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--primary);
        text-decoration: none;
        background: var(--primary-glow);
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
        flex-shrink: 0;
        border: 1px solid rgba(99, 102, 241, 0.15);
        transition: var(--transition-smooth);
    }

    .transcript-time:hover {
        background: var(--primary);
        color: white;
    }

    .transcript-text {
        font-size: 0.925rem;
        line-height: 1.5;
        color: var(--text-secondary);
    }

    /* Sidebar Thumbnail & Embedded Video box */
    .sidebar-block {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .video-preview-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        overflow: hidden;
    }

    .embedded-container {
        aspect-ratio: 16/9;
        width: 100%;
        background: #000;
    }

    .embedded-container iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    .preview-info {
        padding: 1rem;
    }

    .preview-meta-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .preview-meta-value {
        font-size: 0.9rem;
        color: var(--text-primary);
        word-break: break-all;
    }
</style>
@endsection

@section('content')
<div class="workspace-header">
    <div class="video-info-block">
        <h1 class="video-title">{{ $video->title }}</h1>
        <p style="color: var(--text-secondary); font-size: 0.95rem;">
            YouTube ID: <code style="color: var(--primary); font-weight: 600;">{{ $video->video_id }}</code>
        </p>
    </div>
    <div class="video-actions">
        <a href="{{ route('videos.pdf', $video->video_id) }}" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Download PDF Guide
        </a>
        <a href="{{ route('videos.index') }}" class="btn btn-secondary">
            Back to Library
        </a>
    </div>
</div>

<div class="workspace-container">
    <!-- Left Column: Study Workspaces -->
    <div class="glass-card" style="padding: 1.5rem;">
        <!-- Tabs Headers -->
        <nav class="tabs-nav">
            <button class="tab-button active" onclick="switchTab(event, 'tab-quiz')">Interactive Quiz</button>
            <button class="tab-button" onclick="switchTab(event, 'tab-transcript')">Transcript</button>
        </nav>

        <!-- Tab: Interactive Quiz (MCQs) -->
        <div id="tab-quiz" class="tab-pane active">
            <div class="quiz-container">
                @if(empty($video->mcqs))
                    <p style="color: var(--text-muted);">No MCQs generated.</p>
                @else
                    @foreach($video->mcqs as $index => $mcq)
                        <div class="quiz-card" data-correct-answer="{{ $mcq['answer'] }}">
                            <div class="quiz-question">Q{{ $index + 1 }}: {{ $mcq['question'] }}</div>
                            <div class="quiz-options">
                                @foreach($mcq['options'] as $option)
                                    <button class="option-btn" onclick="selectOption(this, '{{ addslashes($option) }}')">
                                        <span>{{ $option }}</span>
                                    </button>
                                @endforeach
                            </div>
                            <div class="quiz-feedback">
                                <strong>Explanation:</strong> {{ $mcq['explanation'] }}
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Tab: Transcript with interactive timestamps -->
        <div id="tab-transcript" class="tab-pane">
            <div class="transcript-box">
                @if(empty($video->transcript))
                    <p style="color: var(--text-muted);">No transcript available.</p>
                @else
                    @foreach($video->transcript as $segment)
                        <div class="transcript-segment">
                            <a href="https://youtube.com/watch?v={{ $video->video_id }}&t={{ floor($segment['start']) }}" 
                               target="_blank" 
                               class="transcript-time"
                               title="Click to jump to YouTube"
                            >
                                {{ $segment['time_str'] }}
                            </a>
                            <div class="transcript-text">{{ $segment['text'] }}</div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Sidebar Embedded Video and Metadata -->
    <div class="sidebar-block">
        <div class="video-preview-card">
            <div class="embedded-container">
                <iframe 
                    src="https://www.youtube.com/embed/{{ $video->video_id }}" 
                    title="YouTube video player" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen
                ></iframe>
            </div>
            <div class="preview-info">
                <div style="margin-bottom: 0.75rem;">
                    <div class="preview-meta-label">Title</div>
                    <div class="preview-meta-value" style="font-weight: 600;">{{ $video->title }}</div>
                </div>
                <div>
                    <div class="preview-meta-label">Original Video Link</div>
                    <a href="https://www.youtube.com/watch?v={{ $video->video_id }}" target="_blank" class="preview-meta-value" style="color: var(--primary); text-decoration: none; word-break: break-all;">
                        youtube.com/watch?v={{ $video->video_id }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Tabs Navigation
    function switchTab(evt, tabId) {
        // Get all elements with class="tab-pane" and hide them
        const tabPanes = document.querySelectorAll('.tab-pane');
        tabPanes.forEach(pane => {
            pane.classList.remove('active');
        });

        // Get all elements with class="tab-button" and remove the class "active"
        const tabButtons = document.querySelectorAll('.tab-button');
        tabButtons.forEach(btn => {
            btn.classList.remove('active');
        });

        // Show the current tab, and add an "active" class to the button that opened the tab
        document.getElementById(tabId).classList.add('active');
        evt.currentTarget.classList.add('active');
    }

    // Toggle Action Item Checkbox
    function toggleChecklist(element) {
        element.classList.toggle('checked');
    }

    // Interactive Quiz (MCQ) click handler
    function selectOption(button, optionText) {
        const card = button.closest('.quiz-card');
        
        // If this question has already been answered, do nothing
        if (card.querySelector('.selected-correct') || card.querySelector('.selected-incorrect')) {
            return;
        }

        const correctAnswer = card.getAttribute('data-correct-answer');
        const feedback = card.querySelector('.quiz-feedback');
        const allOptionBtns = card.querySelectorAll('.option-btn');

        if (optionText.trim() === correctAnswer.trim()) {
            button.classList.add('selected-correct');
            // Append check icon
            button.innerHTML += `
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                </svg>
            `;
        } else {
            button.classList.add('selected-incorrect');
            // Append cross icon
            button.innerHTML += `
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            `;

            // Highlight the correct option
            allOptionBtns.forEach(btn => {
                const btnText = btn.querySelector('span').innerText;
                if (btnText.trim() === correctAnswer.trim()) {
                    btn.classList.add('should-have-selected');
                }
            });
        }

        // Disable hover effects and show explanation
        allOptionBtns.forEach(btn => {
            btn.style.cursor = 'default';
        });
        
        feedback.style.display = 'block';
    }

    // Toggle Accordion Q&A
    function toggleQA(button) {
        const card = button.closest('.qa-card');
        card.classList.toggle('open');
    }
</script>
@endsection
