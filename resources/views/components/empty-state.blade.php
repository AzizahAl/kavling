@props(['title' => 'Belum ada data', 'message' => null, 'icon' => 'inbox'])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-12 text-center']) }}>
    <div class="mb-3 flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-400"><x-icon :name="$icon" class="size-6"/></div>
    <p class="text-sm font-semibold text-slate-800">{{ $title }}</p>
    @if ($message)<p class="mt-1 max-w-xs text-sm text-slate-500">{{ $message }}</p>@endif
    @if (trim($slot))<div class="mt-4">{{ $slot }}</div>@endif
</div>
