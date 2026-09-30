@props(['align' => 'right', 'width' => 'w-56'])
{{--
    Menu tarik-turun: slot "trigger" (tombol) + isi menu. Item: <x-menu-item href|type=submit icon>Label</x-menu-item>
    Bisa ditutup dengan Esc / klik di luar; panah atas-bawah berpindah item.
--}}
<div class="relative" x-data="{ buka: false }" x-on:keydown.escape.window="buka = false" x-on:click.outside="buka = false"
     x-on:keydown.down.prevent="buka = true; $nextTick(() => { const i = [...$refs.isi.querySelectorAll('a,button')]; (i[i.indexOf(document.activeElement) + 1] || i[0])?.focus() })"
     x-on:keydown.up.prevent="const i = [...$refs.isi.querySelectorAll('a,button')]; (i[i.indexOf(document.activeElement) - 1] || i.at(-1))?.focus()">
    <div x-on:click="buka = !buka">{{ $trigger }}</div>
    <div x-ref="isi" x-show="buka" x-cloak role="menu"
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100" x-transition:leave-end="opacity-0 scale-95"
         @class(['absolute z-40 mt-2 overflow-hidden rounded-popup border border-slate-200 bg-white p-1 shadow-menu', $width,
                 'right-0 origin-top-right' => $align === 'right', 'left-0 origin-top-left' => $align !== 'right'])>
        {{ $slot }}
    </div>
</div>
