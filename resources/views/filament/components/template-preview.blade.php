<div class="template-preview-container">
    <!-- Preview Info Banner -->
    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 20px; height: 20px; color: #3b82f6;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            <div>
                <p style="margin: 0; font-size: 0.875rem; font-weight: 600; color: #1e40af;">
                    Preview Mode
                </p>
                <p style="margin: 0; font-size: 0.75rem; color: #3b82f6;">
                    Orientation: <strong>{{ ucfirst($orientation) }}</strong> | 
                    Font Size: <strong>{{ $fontSize }}%</strong> | 
                    Margins: {{ $margins['top'] }}mm, {{ $margins['right'] }}mm, {{ $margins['bottom'] }}mm, {{ $margins['left'] }}mm
                </p>
            </div>
        </div>
    </div>

    <!-- A4 Page Preview -->
    <div style="background: #f8fafc; padding: 20px; border-radius: 8px; overflow: auto; max-height: 70vh;">
        <div class="a4-preview-page {{ $isLandscape ? 'landscape' : 'portrait' }}" 
             style="
                background: white;
                margin: 0 auto;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                {{ $isLandscape ? 'width: 297mm; min-height: 210mm;' : 'width: 210mm; min-height: 297mm;' }}
                padding: {{ $margins['top'] }}mm {{ $margins['right'] }}mm {{ $margins['bottom'] }}mm {{ $margins['left'] }}mm;
                transform: scale(0.8);
                transform-origin: top center;
             ">
            <div class="preview-content" style="font-size: {{ $fontSize }}%;">
                {!! $content !!}
            </div>
        </div>
    </div>

    <!-- Variable Placeholder Notice -->
    <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px; margin-top: 20px;">
        <div style="display: flex; align-items: start; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 20px; height: 20px; color: #d97706; flex-shrink: 0; margin-top: 2px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <div>
                <p style="margin: 0; font-size: 0.875rem; font-weight: 600; color: #92400e;">
                    Variables Preview
                </p>
                <p style="margin: 0; font-size: 0.75rem; color: #b45309;">
                    Variables like <code style="background: #fed7aa; padding: 2px 6px; border-radius: 4px;">$Variable$</code> will be replaced with actual values during printing.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    .preview-content {
        font-family: "Times New Roman", Times, serif;
        font-size: 12pt;
        line-height: 1.3;
        color: #000;
    }

    .preview-content h1 {
        font-size: 16pt;
        margin-bottom: 12pt;
        font-weight: bold;
    }

    .preview-content h2 {
        font-size: 14pt;
        margin-bottom: 10pt;
        font-weight: bold;
    }

    .preview-content h3 {
        font-size: 13pt;
        margin-bottom: 8pt;
        font-weight: bold;
    }

    .preview-content h4 {
        font-size: 12pt;
        margin-bottom: 6pt;
        font-weight: bold;
    }

    .preview-content p {
        margin-bottom: 6pt;
        line-height: 1.3;
    }

    .preview-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 8pt 0;
    }

    .preview-content th,
    .preview-content td {
        border: 1px solid #000;
        padding: 4pt 6pt;
        font-size: 10pt;
    }

    .preview-content th {
        background-color: #f8fafc;
        font-weight: bold;
    }

    .preview-content ul,
    .preview-content ol {
        margin-bottom: 8pt;
        margin-left: 20pt;
    }

    .preview-content li {
        margin-bottom: 4pt;
    }

    .preview-content img {
        max-width: 100%;
        height: auto;
    }

    .preview-content blockquote {
        margin-left: 20pt;
        padding-left: 10pt;
        border-left: 3px solid #d1d5db;
        font-style: italic;
    }

    .preview-content code {
        background: #f3f4f6;
        padding: 2px 6px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 90%;
    }

    .preview-content pre {
        background: #f3f4f6;
        padding: 12px;
        border-radius: 6px;
        overflow-x: auto;
    }

    /* Responsive scaling */
    @media (max-width: 1200px) {
        .a4-preview-page {
            transform: scale(0.7) !important;
        }
    }

    @media (max-width: 900px) {
        .a4-preview-page {
            transform: scale(0.6) !important;
        }
    }
</style>