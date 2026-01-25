@extends('cloudflare::layouts.main')

@section('contents')

    <form action="{{ route('cloudflare.update', $zoneId) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="zoneId" value="{{ $zoneId }}">
        <label for="ip">IP</label>
        <input type="text" name="ip" placeholder="IP Address" class="w-full border rounded mb-3" id="ip">
        @error('ip')
        <div class="bg-red-600 text-white px-4 py-2 rounded">{{ $message }}</div>
        @enderror
        <label for="spf">SPF</label>
        <input type="text" name="spf" placeholder="SPF" class="w-full border rounded mb-3" id="spf">
        @error('spf')
        <div class="bg-red-600 text-white px-4 py-2 rounded">{{ $message }}</div>
        @enderror
        <label for="dkim">DKIM</label>
        <input type="text" name="dkim" placeholder="DKIM" class="w-full border rounded mb-3" id="dkim">
        @error('dkim')
        <div class="bg-red-600 text-white px-4 py-2 rounded">{{ $message }}</div>
        @enderror

        <div class="text-center">
            <button type="submit" class="bg-blue-600 text-white px-2 py-1 text-xs rounded-lg">Save
            </button>
        </div>
    </form>
@endsection
