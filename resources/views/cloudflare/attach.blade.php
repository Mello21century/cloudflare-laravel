@extends('cloudflare::layouts.main')

@section('contents')

    <form action="{{ route('cloudflare.store') }}" method="POST">
        @csrf
        <input type="hidden" name="zoneId" value="{{ $zoneId }}">
        <input type="text" name="ip" placeholder="IP Address" class="w-full border rounded mb-3" id="ip">
        @error('ip')
        <div class="bg-red-600 text-white px-4 py-2 rounded">{{ $message }}</div>
        @enderror
        <div class="text-center">
            <button type="submit" class="bg-blue-600 text-white px-2 py-1 text-xs rounded-lg">Save
            </button>
        </div>
    </form>
@endsection
