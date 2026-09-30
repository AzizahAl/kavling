@props(['name', 'value' => null, 'rows' => 3])
<textarea name="{{ $name }}" id="{{ $attributes->get('id', $name) }}" rows="{{ $rows }}"
          @if ($errors->has($name)) aria-invalid="true" @endif
          {{ $attributes->except('id')->class(['form-input', 'is-invalid' => $errors->has($name)]) }}>{{ old($name, $value) }}</textarea>
