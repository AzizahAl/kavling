{{-- Dialog konfirmasi global. Dipicu oleh form ber-atribut data-confirm (lihat resources/js/app.js). --}}
<div x-data x-show="$store.confirm.open" x-cloak class="fixed inset-0 z-[60] flex items-end justify-center p-4 sm:items-center" role="alertdialog" aria-modal="true">
    <div x-show="$store.confirm.open" x-transition.opacity class="absolute inset-0 bg-slate-900/50" x-on:click="$store.confirm.cancel()"></div>
    <div x-show="$store.confirm.open" x-transition class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"
         x-on:keydown.escape.window="$store.confirm.cancel()">
        <div class="flex gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full"
                 :class="$store.confirm.danger ? 'bg-red-50 text-red-600' : 'bg-forest-50 text-forest-600'">
                <x-icon name="warning" class="h-6 w-6"/>
            </div>
            <div class="min-w-0">
                <h3 class="text-base font-semibold text-slate-900" x-text="$store.confirm.title"></h3>
                <p class="mt-1 text-sm text-slate-600" x-text="$store.confirm.message"></p>
            </div>
        </div>
        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-secondary" x-on:click="$store.confirm.cancel()">Batal</button>
            <button type="button" class="btn" :class="$store.confirm.danger ? 'btn-danger' : 'btn-primary'"
                    x-on:click="$store.confirm.ok()" x-text="$store.confirm.okText"></button>
        </div>
    </div>
</div>
