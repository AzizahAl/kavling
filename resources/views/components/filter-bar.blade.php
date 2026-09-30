@props(['action' => url()->current(), 'cari' => true, 'placeholder' => 'Cari…'])
{{-- Bar pencarian & filter (GET). Select di slot otomatis mengirim form saat diubah. --}}
<form method="GET" action="{{ $action }}" x-data x-on:change="if ($event.target.tagName === 'SELECT' || $event.target.type === 'date' || $event.target.type === 'month') $el.requestSubmit()"
      {{ $attributes->merge(['class' => 'flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:flex-wrap sm:items-center']) }}>
    @if ($cari)
        <div class="relative w-full sm:w-72">
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"/>
            <input type="search" name="cari" value="{{ request('cari') }}" placeholder="{{ $placeholder }}" class="form-input pl-9">
        </div>
    @endif
    <div class="grid grid-cols-2 gap-3 sm:flex sm:flex-wrap sm:items-center">
        {{ $slot }}
    </div>
    <div class="flex gap-2 sm:ml-auto">
        <button type="submit" class="btn btn-secondary flex-1 sm:flex-none"><x-icon name="funnel" class="h-4 w-4"/> Terapkan</button>
        @if (collect(request()->except('page'))->filter()->isNotEmpty())
            <a href="{{ $action }}" class="btn btn-ghost flex-1 sm:flex-none">Reset</a>
        @endif
    </div>
</form>
