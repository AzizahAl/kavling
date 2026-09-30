@props(['name', 'type' => 'text', 'value' => null])
<input type="{{ $type }}" name="{{ $name }}" id="{{ $attributes->get('id', $name) }}" value="{{ $type === 'password' ? '' : old($name, $value) }}"
       {{ $attributes->except('id')->class(['form-input', 'is-invalid' => $errors->has($name)]) }}>
