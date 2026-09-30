{{-- Isian identitas konsumen. $p = awalan nama field (mis. "konsumen_" di form transaksi), $k = nilai awal. --}}
@php $p = $p ?? ''; $k = $k ?? null; @endphp
<div class="grid gap-4 sm:grid-cols-2">
    <x-field label="Nama Lengkap (sesuai KTP)" :name="$p . 'nama_lengkap'" required class="sm:col-span-2">
        <x-input :name="$p . 'nama_lengkap'" :value="$k?->nama_lengkap" autocomplete="off"/>
    </x-field>
    <x-field label="NIK" :name="$p . 'nik'" required hint="16 digit angka">
        <x-input :name="$p . 'nik'" :value="$k?->nik" inputmode="numeric" maxlength="16"/>
    </x-field>
    <x-field label="No. HP / WhatsApp" :name="$p . 'no_hp'" required>
        <x-input :name="$p . 'no_hp'" :value="$k?->no_hp" type="tel" inputmode="tel"/>
    </x-field>
    <x-field label="Email" :name="$p . 'email'">
        <x-input :name="$p . 'email'" :value="$k?->email" type="email"/>
    </x-field>
    <x-field label="Pekerjaan" :name="$p . 'pekerjaan'">
        <x-input :name="$p . 'pekerjaan'" :value="$k?->pekerjaan"/>
    </x-field>
    <x-field label="Alamat (sesuai KTP)" :name="$p . 'alamat'" required class="sm:col-span-2">
        <x-textarea :name="$p . 'alamat'" :value="$k?->alamat" rows="2"/>
    </x-field>
    @if (empty($ringkas))
        <x-field label="Label" :name="$p . 'label'" hint="Opsional, mis. Konsumen Prioritas">
            <x-input :name="$p . 'label'" :value="$k?->label"/>
        </x-field>
        <x-field label="Catatan" :name="$p . 'catatan'">
            <x-input :name="$p . 'catatan'" :value="$k?->catatan"/>
        </x-field>
    @endif
</div>
