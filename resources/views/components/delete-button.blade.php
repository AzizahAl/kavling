@props(['action', 'message' => 'Data yang dihapus tidak dapat dikembalikan.', 'title' => 'Hapus data ini?', 'label' => null])
<form method="POST" action="{{ $action }}" class="inline"
      data-confirm="{{ $message }}" data-confirm-title="{{ $title }}" data-confirm-ok="Ya, hapus">
    @csrf @method('DELETE')
    @if ($label)
        <button type="submit" {{ $attributes->merge(['class' => 'btn btn-danger']) }}><x-icon name="trash" class="h-4 w-4"/>{{ $label }}</button>
    @else
        <button type="submit" {{ $attributes->merge(['class' => 'btn-icon btn-icon-danger']) }} title="Hapus" aria-label="Hapus"><x-icon name="trash" class="h-[18px] w-[18px]"/></button>
    @endif
</form>
