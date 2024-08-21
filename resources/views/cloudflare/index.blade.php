@extends('cloudflare::layouts.main')

@section('contents')
    <table class="w-full bg-gray-100 rounded-2xl overflow-hidden">
        @foreach($domains as $domain)
            <tr class="{{ $loop->index%2==0 ? 'bg-gray-100' : 'bg-gray-50' }}">
                <td class="p-2">{{ $domain->id }}</td>
                <td class="p-2">{{ $domain->name }}</td>
                <td class="p-2">{{ $domain->account->name }}</td>
                <td class="p-2">
                    <a href="{{ route('cloudflare.attach',$domain->id) }}"
                       class="bg-green-600 text-white px-2 py-1 text-xs rounded-lg">Create Default cPanel DNS</a>
                    <form action="{{ route('cloudflare.destroy',$domain->id) }}"
                          onsubmit="return confirm('Are you sure?')" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-2 py-1 text-xs rounded-lg">Clear Old DNS
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>
@endsection
