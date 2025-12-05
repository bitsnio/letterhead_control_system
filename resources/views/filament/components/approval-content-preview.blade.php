@php
    $orientation = $margins['orientation'] ?? 'portrait';
    $fontSize = $margins['font_size'] ?? 100;
    $isLandscape = $orientation === 'landscape';
    
    // Add inline styles to table elements for maximum compatibility
    $processedContent = preg_replace_callback(
        '/<table[^>]*>/i',
        function($matches) {
            return '<table style="width: 100%; border-collapse: collapse; border: 2px solid #000; margin: 12pt 0;">';
        },
        $content
    );
    
    $processedContent = preg_replace_callback(
        '/<(td|th)([^>]*)>/i',
        function($matches) {
            $tag = $matches[1];
            $attrs = $matches[2];
            $bgColor = $tag === 'th' ? ' background-color: #f3f4f6;' : '';
            $fontWeight = $tag === 'th' ? ' font-weight: bold;' : '';
            return "<{$tag}{$attrs} style=\"border: 1px solid #000; padding: 6pt 8pt;{$bgColor}{$fontWeight}\">";
        },
        $processedContent
    );
@endphp

<div class="approval-content-wrapper">
    
    <!-- A4 Page Preview with Shadow -->
    <div style="
        background: #f8fafc;
        padding: 30px;
        border-radius: 12px;
        overflow: auto;
        border: 1px solid #e2e8f0;
    ">
        <div class="a4-approval-page {{ $isLandscape ? 'landscape' : 'portrait' }}" 
             style="
                background: white;
                margin: 0 auto;
                box-shadow: 
                    0 4px 6px -1px rgba(0, 0, 0, 0.1),
                    0 2px 4px -1px rgba(0, 0, 0, 0.06),
                    0 0 0 1px rgba(0, 0, 0, 0.05);
                {{ $isLandscape ? 'width: 297mm; min-height: 210mm;' : 'width: 210mm; min-height: 297mm;' }}
                padding: {{ $margins['top'] }}mm {{ $margins['right'] }}mm {{ $margins['bottom'] }}mm {{ $margins['left'] }}mm;
                transform: scale(0.85);
                transform-origin: top center;
                border-radius: 4px;
             ">
            <div class="approval-content" style="font-size: {{ $fontSize }}%;">
                {!! $processedContent !!}
            </div>
        </div>
    </div>

    <!-- Zoom Controls Helper -->
    <div style="
        text-align: center;
        margin-top: 16px;
        padding: 8px;
        background: #fef3c7;
        border: 1px solid #fde68a;
        border-radius: 6px;
        font-size: 0.8125rem;
        color: #92400e;
    ">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px; display: inline; vertical-align: middle; margin-right: 4px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
        </svg>
        <strong>Tip:</strong> Use browser zoom (Ctrl + / Ctrl -) to adjust preview size if needed.
    </div>
</div>

<style>
    .approval-content-wrapper {
        width: 100%;
    }

    .approval-content {
        font-family: "Times New Roman", Times, serif;
        font-size: 12pt;
        line-height: 1.4;
        color: #000;
        word-wrap: break-word;
    }

    /* Headings */
    .approval-content h1 {
        font-size: 18pt;
        margin: 16pt 0 12pt 0;
        font-weight: bold;
        line-height: 1.2;
    }

    .approval-content h2 {
        font-size: 16pt;
        margin: 14pt 0 10pt 0;
        font-weight: bold;
        line-height: 1.2;
    }

    .approval-content h3 {
        font-size: 14pt;
        margin: 12pt 0 8pt 0;
        font-weight: bold;
        line-height: 1.2;
    }

    .approval-content h4 {
        font-size: 13pt;
        margin: 10pt 0 6pt 0;
        font-weight: bold;
        line-height: 1.2;
    }

    /* Paragraphs */
    .approval-content p {
        margin: 0 0 8pt 0;
        line-height: 1.4;
    }

    /* Tables - Force borders and proper styling with maximum specificity */
    .approval-content table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin: 12pt 0 !important;
        border: 2px solid #000 !important;
    }

    .approval-content table th,
    .approval-content table td {
        border: 1px solid #000 !important;
        padding: 6pt 8pt !important;
        font-size: 11pt !important;
        line-height: 1.3 !important;
    }

    .approval-content table th {
        background-color: #f3f4f6 !important;
        font-weight: bold !important;
        text-align: left !important;
    }

    .approval-content table tbody tr td {
        border: 1px solid #000 !important;
    }

    .approval-content table thead tr th {
        border: 1px solid #000 !important;
    }

    .approval-content table p {
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Additional table border enforcement */
    .approval-content table,
    .approval-content table * {
        border-color: #000 !important;
    }

    .approval-content tbody,
    .approval-content thead,
    .approval-content tr {
        border: 1px solid #000 !important;
    }

    /* Lists */
    .approval-content ul,
    .approval-content ol {
        margin: 8pt 0;
        padding-left: 24pt;
    }

    .approval-content li {
        margin-bottom: 4pt;
        line-height: 1.4;
    }

    /* Text formatting */
    .approval-content strong,
    .approval-content b {
        font-weight: bold;
    }

    .approval-content em,
    .approval-content i {
        font-style: italic;
    }

    .approval-content u {
        text-decoration: underline;
    }

    .approval-content s,
    .approval-content strike {
        text-decoration: line-through;
    }

    /* Blockquotes */
    .approval-content blockquote {
        margin: 12pt 0;
        padding-left: 16pt;
        border-left: 4px solid #d1d5db;
        color: #4b5563;
        font-style: italic;
    }

    /* Code */
    .approval-content code {
        background: #f3f4f6;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        font-size: 90%;
        color: #dc2626;
    }

    .approval-content pre {
        background: #f3f4f6;
        padding: 12px;
        border-radius: 6px;
        overflow-x: auto;
        margin: 12pt 0;
        border: 1px solid #e5e7eb;
    }

    .approval-content pre code {
        background: none;
        padding: 0;
        color: inherit;
    }

    /* Images */
    .approval-content img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 12pt 0;
    }

    /* Links */
    .approval-content a {
        color: #2563eb;
        text-decoration: underline;
    }

    /* Horizontal rules */
    .approval-content hr {
        border: none;
        border-top: 2px solid #e5e7eb;
        margin: 16pt 0;
    }

    /* Responsive scaling for smaller screens */
    @media (max-width: 1400px) {
        .a4-approval-page {
            transform: scale(0.75) !important;
        }
    }

    @media (max-width: 1200px) {
        .a4-approval-page {
            transform: scale(0.65) !important;
        }
    }

    @media (max-width: 1000px) {
        .a4-approval-page {
            transform: scale(0.55) !important;
        }
    }

    @media (max-width: 800px) {
        .a4-approval-page {
            transform: scale(0.45) !important;
        }
    }
</style>