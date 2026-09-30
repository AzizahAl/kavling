@props(['label', 'value', 'singkat' => null, 'hint' => null, 'icon' => null, 'tone' => 'default', 'href' => null])
{{--
    Kartu angka ringkasan. $singkat (mis. "Rp116,9 jt") dipakai di layar kecil agar nominal tidak terpotong;
    nilai lengkap tampil mulai lebar tablet. tone: default | utama | peringatan | bahaya
--}}
@php
    $tones = [
        'default'    => ['card' => 'bg-white border-slate-200', 'label' => 'text-slate-500', 'value' => 'text-slate-900', 'hint' => 'text-slate-500', 'icon' => 'bg-slate-100 text-slate-500'],
        'utama'      => ['card' => 'bg-brand-700 border-brand-700', 'label' => 'text-brand-100', 'value' => 'text-white', 'hint' => 'text-brand-100', 'icon' => 'bg-white/10 text-white'],
        'peringatan' => ['card' => 'bg-white border-amber-200', 'label' => 'text-amber-700', 'value' => 'text-slate-900', 'hint' => 'text-slate-500', 'icon' => 'bg-amber-50 text-amber-600'],
        'bahaya'     => ['card' => 'bg-white border-red-200', 'label' => 'text-red-600', 'value' => 'text-red-600', 'hint' => 'text-slate-500', 'icon' => 'bg-red-50 text-red-600'],
        // alias lama
        'dark' => null, 'gold' => null, 'danger' => null,
    ];
    $tone = ['dark' => 'utama', 'gold' => 'peringatan', 'danger' => 'bahaya'][$tone] ?? $tone;
    $t = $tones[$tone] ?? $tones['default'];
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => "block min-w-0 rounded-kartu border p-4 shadow-kartu transition-colors duration-150 sm:p-5 {$t['card']}" . ($href ? ' hover:border-brand-300' : '')]) }}>
    <div class="flex items-start justify-between gap-2">
        <p class="text-xs font-medium {{ $t['label'] }}">{{ $label }}</p>
        @if ($icon)
            <span class="-mt-1 -mr-1 flex size-8 shrink-0 items-center justify-center rounded-lg {{ $t['icon'] }}"><x-icon :name="$icon" class="size-4"/></span>
        @endif
    </div>
    <p class="mt-1 text-lg font-semibold tracking-tight tabular-nums sm:text-2xl {{ $t['value'] }}">
        @if ($singkat !== null)
            <span class="sm:hidden">{{ $singkat }}</span><span class="hidden sm:inline">{{ $value }}</span>
        @else
            <span class="break-words">{{ $value }}</span>
        @endif
    </p>
    @if ($hint)<p class="mt-1 text-xs {{ $t['hint'] }}">{{ $hint }}</p>@endif
    {{ $slot }}
</{{ $tag }}>
