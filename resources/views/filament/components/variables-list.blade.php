<div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
    <span style="font-size: 0.875rem; font-weight: 600; color: #374151;">
        Detected Variables:
    </span>
    @foreach($variables as $variable)
        <span style="
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            font-family: monospace;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        ">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px; margin-right: 4px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
            </svg>
            ${{ $variable }}$
        </span>
    @endforeach
    <span style="
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        background: #f3f4f6;
        color: #6b7280;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    ">
        {{ count($variables) }} variable{{ count($variables) !== 1 ? 's' : '' }}
    </span>
</div>