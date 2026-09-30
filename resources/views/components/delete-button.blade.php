@props(['action', 'message' => 'Data yang dihapus tidak bisa dikembalikan.', 'title' => 'Hapus data ini?', 'label' => null, 'ok' => 'Hapus'])
{{-- Tombol hapus dengan dialog konfirmasi seragam (bukan dialog bawaan browser). --}}
<form method="POST" action="{{ $action }}" class="inline-flex"
      data-confirm="{{ $message }}" data-confirm-title="{{ $title }}" data-confirm-ok="{{ $ok }}">
    @csrf @method('DELETE')
    @if ($label)
        <x-button type="submit" variant="danger" icon="trash" {{ $attributes }}>{{ $label }}</x-button>
    @else
        <x-icon-button type="submit" icon="trash" label="Hapus" variant="danger" {{ $attributes }}/>
    @endif
</form>
