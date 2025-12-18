@props([
    'value' => 0,
    'label' => '',
    'icon' => 'chart-bar',
])

@php
    $svgPaths = [
        'users' => 'M9 7.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM15.75 9.75a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM4.501 15.03a3.75 3.75 0 0 1 7.248-1.235 5.995 5.995 0 0 0-4.749 4.688 6 6 0 0 0-2.499-3.453ZM12 16.5a3.75 3.75 0 0 1 7.48.607 6 6 0 0 0-7.123 4.11A3.75 3.75 0 0 1 12 16.5Z',
        'clipboard' => 'M9 2.25A2.25 2.25 0 0 0 6.75 4.5v.75H6a3 3 0 0 0-3 3V18a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3V8.25a3 3 0 0 0-3-3h-.75V4.5A2.25 2.25 0 0 0 15 2.25h-6Zm-1.5 9a.75.75 0 0 1 0-1.5h6a.75.75 0 0 1 0 1.5h-6ZM9 13.5a.75.75 0 0 0 0 1.5h6a.75.75 0 0 0 0-1.5H9Z',
        'sparkles' => 'M12 2.25l.818 2.52a2.25 2.25 0 0 0 1.412 1.412l2.52.818-2.52.818a2.25 2.25 0 0 0-1.412 1.412L12 11.25l-.818-2.52a2.25 2.25 0 0 0-1.412-1.412L7.25 6.75l2.52-.818a2.25 2.25 0 0 0 1.412-1.412L12 2.25ZM5.25 9l.545 1.683c.18.557.607.985 1.164 1.164L8.643 12l-1.684.545a1.875 1.875 0 0 0-1.164 1.164L5.25 15.5l-.545-1.791a1.875 1.875 0 0 0-1.164-1.164L1.857 12l1.684-.546a1.875 1.875 0 0 0 1.164-1.164L5.25 9Zm11.25 1.5 1.091 3.378a1.5 1.5 0 0 0 .95.95L21.75 15l-3.378 1.091a1.5 1.5 0 0 0-.95.95L16.5 20.25l-1.091-3.379a1.5 1.5 0 0 0-.95-.95L11.25 15l3.378-1.091a1.5 1.5 0 0 0 .95-.95L16.5 10.5Z',
        'bell' => 'M12 3a6 6 0 0 1 6 6v3.586l1.707 1.707A1 1 0 0 1 19.586 16H4.414a1 1 0 0 1-.707-1.707L5.414 12.586V9a6 6 0 0 1 6-6Zm0 18a3 3 0 0 0 2.995-2.824L15 18h-6a3 3 0 0 0 2.824 2.995L12 21Z',
        'chart' => 'M4.5 4.5h3v13h-3v-13Zm6 5h3v8h-3v-8Zm6-4h3v12h-3v-12Z',
    ];

    $path = $svgPaths[$icon] ?? $svgPaths['chart'];
@endphp

<div {{ $attributes->class('rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg shadow-black/10') }}>
    <div class="flex items-center justify-between">
        <div class="rounded-2xl bg-uae-gold-300/15 p-3 text-uae-gold-200">
            <svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                <path d="{{ $path }}" />
            </svg>
        </div>
        <span class="text-xs uppercase tracking-wide text-slate-400">{{ $label }}</span>
    </div>
    <p class="mt-6 text-3xl font-semibold text-white">{{ number_format($value) }}</p>
</div>

