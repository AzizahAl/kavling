@props(['name', 'type' => 'text', 'value' => null])
{{-- Isian teks/angka/email/tanggal. Nilai lama (old) tetap terisi setelah gagal validasi. --}}
<input type="{{ $type }}" name="{{ $name }}" id="{{ $attributes->get('id', $name) }}"
       @unless ($type === 'password') value="{{ old($name, $value) }}" @endunless
       @if ($errors->has($name)) aria-invalid="true" @endif
       {{ $attributes->except('id')->class(['form-input', 'is-invalid' => $errors->has($name)]) }}>
