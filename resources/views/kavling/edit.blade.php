@extends('layouts.app')

@section('content')
<div class="p-6 max-w-2xl">
    <p class="text-sm text-gray-500 mb-1">Data Master &gt; Master Kavling &gt; <span class="text-gray-800 font-medium">Edit Kavling</span></p>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Kavling</h1>

    <form action="{{ route('kavling.update', $kavling) }}" method="POST" class="bg-white border rounded-xl p-6 space-y-4">
        @csrf
        @method('PUT')
        @include('kavling._form')

        <div class="flex gap-3 pt-4">
            <button type="submit" class="bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">
                Update
            </button>
            <a href="{{ route('kavling.index') }}" class="px-5 py-2 rounded-lg text-sm font-medium border text-gray-700 hover:bg-gray-50">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection