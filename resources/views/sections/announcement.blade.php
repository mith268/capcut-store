@php
    $bgColor = $design['bg_color'] ?? ($isDark ? '#080C14' : '#0F172A');
    $textColor = $design['text_color'] ?? '#FFFFFF';
@endphp
<div style="background-color: {{ $bgColor }}; color: {{ $textColor }};" class="py-2.5 px-4 text-center text-xs font-semibold tracking-wide border-b border-cyan-500/20">
    <div class="max-w-7xl mx-auto flex items-center justify-center space-x-2">
        <span class="inline-block w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
        <span>{{ $content['text'] ?? '⚡ LIMITED-TIME OFFER • Instant Digital Delivery' }}</span>
        @if(!empty($content['link_url']) && !empty($content['link_label']))
            <a href="{{ $content['link_url'] }}" class="underline font-bold text-cyan-300 ml-2">{{ $content['link_label'] }}</a>
        @endif
    </div>
</div>
