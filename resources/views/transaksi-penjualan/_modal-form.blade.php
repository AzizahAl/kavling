<div x-show="modalOpen" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4">

    {{-- Backdrop --}}
    <div x-show="modalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
         @click="closeModal()"></div>

    {{-- Panel --}}
    <div x-show="modalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-3"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-3"
         class="relative bg-white w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl shadow-2xl ring-1 ring-slate-900/5">

        <form @submit.prevent="submitForm()">

            {{-- Header --}}
            <div class="flex items-start justify-between px-6 pt-6 pb-4 border-b border-slate-200 sticky top-0 bg-white z-10">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Tambah Transaksi Penjualan</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Lengkapi data transaksi kavling dan informasi pembayaran.</p>
                </div>
                <button type="button" @click="closeModal()"
                    class="flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-6 space-y-6">

                {{-- 1. Informasi Transaksi --}}
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-semibold shrink-0">1</span>
                        <h3 class="font-semibold text-slate-800 text-sm tracking-wide">Informasi Transaksi</h3>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-slate-500">ID Transaksi</label>
                            <input type="text" :value="kodeBaru" disabled
                                class="mt-1.5 w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm bg-slate-50 text-slate-500 font-medium">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-500">Tanggal</label>
                            <input type="date" x-model="form.tanggal" required
                                class="mt-1.5 w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-slate-800/10 focus:border-slate-800 outline-none transition-shadow">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="text-xs font-medium text-slate-500 block mb-2">Status Transaksi</label>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="opt in ['reservasi','booking','dp']" :key="opt">
                                <button type="button" @click="form.status = opt"
                                    :class="form.status === opt
                                        ? 'bg-slate-900 text-white border-slate-900'
                                        : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'"
                                    class="px-3 py-2 rounded-lg text-sm font-medium capitalize transition-colors duration-150 border"
                                    x-text="opt"></button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- 2. Data Pembeli --}}
                <div class="pt-6 border-t border-slate-100">
                    <div class="flex items-center gap-2.5 mb-4">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-semibold shrink-0">2</span>
                        <h3 class="font-semibold text-slate-800 text-sm tracking-wide">Data Pembeli</h3>
                    </div>

                    <div class="relative">
                        <label class="text-xs font-medium text-slate-500">Cari Konsumen</label>
                        <div class="relative mt-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <input type="text" x-model="cariKonsumen" @input.debounce.300ms="searchKonsumen()"
                                placeholder="Cari nama, kode, atau nomor telepon"
                                class="w-full border border-slate-300 rounded-lg pl-10 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-slate-800/10 focus:border-slate-800 outline-none transition-shadow">
                        </div>

                        <div x-show="hasilKonsumen.length > 0" x-transition
                            class="absolute z-10 mt-1.5 w-full bg-white border border-slate-200 rounded-lg shadow-lg divide-y divide-slate-100 overflow-hidden">
                            <template x-for="k in hasilKonsumen" :key="k.id">
                                <div @click="pilihKonsumen(k)"
                                    class="px-3.5 py-2.5 text-sm hover:bg-slate-50 cursor-pointer transition-colors duration-100">
                                    <p class="font-medium text-slate-800" x-text="k.kode_konsumen + ' — ' + k.nama"></p>
                                    <p class="text-xs text-slate-500 mt-0.5" x-text="k.telepon"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div x-show="konsumenTerpilih.id" x-transition
                        class="mt-3 flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-lg px-4 py-3">
                        <div class="flex items-center justify-center w-9 h-9 rounded-full bg-slate-200 text-slate-500 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate" x-text="konsumenTerpilih.kode_konsumen + ' — ' + konsumenTerpilih.nama"></p>
                            <p class="text-xs text-slate-500" x-text="konsumenTerpilih.telepon"></p>
                        </div>
                    </div>
                </div>

                {{-- 3. Informasi Kavling --}}
                <div class="pt-6 border-t border-slate-100">
                    <div class="flex items-center gap-2.5 mb-4">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-semibold shrink-0">3</span>
                        <h3 class="font-semibold text-slate-800 text-sm tracking-wide">Informasi Kavling</h3>
                    </div>

                    <label class="text-xs font-medium text-slate-500">Kode Kavling</label>
                    <select x-model="form.kavling_id" @change="pilihKavling()" required
                        class="mt-1.5 w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-slate-800/10 focus:border-slate-800 outline-none transition-shadow">
                        <option value="">Pilih kavling</option>
                        <template x-for="kv in kavlings" :key="kv.id">
                            <option :value="kv.id" x-text="kv.kode_kavling"></option>
                        </template>
                    </select>

                    <div x-show="kavlingTerpilih.id" x-transition
                        class="mt-3 grid grid-cols-3 gap-3 bg-slate-50 border border-slate-200 rounded-lg p-4">
                        <div>
                            <p class="text-[11px] text-slate-400 uppercase tracking-wide font-medium">Kode</p>
                            <p class="font-medium text-slate-800 text-sm mt-0.5" x-text="kavlingTerpilih.kode_kavling"></p>
                        </div>
                        <div>
                            <p class="text-[11px] text-slate-400 uppercase tracking-wide font-medium">Blok / No</p>
                            <p class="font-medium text-slate-800 text-sm mt-0.5" x-text="kavlingTerpilih.blok + ' / ' + kavlingTerpilih.no"></p>
                        </div>
                        <div>
                            <p class="text-[11px] text-slate-400 uppercase tracking-wide font-medium">Tipe</p>
                            <p class="font-medium text-slate-800 text-sm mt-0.5" x-text="kavlingTerpilih.tipe"></p>
                        </div>
                        <div>
                            <p class="text-[11px] text-slate-400 uppercase tracking-wide font-medium">Luas</p>
                            <p class="font-medium text-slate-800 text-sm mt-0.5" x-text="kavlingTerpilih.luas + ' m²'"></p>
                        </div>
                        <div>
                            <p class="text-[11px] text-slate-400 uppercase tracking-wide font-medium">Harga / m²</p>
                            <p class="font-medium text-slate-800 text-sm mt-0.5" x-text="'Rp' + formatRupiah(kavlingTerpilih.harga_per_m2)"></p>
                        </div>
                        <div>
                            <p class="text-[11px] text-slate-400 uppercase tracking-wide font-medium">Nilai Jual</p>
                            <p class="font-semibold text-slate-900 text-sm mt-0.5" x-text="'Rp' + formatRupiah(kavlingTerpilih.harga_jual)"></p>
                        </div>
                    </div>
                </div>

                {{-- 4. Informasi Pembayaran --}}
