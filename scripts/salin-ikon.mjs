// Menyalin ikon Lucide yang dipakai aplikasi ke resources/ikon/lucide.json (disimpan di git, tanpa CDN).
// Jalankan setelah menambah ikon baru ke daftar di bawah:  node scripts/salin-ikon.mjs
import fs from 'node:fs';

// nama di aplikasi => nama file Lucide
const IKON = {
    home: 'house', building: 'building-2', grid: 'layout-grid', tag: 'tag', users: 'users', user: 'user',
    cart: 'shopping-cart', clipboard: 'clipboard-list', wallet: 'wallet', cashflow: 'arrow-left-right',
    banknotes: 'banknote', cog: 'settings', document: 'file-text', 'check-badge': 'badge-check', calendar: 'calendar-days',
    funnel: 'funnel', chart: 'chart-column', receipt: 'receipt', plus: 'plus', pencil: 'pencil', trash: 'trash-2',
    eye: 'eye', 'eye-off': 'eye-off', search: 'search', x: 'x', menu: 'menu', logout: 'log-out', printer: 'printer',
    download: 'download', 'arrow-left': 'arrow-left', 'arrow-right': 'arrow-right', 'chevron-down': 'chevron-down',
    'chevron-right': 'chevron-right', 'chevron-left': 'chevron-left', 'chevron-up': 'chevron-up', 'chevrons-up-down': 'chevrons-up-down',
    check: 'check', 'check-circle': 'circle-check', 'x-circle': 'circle-x', warning: 'triangle-alert', 'alert': 'circle-alert',
    info: 'info', inbox: 'inbox', phone: 'phone', clock: 'clock', ban: 'ban', lock: 'lock', scale: 'scale',
    'filter-x': 'filter-x', more: 'ellipsis', undo: 'undo-2', 'map-pin': 'map-pin', 'trending-up': 'trending-up',
    key: 'key-round', shield: 'shield-check', sliders: 'list-filter', reset: 'rotate-ccw', landmark: 'landmark', coins: 'hand-coins',
};

const hasil = {};
for (const [nama, file] of Object.entries(IKON)) {
    const svg = fs.readFileSync(`node_modules/lucide-static/icons/${file}.svg`, 'utf8');
    hasil[nama] = svg.slice(svg.indexOf('>', svg.indexOf('<svg')) + 1, svg.lastIndexOf('</svg>')).replace(/\s*\n\s*/g, '').trim();
}

fs.mkdirSync('resources/ikon', { recursive: true });
fs.writeFileSync('resources/ikon/lucide.json', JSON.stringify(hasil, null, 1) + '\n');
console.log(`${Object.keys(hasil).length} ikon disalin ke resources/ikon/lucide.json`);
