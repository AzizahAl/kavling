@props(['name', 'value' => null])
{{--
    Nominal Rupiah: tampil dengan titik ribuan, yang terkirim angka murni.
    Setiap perubahan memancarkan "money-changed" {name, value}; nilai bisa diisi lewat event "set-money".
--}}
<div class="relative" x-data="moneyInput(@js(old($name, $value)))" data-name="{{ $name }}">
    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-400">Rp</span>
    <input type="text" inputmode="numeric" autocomplete="off" :value="display" x-on:input="onInput($event)"
           id="{{ $attributes->get('id', $name) }}" placeholder="0"
           @if ($errors->has($name)) aria-invalid="true" @endif
           {{ $attributes->except('id')->class(['form-input pl-9 tabular-nums', 'is-invalid' => $errors->has($name)]) }}>
    <input type="hidden" name="{{ $name }}" :value="raw">
</div>
