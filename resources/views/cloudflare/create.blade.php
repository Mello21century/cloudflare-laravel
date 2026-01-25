@extends('cloudflare::layouts.main')

@section('contents')

    <form action="{{ route('cloudflare.store') }}" method="POST">
        @csrf
        <label for="domain">Domain</label>
        <input type="text" name="domain" placeholder="Domain" class="w-full border rounded mb-3" id="domain">
        @error('domain')
        <div class="bg-red-600 text-white px-4 py-2 rounded">{{ $message }}</div>
        @enderror

        <div class="text-center">
            <button type="submit" class="bg-blue-600 text-white px-2 py-1 text-xs rounded-lg">Save
            </button>
        </div>
    </form>
@endsection
