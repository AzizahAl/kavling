{{-- Dialog konfirmasi (hapus, batal, kembalikan tahap) mengambang di tengah layar, gaya sama dengan popup pesan. --}}
<div x-data x-show="$store.confirm.open" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4" role="alertdialog" aria-modal="true"
     x-on:keydown.escape.window="if ($store.confirm.open) $store.confirm.cancel()"
     x-effect="$store.confirm.open && $nextTick(() => $refs.konfirmasiBatal.focus())">
    <div x-show="$store.confirm.open" x-transition.opacity.duration.200ms class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]" x-on:click="$store.confirm.cancel()"></div>
    <div x-show="$store.confirm.open"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-end="opacity-0 scale-95"
         class="relative w-full max-w-sm rounded-popup bg-white px-6 pt-7 pb-6 text-center shadow-popup">
        <span class="mx-auto flex size-14 items-center justify-center rounded-full ring-8"
              :class="$store.confirm.danger ? 'bg-red-50 text-red-600 ring-red-100' : 'bg-amber-50 text-amber-600 ring-amber-100'">
            <x-icon name="warning" class="size-7"/>
        </span>
        <h3 class="mt-4 text-lg font-semibold text-slate-900" x-text="$store.confirm.title"></h3>
        <p class="mt-1.5 text-sm text-slate-600" x-text="$store.confirm.message"></p>
        <div class="mt-6 grid grid-cols-2 gap-2">
            <button type="button" x-ref="konfirmasiBatal" class="btn btn-secondary" x-on:click="$store.confirm.cancel()">Batal</button>
            <button type="button" class="btn" :class="$store.confirm.danger ? 'btn-danger' : 'btn-primary'"
                    x-on:click="$store.confirm.ok()" x-text="$store.confirm.okText"></button>
        </div>
    </div>
</div>
