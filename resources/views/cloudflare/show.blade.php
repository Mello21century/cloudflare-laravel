@php use Space\Cloudflare\Enums\DnsType; @endphp
@extends('cloudflare::layouts.main')

@section('contents')

    <div id="list">

        @foreach ($records as $record)
            <form action="{{ route('cloudflare.update-dns-record',['zoneId' => $zoneId, 'recordId' => $record->id]) }}"
                  method="POST">
                @csrf
                <div class="flex items-center justify-center gap-3">
                    <div class="flex flex-col items-start gap-1">
                        <label for="name">Name</label>
                        <input type="text" name="name" placeholder="Name" class="w-full border rounded mb-3" id="name"
                               value="{{ $record->name }}">
                        @error('name')
                        <div class="bg-red-600 text-white px-4 py-2 rounded">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex flex-col items-start  gap-1">
                        <label for="type">Type</label>
                        <select name="type" id="type">
                            @foreach (DnsType::cases() as $type)
                                <option value="{{ $type->value }}" {{ ($type->value == $record->type) ? 'selected' : '' }}>
                                    {{ $type->value }}
                                </option>
                            @endforeach
                        </select>
                        @error('content')
                        <div class="bg-red-600 text-white px-4 py-2 rounded">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="flex flex-col items-start  gap-1">
                        <label for="content">Content</label>
                        <textarea type="text" name="content" placeholder="Content" class="w-full border rounded mb-3"
                                  id="content">{{ $record->content }}</textarea>
                        @error('content')
                        <div class="bg-red-600 text-white px-4 py-2 rounded">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="flex flex-col items-start  gap-1">
                        <label for="proxied">Proxied</label>
                        <input type="checkbox" name="proxied" id="proxied"
                               value="1" {{ $record->proxied ? 'checked' : '' }}>
                        @error('proxied')
                        <div class="bg-red-600 text-white px-4 py-2 rounded">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="text-center">
                        <button type="submit" class="bg-blue-600 text-white px-2 py-1 text-xs rounded-lg">Save
                        </button>
                    </div>
                    <div class="text-center">
                        <a href="{{ route('cloudflare.delete-dns-record',['zoneId' => $zoneId, 'recordId' => $record->id]) }}"
                           onclick="return confirm('Are you sure?')"
                           class="bg-red-600 text-white px-2 py-1 text-xs rounded-lg">Delete
                        </a>
                    </div>
                </div>
            </form>
        @endforeach

        <form class="template hidden" action="{{ route('cloudflare.add-dns-record',['zoneId' => $zoneId]) }}" method="POST">
            @csrf
            <div class="flex items-center justify-center gap-3">
                <div class="flex flex-col items-start gap-1">
                    <label for="name">Name</label>
                    <input type="text" name="name" placeholder="Name" class="w-full border rounded mb-3" id="name"
                           value="">
                </div>

                <div class="flex flex-col items-start  gap-1">
                    <label for="type">Type</label>
                    <select name="type" id="type">
                        @foreach (DnsType::cases() as $type)
                            <option value="{{ $type->value }}">
                                {{ $type->value }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col items-start  gap-1">
                    <label for="content">Content</label>
                    <textarea type="text" name="content" placeholder="Content" class="w-full border rounded mb-3"
                              id="content"></textarea>
                </div>
                <div class="flex flex-col items-start  gap-1">
                    <label for="proxied">Proxied</label>
                    <input type="checkbox" name="proxied" id="proxied"
                           value="1">
                </div>
                <div class="text-center">
                    <button type="submit" class="bg-blue-600 text-white px-2 py-1 text-xs rounded-lg">Save
                    </button>
                    <div class="text-center">
                        <a href="#"
                           onclick="return confirm('Are you sure?')"
                           class="bg-red-600 text-white px-2 py-1 text-xs rounded-lg">Delete
                        </a>
                    </div>
                </div>
            </div>
        </form>

    </div>
    <a href="#Add-More" id="add-more" class="bg-green-600 text-white px-2 py-1 text-xs rounded-lg">+ Add More</a>

@endsection

@push('js')
    <script>
        const template = document.querySelector('.template');
        const addMore = document.querySelector('#add-more');
        const form = document.getElementById('list');
        addMore.addEventListener('click', () => {
            const clone = template.cloneNode(true);
            clone.classList.remove('hidden');
            form.appendChild(clone);
        });
    </script>
@endpush
