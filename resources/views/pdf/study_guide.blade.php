<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Study Guide - {{ $video->title }}</title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2d3748;
            line-height: 1.6;
            font-size: 13px;
        }
        h1, h2, h3, h4 {
            color: #1a202c;
            font-weight: 700;
            page-break-after: avoid;
        }
        .header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .title {
            font-size: 22px;
            color: #1a202c;
            margin: 0 0 8px 0;
            line-height: 1.3;
        }
        .meta {
            font-size: 11px;
            color: #718096;
        }
        .meta-item {
            margin-right: 15px;
        }
        .section-title {
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #2b6cb0;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-top: 30px;
            margin-bottom: 15px;
            page-break-after: avoid;
        }
        .markdown-content h1 { font-size: 15px; margin-top: 20px; }
        .markdown-content h2 { font-size: 14px; margin-top: 15px; }
        .markdown-content h3 { font-size: 13px; margin-top: 10px; }
        .markdown-content p { margin: 0 0 10px 0; text-align: justify; }
        .markdown-content ul, .markdown-content ol { margin: 0 0 10px 0; padding-left: 20px; }
        .markdown-content li { margin-bottom: 4px; }
        .markdown-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .markdown-content th, .markdown-content td {
            border: 1px solid #cbd5e0;
            padding: 8px;
            text-align: left;
        }
        .markdown-content th {
            background-color: #f7fafc;
            font-weight: bold;
        }
        .action-item {
            margin-bottom: 8px;
            page-break-inside: avoid;
        }
        .checkbox {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #4a5568;
            margin-right: 8px;
        }
        .action-text {
            display: inline-block;
            vertical-align: top;
            margin-top: -3px;
        }
        .qa-item {
            margin-bottom: 15px;
            page-break-inside: avoid;
            background-color: #f7fafc;
            padding: 12px;
            border-left: 3px solid #4299e1;
            border-radius: 0 4px 4px 0;
        }
        .question {
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 6px;
        }
        .answer {
            color: #4a5568;
            margin: 0;
        }
        .mcq-item {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .mcq-question {
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 8px;
        }
        .mcq-options {
            margin-bottom: 6px;
            padding-left: 15px;
        }
        .mcq-option {
            margin-bottom: 4px;
            color: #4a5568;
        }
        .mcq-answer {
            font-size: 11px;
            font-weight: bold;
            color: #2f855a;
            margin-top: 5px;
            background-color: #f0fff4;
            padding: 6px;
            border-radius: 4px;
            border-left: 2px solid #48bb78;
        }
        .mcq-explanation {
            font-weight: normal;
            color: #718096;
            display: block;
            margin-top: 2px;
        }
        .page-break {
            page-break-after: always;
        }
        .footer {
            position: fixed;
            bottom: -1cm;
            left: 0;
            right: 0;
            height: 0.5cm;
            text-align: center;
            font-size: 9px;
            color: #a0aec0;
            border-top: 1px solid #edf2f7;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="footer">
        Generated Study Guide • Video ID: {{ $video->video_id }} • Page <script type="text/php">echo $PAGE_NUM;</script> of <script type="text/php">echo $PAGE_COUNT;</script>
    </div>

    <div class="header">
        <h1 class="title">{{ $video->title }}</h1>
        <div class="meta">
            <span class="meta-item"><strong>Source:</strong> YouTube (ID: {{ $video->video_id }})</span>
            <span class="meta-item"><strong>Generated On:</strong> {{ now()->format('F d, Y') }}</span>
        </div>
    </div>

    @if(!empty($video->mcqs))
        <!-- Section: Self-Assessment MCQs -->
        <div class="section-title">Self-Assessment Quiz (MCQs)</div>
        <div style="margin-top: 10px;">
            @foreach($video->mcqs as $index => $mcq)
                <div class="mcq-item">
                    <div class="mcq-question">Q{{ $index + 1 }}: {{ $mcq['question'] }}</div>
                    <div class="mcq-options">
                        @foreach($mcq['options'] as $optIndex => $option)
                            @php $letter = chr(65 + $optIndex); @endphp
                            <div class="mcq-option"><strong>{{ $letter }}.</strong> {{ $option }}</div>
                        @endforeach
                    </div>
                    <div class="mcq-answer">
                        Correct Answer: {{ $mcq['answer'] }}
                        <span class="mcq-explanation"><strong>Explanation:</strong> {{ $mcq['explanation'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>
