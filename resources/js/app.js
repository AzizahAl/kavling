import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Chart from 'chart.js/auto';

// ---------- Grafik: palet hijau & netral ----------
window.Chart = Chart;
Chart.defaults.font.family = "'Inter Variable', 'Inter', system-ui, sans-serif";
Chart.defaults.font.size = 12;
Chart.defaults.color = '#64748b';
Chart.defaults.borderColor = '#eef2f6';
Chart.defaults.plugins.tooltip.backgroundColor = '#0f172a';
Chart.defaults.plugins.tooltip.padding = 10;
Chart.defaults.plugins.tooltip.cornerRadius = 8;
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.boxWidth = 8;
window.warnaGrafik = {
    utama: '#2e8b62', utamaMuda: '#aedec7', gelap: '#1a5a3f', netral: '#94a3b8', netralMuda: '#e2e8f0',
    teal: '#14b8a6', biru: '#38bdf8', kuning: '#f59e0b', ungu: '#8b5cf6', abuGelap: '#334155', merah: '#ef4444',
};

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

// ---------- Dropdown pilihan (pengganti <select>) ----------
// Nilai disimpan di <input type=hidden name=...>, mendukung x-model (x-modelable="nilai"),
// keyboard (panah, Enter, Esc, ketik untuk mencari), dan panel yang tidak terpotong wadah bergulir.
Alpine.data('pilihan', (cfg) => ({
    nilai: cfg.nilai ?? '',
    opsiStatis: cfg.opsi ?? [],
    placeholder: cfg.placeholder ?? 'Pilih…',
    cariAktif: !!cfg.cari,
    buka: false, q: '', sorot: -1, pos: {},
    get opsi() {
        const ekspr = this.$root.dataset.opsiExpr;
        if (!ekspr) return this.opsiStatis;
        const hasil = Alpine.evaluate(this.$root, ekspr) || [];
        return hasil.map((o) => ({ v: String(o.v ?? o.id ?? o.value), l: o.l ?? o.label }));
    },
    get tersaring() {
        const q = this.q.trim().toLowerCase();
        return q ? this.opsi.filter((o) => o.l.toLowerCase().includes(q)) : this.opsi;
    },
    get label() {
        const o = this.opsi.find((o) => o.v === String(this.nilai ?? ''));
        return o ? o.l : '';
    },
    init() {
        this.$watch('nilai', (v, lama) => {
            if (String(v ?? '') === String(lama ?? '')) return;
            this.$nextTick(() => this.$refs.plh_nilai.dispatchEvent(new Event('change', { bubbles: true })));
        });
    },
    atur() {
        const r = this.$refs.plh_tombol.getBoundingClientRect();
        const tinggi = Math.min(320, 44 + this.tersaring.length * 40);
        const bawah = window.innerHeight - r.bottom;
        const keAtas = bawah < tinggi + 12 && r.top > bawah;
        this.pos = {
            left: Math.max(8, Math.min(r.left, window.innerWidth - Math.max(r.width, 200) - 8)) + 'px',
            width: Math.max(r.width, 200) + 'px',
            top: keAtas ? 'auto' : r.bottom + 6 + 'px',
            bottom: keAtas ? window.innerHeight - r.top + 6 + 'px' : 'auto',
        };
    },
    bukaPanel() {
        if (this.$refs.plh_tombol.disabled) return;
        this.q = ''; this.atur(); this.buka = true;
        this.sorot = Math.max(0, this.tersaring.findIndex((o) => o.v === String(this.nilai)));
        this.$nextTick(() => { (this.$refs.plh_cari || this.$refs.plh_daftar)?.focus(); this.gulirKeSorot(); });
    },
    tutup(fokus = true) { this.buka = false; if (fokus) this.$refs.plh_tombol.focus(); },
    pilih(o) { this.nilai = o.v; this.tutup(); },
    gulirKeSorot() { this.$nextTick(() => this.$refs.plh_daftar?.querySelector(`[data-i="${this.sorot}"]`)?.scrollIntoView({ block: 'nearest' })); },
    tombolKey(e) {
        if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(e.key)) { e.preventDefault(); this.bukaPanel(); }
    },
    panelKey(e) {
        const n = this.tersaring.length;
        if (e.key === 'ArrowDown') { e.preventDefault(); this.sorot = (this.sorot + 1) % n; this.gulirKeSorot(); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); this.sorot = (this.sorot - 1 + n) % n; this.gulirKeSorot(); }
        else if (e.key === 'Home') { e.preventDefault(); this.sorot = 0; this.gulirKeSorot(); }
        else if (e.key === 'End') { e.preventDefault(); this.sorot = n - 1; this.gulirKeSorot(); }
        else if (e.key === 'Enter') { e.preventDefault(); if (this.tersaring[this.sorot]) this.pilih(this.tersaring[this.sorot]); }
        else if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); this.tutup(); }
        else if (e.key === 'Tab') { this.tutup(false); }
        else if (!this.cariAktif && e.key.length === 1) {
            const i = this.tersaring.findIndex((o) => o.l.toLowerCase().startsWith(e.key.toLowerCase()));
            if (i >= 0) { this.sorot = i; this.gulirKeSorot(); }
        }
    },
}));

