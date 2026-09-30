@props(['name', 'options' => [], 'value' => null, 'placeholder' => null])
{{-- $options = [value => label]. Bisa juga isi <option> sendiri lewat slot. --}}
@php $pilih = (string) old($name, $value); @endphp
<select name="{{ $name }}" id="{{ $attributes->get('id', $name) }}" {{ $attributes->except('id')->class(['form-input pr-8', 'is-invalid' => $errors->has($name)]) }}>
    @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
    @foreach ($options as $val => $label)
        <option value="{{ $val }}" @selected($pilih === (string) $val)>{{ $label }}</option>
    @endforeach
    {{ $slot }}
</select>
