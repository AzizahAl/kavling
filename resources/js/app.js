import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Chart from 'chart.js/auto';

window.Chart = Chart;
Chart.defaults.font.family = "'Inter Variable', 'Inter', system-ui, sans-serif";
Chart.defaults.color = '#64748b';

// ---------- Format Indonesia ----------
const nf = new Intl.NumberFormat('id-ID');
window.rupiah = (n) => 'Rp' + nf.format(Math.round(Number(n) || 0));
window.angka = (n) => nf.format(Math.round(Number(n) || 0));
window.parseAngka = (s) => Number(String(s ?? '').replace(/[^\d-]/g, '')) || 0;

// ---------- Input uang: tampil "1.500.000", terkirim "1500000" ----------
Alpine.data('moneyInput', (initial = '') => ({
    raw: initial === null || initial === '' ? '' : String(Math.round(Number(initial))),
    init() {
        // Nilai bisa diisi dari luar: $dispatch('set-money', { name: 'nominal_dp', value: 7350000 })
        window.addEventListener('set-money', (e) => {
            if (e.detail?.name === this.$el.dataset.name) {
                this.raw = e.detail.value === '' || e.detail.value === null ? '' : String(Math.round(Number(e.detail.value)));
            }
        });
    },
    get display() { return this.raw === '' ? '' : nf.format(Number(this.raw)); },
    set display(v) { const d = String(v).replace(/\D/g, ''); this.raw = d === '' ? '' : String(Number(d)); },
    onInput(e) {
        this.display = e.target.value;
        e.target.value = this.display;
        this.$dispatch('money-changed', { name: this.$el.dataset.name, value: Number(this.raw || 0) });
    },
}));

// ---------- Dialog konfirmasi global ----------
Alpine.store('confirm', {
    open: false, title: '', message: '', okText: 'Ya, lanjutkan', danger: true, onOk: null,
    ask({ title = 'Konfirmasi', message = 'Apakah Anda yakin?', okText = 'Ya, lanjutkan', danger = true }, onOk) {
        Object.assign(this, { title, message, okText, danger, onOk, open: true });
    },
    ok() { this.open = false; const f = this.onOk; this.onOk = null; f && f(); },
    cancel() { this.open = false; this.onOk = null; },
});

// Form dengan atribut data-confirm="Pesan" meminta konfirmasi dulu sebelum dikirim.
document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement) || !form.dataset.confirm || form.dataset.confirmed) return;
    e.preventDefault();
    Alpine.store('confirm').ask({
        title: form.dataset.confirmTitle || 'Konfirmasi',
        message: form.dataset.confirm,
        okText: form.dataset.confirmOk || 'Ya, lanjutkan',
        danger: form.dataset.confirmDanger !== 'false',
    }, () => { form.dataset.confirmed = '1'; form.requestSubmit ? form.requestSubmit() : form.submit(); });
}, true);

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();
