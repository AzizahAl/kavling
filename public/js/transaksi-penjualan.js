function transaksiPage() {
    const today = () => new Date().toISOString().slice(0, 10);

    const emptyForm = () => ({
        tanggal: today(),
        status: 'reservasi',
        konsumen_id: '',
        kavling_id: '',
        agen_id: '',
        jenis_pembayaran: 'cash',
        tenor: null,
        bayar_sekarang: '',   // nominal yang mau dibayar sekarang (diketik user)
        catatan: '',
    });

    return {
        modalOpen: false,
        kodeBaru: KODE_BARU,
        kavlings: [...KAVLINGS],
        agens: AGENS,
        form: emptyForm(),

        cariKonsumen: '',
        hasilKonsumen: [],
        konsumenTerpilih: {},
        kavlingTerpilih: {},
        sudahBayar: 0,        // total yang sudah dibayar konsumen sebelumnya
        errors: {},
        saving: false,

        init() {},

        openModal() {
            this.resetForm();
            this.modalOpen = true;
        },

        closeModal() {
            this.modalOpen = false;
        },

        resetForm() {
            this.form = emptyForm();
            this.cariKonsumen = '';
            this.hasilKonsumen = [];
            this.konsumenTerpilih = {};
            this.kavlingTerpilih = {};
            this.sudahBayar = 0;
            this.errors = {};
        },

        formatRupiah(n) {
            return new Intl.NumberFormat('id-ID').format(Number(n) || 0);
        },

                // Tampilan input: 2500000 -> "2.500.000"
        get bayarSekarangTampil() {
            const n = Number(this.form.bayar_sekarang) || 0;
            return n > 0 ? new Intl.NumberFormat('id-ID').format(n) : '';
        },

        // Saat user mengetik: ambil angkanya saja, simpan sebagai angka murni
        setBayarSekarang(e) {
            const angka = e.target.value.replace(/\D/g, '');   // buang titik & huruf
            this.form.bayar_sekarang = angka === '' ? '' : Number(angka);
            e.target.value = this.bayarSekarangTampil;          // tampilkan dengan titik
        },

        get nilaiJual() {
            return Number(this.kavlingTerpilih.harga_jual) || 0;
        },

        // Sisa yang harus dibayar sebelum ada pembayaran baru
        get sisaAwal() {
            return Math.max(this.nilaiJual - this.sudahBayar, 0);
        },

        // Sisa setelah dikurangi "Total Bayar Saat Ini"
        get sisaPembayaran() {
            return Math.max(this.sisaAwal - (Number(this.form.bayar_sekarang) || 0), 0);
        },

        async searchKonsumen() {
            const q = this.cariKonsumen.trim();
            if (q.length < 1) {
                this.hasilKonsumen = [];
                return;
            }
            try {
                const res = await fetch(`${URL_CARI_KONSUMEN}?q=${encodeURIComponent(q)}`, {
                    headers: { 'Accept': 'application/json' },
                });
                this.hasilKonsumen = await res.json();
            } catch (e) {
                console.error(e);
                this.hasilKonsumen = [];
            }
        },

        pilihKonsumen(k) {
            this.konsumenTerpilih = k;
            this.form.konsumen_id = k.id;
            this.cariKonsumen = '';
            this.hasilKonsumen = [];

            if (k.kavling) {
                if (!this.kavlings.some(kv => String(kv.id) === String(k.kavling.id))) {
                    this.kavlings.push(k.kavling);
                }
                this.form.kavling_id = String(k.kavling.id);
                this.kavlingTerpilih = k.kavling;
            } else {
                this.form.kavling_id = '';
                this.kavlingTerpilih = {};
            }

            this.form.agen_id = k.agen_id ? String(k.agen_id) : '';
            this.form.status = k.status || 'reservasi';
            this.form.jenis_pembayaran = k.jenis_pembayaran || 'cash';
            this.form.tenor = k.tenor || null;

            this.sudahBayar = Number(k.sudah_bayar) || 0;
            this.form.bayar_sekarang = '';   // kosong, user yang isi
        },

        pilihKavling() {
            const kv = this.kavlings.find(x => String(x.id) === String(this.form.kavling_id));
            this.kavlingTerpilih = kv || {};
        },

        getCsrf() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) return { 'X-CSRF-TOKEN': meta.content };
            const m = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
            return m ? { 'X-XSRF-TOKEN': decodeURIComponent(m[1]) } : {};
        },

        async submitForm() {
            if (!this.form.konsumen_id) {
                alert('Pilih konsumen terlebih dahulu.');
                return;
            }
            if (!this.form.kavling_id) {
                alert('Pilih kavling terlebih dahulu.');
                return;
            }
            if ((Number(this.form.bayar_sekarang) || 0) > this.sisaAwal) {
                alert('Total bayar saat ini tidak boleh melebihi sisa pembayaran.');
                return;
            }

            this.saving = true;
            this.errors = {};

            try {
                const res = await fetch(URL_STORE, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        ...this.getCsrf(),
                    },
                    body: JSON.stringify({
                        ...this.form,
                        bayar_sekarang: Number(this.form.bayar_sekarang) || 0,
                    }),
                });

                if (res.status === 422) {
                    const data = await res.json();
                    this.errors = data.errors || {};
                    alert(Object.values(this.errors).flat().join('\n'));
                    return;
                }
                if (!res.ok) {
                    alert('Gagal menyimpan transaksi.');
                    return;
                }

                window.location.reload();
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan saat menyimpan.');
            } finally {
                this.saving = false;
            }
        },
    };
}