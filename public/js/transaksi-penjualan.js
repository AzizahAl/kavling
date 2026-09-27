function transaksiPage() {
    return {
        modalOpen: false,
        kodeBaru: KODE_BARU,
        kavlings: KAVLINGS,
        agens: AGENS,

        cariKonsumen: '',
        hasilKonsumen: [],
        konsumenTerpilih: {},
        kavlingTerpilih: {},

        form: {
            tanggal: new Date().toISOString().slice(0, 10),
            status: 'reservasi',
            konsumen_id: null,
            kavling_id: '',
            jenis_pembayaran: 'cash',
            tenor: 12,
            nominal_dp: 0,
            agen_id: '',
            catatan: '',
        },

        init() {
            // state awal sudah di-set lewat properti di atas
        },

        openModal() {
            this.resetForm();
            this.modalOpen = true;
        },

        closeModal() {
            this.modalOpen = false;
        },

        resetForm() {
            this.form = {
                tanggal: new Date().toISOString().slice(0, 10),
                status: 'reservasi',
                konsumen_id: null,
                kavling_id: '',
                jenis_pembayaran: 'cash',
                tenor: 12,
                nominal_dp: 0,
                agen_id: '',
                catatan: '',
            };
            this.konsumenTerpilih = {};
            this.kavlingTerpilih = {};
            this.cariKonsumen = '';
            this.hasilKonsumen = [];
        },

        async searchKonsumen() {
            if (this.cariKonsumen.trim().length < 2) {
                this.hasilKonsumen = [];
                return;
            }
            try {
                const res = await fetch(`${URL_CARI_KONSUMEN}?q=${encodeURIComponent(this.cariKonsumen)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                this.hasilKonsumen = await res.json();
            } catch (err) {
                console.error('Gagal mencari konsumen:', err);
                this.hasilKonsumen = [];
            }
        },

        pilihKonsumen(k) {
            this.konsumenTerpilih = k;
            this.form.konsumen_id = k.id;
            this.cariKonsumen = '';
            this.hasilKonsumen = [];
        },

        pilihKavling() {
            const kv = this.kavlings.find(k => k.id == this.form.kavling_id);
            this.kavlingTerpilih = kv || {};
        },

        formatRupiah(angka) {
            if (!angka) return '0';
            return Number(angka).toLocaleString('id-ID');
        },

        get totalBayar() {
            if (this.form.jenis_pembayaran === 'cash') {
                return this.kavlingTerpilih.harga_jual || 0;
            }
            return this.form.nominal_dp || 0;
        },

        get sisaPembayaran() {
            const nilaiJual = this.kavlingTerpilih.harga_jual || 0;
            return nilaiJual - this.totalBayar;
        },

        async submitForm() {
            if (!this.form.konsumen_id) {
                alert('Silakan pilih konsumen terlebih dahulu.');
                return;
            }
            if (!this.form.kavling_id) {
                alert('Silakan pilih kavling terlebih dahulu.');
                return;
            }

            try {
                const res = await fetch(URL_STORE, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
                    },
                    body: JSON.stringify(this.form)
                });

                if (!res.ok) {
                    const err = await res.json();
                    alert(err.message || 'Gagal menyimpan transaksi.');
                    return;
                }

                window.location.reload();
            } catch (err) {
                console.error('Gagal menyimpan transaksi:', err);
                alert('Terjadi kesalahan saat menyimpan transaksi.');
            }
        }
    }
}