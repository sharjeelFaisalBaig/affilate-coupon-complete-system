@extends('admin.layouts.app')

@section('title', 'Affiliate Networks')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">Credentials for affiliate networks used to build tracking links for this region.</p>
        <a href="{{ route('admin.affiliate-networks.create') }}" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">
            + Add Network
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Network</th>
                    <th class="px-4 py-3">Tracking ID</th>
                    <th class="px-4 py-3">Sync Status</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($networks as $network)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $network->network_name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $network->tracking_id ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ ucfirst($network->sync_status) }}</td>
                        <td class="px-4 py-3">
                            @if ($network->is_active)
                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Connected</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-500">Not Connected</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.affiliate-networks.edit', $network) }}" class="font-medium text-emerald-600 hover:text-emerald-700">Edit</a>
                            <form action="{{ route('admin.affiliate-networks.destroy', $network) }}" method="POST" class="ml-3 inline"
                                  onsubmit="return confirm('Delete this affiliate network?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No affiliate networks yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
