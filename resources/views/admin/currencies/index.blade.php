@extends('admin.layouts.app')

@section('title', 'Currencies')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">Shared currency list available for regions to select from — not region-specific.</p>
        <a href="{{ route('admin.currencies.create') }}" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">
            + Add Currency
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Symbol</th>
                    <th class="px-4 py-3">ISO Code</th>
                    <th class="px-4 py-3">Regions Using It</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($currencies as $currency)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $currency->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $currency->symbol }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $currency->iso_code }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $currency->regions_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.currencies.edit', $currency) }}" class="font-medium text-emerald-600 hover:text-emerald-700">Edit</a>
                            <form action="{{ route('admin.currencies.destroy', $currency) }}" method="POST" class="ml-3 inline"
                                  onsubmit="return confirm('Delete this currency?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No currencies yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
