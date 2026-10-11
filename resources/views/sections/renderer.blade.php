{{-- Master Section Renderer --}}
@props(['sections' => [], 'product' => null, 'theme' => 'dark_neon', 'isDraftPreview' => false])

@php
    $themePreset = $theme ?? 'dark_neon';
    $isDark = in_array($themePreset, ['dark_neon', 'luxury_emerald']);
@endphp

<div class="page-sections-container {{ $isDark ? 'bg-[#070B12] text-slate-100' : 'bg-[#F7F9FC] text-slate-900' }}">
    @foreach($sections as $index => $section)
        @if($section['enabled'] ?? true)
            @php
                $type = $section['type'] ?? 'spacer';
                $content = $section['content'] ?? [];
                $design = $section['design'] ?? [];
                $sectionId = $section['id'] ?? 'sec_' . $index;
            @endphp
            <div id="{{ $sectionId }}" data-section-type="{{ $type }}" class="section-wrapper relative">
                @includeIf('sections.' . $type, [
                    'section' => $section,
                    'content' => $content,
                    'design' => $design,
                    'product' => $product,
                    'theme' => $themePreset,
                    'isDark' => $isDark,
                    'isDraftPreview' => $isDraftPreview,
                ])
            </div>
        @endif
    @endforeach
</div>
