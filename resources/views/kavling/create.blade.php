@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8">
    <h1 class="text-xl font-bold mb-6">Tambah Kavling</h1>

    <form action="{{ route('kavling.store') }}" method="POST">
        @csrf
        @include('kavling._form')

        <div class="flex gap-2 mt-6">
            <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm">Simpan</button>
            <a href="{{ route('kavling.index') }}" class="border px-4 py-2 rounded-lg text-sm">Batal</a>
        </div>
    </form>
</div>
@endsection