<div class="pt-6 border-t border-slate-100">
    <div class="flex items-center gap-2.5 mb-4">
        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-semibold shrink-0">4</span>
        <h3 class="font-semibold text-slate-800 text-sm tracking-wide">Informasi Pembayaran</h3>
    </div>

    <label class="text-xs font-medium text-slate-500 block mb-2">Jenis Pembayaran</label>
    <div class="grid grid-cols-2 gap-2 mb-4">
        <template x-for="opt in ['cash','angsuran']" :key="opt">
            <button type="button" @click="form.jenis_pembayaran = opt"
                :class="form.jenis_pembayaran === opt
                    ? 'bg-slate-900 text-white border-slate-900'
                    : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'"
                class="px-3 py-2 rounded-lg text-sm font-medium capitalize transition-colors duration-150 border"
                x-text="opt"></button>
        </template>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-xs font-medium text-slate-500">Nilai Jual</label>
            <input type="text" :value="'Rp' + formatRupiah(nilaiJual)" disabled
                class="mt-1.5 w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm bg-slate-50 text-slate-500 font-medium">
        </div>
        <div>
            <label class="text-xs font-medium text-slate-500">Sudah Dibayar</label>
            <input type="text" :value="'Rp' + formatRupiah(sudahBayar)" disabled
                class="mt-1.5 w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm bg-slate-50 text-slate-500 font-medium">
        </div>
    </div>

    <div x-show="form.jenis_pembayaran === 'angsuran'" class="mt-4">
        <label class="text-xs font-medium text-slate-500">Tenor (bulan)</label>
        <input type="number" x-model.number="form.tenor" min="1"
            class="mt-1.5 w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-slate-800/10 focus:border-slate-800 outline-none transition-shadow">
    </div>

    <div class="mt-4 grid grid-cols-2 gap-4">
        {{-- Total Bayar Saat Ini: DIKETIK user --}}
        <div>
            <label class="text-xs font-medium text-slate-500">Total Bayar Saat Ini</label>
            <div class="relative mt-1.5">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500">Rp</span>
                <input type="text" inputmode="numeric" autocomplete="off" placeholder="0"
                :value="bayarSekarangTampil"
                @input="setBayarSekarang($event)"
                class="w-full border border-slate-300 rounded-lg pl-9 pr-3 py-2.5 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-slate-800/10 focus:border-slate-800 outline-none transition-shadow">
            </div>
        </div>

        {{-- Sisa Pembayaran: otomatis, berkurang saat user mengetik --}}
        <div>
            <label class="text-xs font-medium text-slate-500">Sisa Pembayaran</label>
            <div class="mt-1.5 w-full border border-slate-200 rounded-lg px-3 py-2.5 bg-slate-50">
                <p class="text-sm font-semibold"
                   :class="sisaPembayaran > 0 ? 'text-red-500' : 'text-emerald-600'"
                   x-text="'Rp' + formatRupiah(sisaPembayaran)"></p>
            </div>
        </div>
    </div>
</div>

                {{-- 5. Data Agen --}}
                <div class="pt-6 border-t border-slate-100">
                    <div class="flex items-center gap-2.5 mb-4">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-semibold shrink-0">5</span>
                        <h3 class="font-semibold text-slate-800 text-sm tracking-wide">Data Agen</h3>
                    </div>
                    <label class="text-xs font-medium text-slate-500">Pilih Agen</label>
                    <select x-model="form.agen_id"
                        class="mt-1.5 w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-slate-800/10 focus:border-slate-800 outline-none transition-shadow">
                        <option value="">Pilih agen</option>
                        <template x-for="a in agens" :key="a.id">
                            <option :value="a.id" x-text="a.nama_agen"></option>
                        </template>
                    </select>
                </div>

                {{-- 6. Catatan --}}
                <div class="pt-6 border-t border-slate-100">
                    <div class="flex items-center gap-2.5 mb-4">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-semibold shrink-0">6</span>
                        <h3 class="font-semibold text-slate-800 text-sm tracking-wide">Catatan</h3>
                    </div>
                    <textarea x-model="form.catatan" rows="3" placeholder="Tambahkan catatan terkait transaksi..."
                        class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-slate-800/10 focus:border-slate-800 outline-none transition-shadow resize-none"></textarea>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-200 sticky bottom-0 bg-white">
                <button type="button" @click="closeModal()"
                    class="px-4 py-2.5 rounded-lg border border-slate-300 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors duration-150">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium transition-colors duration-150">
                    Simpan Transaksi
                </button>
            </div>
        </form>
    </div>
</div>