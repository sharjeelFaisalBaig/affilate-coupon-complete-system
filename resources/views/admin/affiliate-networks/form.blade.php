@extends('admin.layouts.app')

@section('title', $network->exists ? 'Edit Affiliate Network' : 'Add Affiliate Network')

@section('content')
    <div class="max-w-xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ $network->exists ? route('admin.affiliate-networks.update', $network) : route('admin.affiliate-networks.store') }}"
              class="space-y-5">
            @csrf
            @if ($network->exists) @method('PUT') @endif

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Network Name</label>
                <input type="text" name="network_name" value="{{ old('network_name', $network->network_name) }}" required placeholder="e.g. Amazon Associates"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Tracking ID</label>
                <input type="text" name="tracking_id" value="{{ old('tracking_id', $network->tracking_id) }}"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">API Key</label>
                <input type="password" name="api_key" placeholder="{{ $network->api_key ? '•••••••• (leave blank to keep unchanged)' : '' }}"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">API Secret</label>
                <input type="password" name="api_secret" placeholder="{{ $network->api_secret ? '•••••••• (leave blank to keep unchanged)' : '' }}"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Sync Status</label>
                <select name="sync_status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="pending" @selected(old('sync_status', $network->sync_status ?? 'pending') === 'pending')>Pending</option>
                    <option value="synced" @selected(old('sync_status', $network->sync_status) === 'synced')>Synced</option>
                    <option value="failed" @selected(old('sync_status', $network->sync_status) === 'failed')>Failed</option>
                </select>
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $network->is_active))
                       class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                <span class="text-sm text-gray-700">Connected / Active</span>
            </label>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    {{ $network->exists ? 'Save Changes' : 'Create' }}
                </button>
                <a href="{{ route('admin.affiliate-networks.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:-translate-y-0.5 hover:border-gray-400 hover:bg-gray-50 hover:shadow-sm active:translate-y-0">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
