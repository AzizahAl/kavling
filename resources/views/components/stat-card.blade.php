@props(['label', 'value', 'hint' => null, 'icon' => null, 'tone' => 'default'])
@php
    $tones = [
        'default' => ['card' => 'bg-white border-slate-200', 'label' => 'text-slate-500', 'value' => 'text-slate-900', 'hint' => 'text-slate-500', 'icon' => 'bg-forest-50 text-forest-600'],
        'dark'    => ['card' => 'bg-forest-800 border-forest-800', 'label' => 'text-forest-200', 'value' => 'text-white', 'hint' => 'text-forest-200', 'icon' => 'bg-white/10 text-gold-400'],
        'gold'    => ['card' => 'bg-gold-50 border-gold-200', 'label' => 'text-gold-800', 'value' => 'text-slate-900', 'hint' => 'text-gold-700', 'icon' => 'bg-gold-100 text-gold-700'],
        'danger'  => ['card' => 'bg-white border-slate-200', 'label' => 'text-slate-500', 'value' => 'text-red-600', 'hint' => 'text-slate-500', 'icon' => 'bg-red-50 text-red-600'],
    ];
    $t = $tones[$tone] ?? $tones['default'];
@endphp
<div {{ $attributes->merge(['class' => "rounded-xl border p-4 shadow-xs sm:p-5 {$t['card']}"]) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-xs font-medium tracking-wide uppercase {{ $t['label'] }}">{{ $label }}</p>
            <p class="mt-1.5 truncate text-xl font-bold tabular-nums sm:text-2xl {{ $t['value'] }}">{{ $value }}</p>
            @if ($hint)<p class="mt-1 text-xs {{ $t['hint'] }}">{{ $hint }}</p>@endif
            {{ $slot }}
        </div>
        @if ($icon)
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $t['icon'] }}"><x-icon :name="$icon"/></div>
        @endif
    </div>
</div>