// ---------- Tooltip: x-tip="Teks" (muncul dengan jeda, tidak terpotong tepi layar) ----------
Alpine.directive('tip', (el, { expression }, { evaluateLater, effect, cleanup }) => {
    let teks = expression;
    try { const ambil = evaluateLater(expression); effect(() => ambil((v) => { teks = v; })); } catch { teks = expression; }
    let tip, jeda;
    const tampil = () => {
        jeda = setTimeout(() => {
            if (!teks) return;
            tip = document.createElement('div');
            tip.className = 'tip'; tip.setAttribute('role', 'tooltip'); tip.textContent = teks; tip.style.opacity = '0';
            document.body.appendChild(tip);
            const r = el.getBoundingClientRect(), t = tip.getBoundingClientRect();
            let top = r.top - t.height - 8;
            if (top < 8) top = r.bottom + 8;
            const left = Math.min(Math.max(8, r.left + r.width / 2 - t.width / 2), window.innerWidth - t.width - 8);
            tip.style.top = top + 'px'; tip.style.left = left + 'px';
            requestAnimationFrame(() => tip && (tip.style.opacity = '1'));
        }, 350);
    };
    const sembunyi = () => { clearTimeout(jeda); tip?.remove(); tip = null; };
    el.addEventListener('mouseenter', tampil); el.addEventListener('focus', tampil);
    ['mouseleave', 'blur', 'click'].forEach((ev) => el.addEventListener(ev, sembunyi));
    if (!el.getAttribute('aria-label')) el.setAttribute('aria-label', teks);
    cleanup(sembunyi);
});

// ---------- Notifikasi (toast) ----------
// Popup pesan di tengah layar, tampil satu per satu (antrean).
// Berhasil/info menutup sendiri setelah 3 detik; gagal/perhatian menunggu tombol OK.
Alpine.store('toast', {
    daftar: [], id: 0, pewaktu: null,
    tambah(jenis, pesan) {
        this.daftar.push({ id: ++this.id, jenis, pesan, tampil: true });
        if (this.daftar.length === 1) this.jadwalkan();
    },
    jadwalkan() {
        clearTimeout(this.pewaktu);
        const t = this.daftar[0];
        if (t && (t.jenis === 'success' || t.jenis === 'info')) this.pewaktu = setTimeout(() => this.hapus(t.id), 3000);
    },
    hapus(id) {
        const t = this.daftar.find((x) => x.id === id);
        if (!t || !t.tampil) return;
        t.tampil = false;
        setTimeout(() => { this.daftar = this.daftar.filter((x) => x.id !== id); this.jadwalkan(); }, 180);
    },
});
window.toast = (jenis, pesan) => Alpine.store('toast').tambah(jenis, pesan);

// ---------- Dialog konfirmasi global ----------
Alpine.store('confirm', {
    open: false, title: '', message: '', okText: 'Ya, lanjutkan', danger: true, onOk: null,
    ask({ title = 'Konfirmasi', message = 'Lanjutkan?', okText = 'Ya, lanjutkan', danger = true }, onOk) {
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

// Cegah kirim ganda: tombol submit dinonaktifkan sesaat setelah form dikirim.
document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement) || e.defaultPrevented || form.method.toLowerCase() === 'get') return;
    setTimeout(() => form.querySelectorAll('button[type=submit]').forEach((b) => { b.disabled = true; b.dataset.dikunci = '1'; }), 0);
});
window.addEventListener('pageshow', () => document.querySelectorAll('button[data-dikunci]').forEach((b) => { b.disabled = false; delete b.dataset.dikunci; }));

// Pesan validasi selalu dari server (Bahasa Indonesia, tepat di bawah kolom),
// bukan gelembung bawaan browser yang bahasanya mengikuti browser.
const matikanValidasiBrowser = () => document.querySelectorAll('form').forEach((f) => { f.noValidate = true; });
document.addEventListener('DOMContentLoaded', matikanValidasiBrowser);

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();
