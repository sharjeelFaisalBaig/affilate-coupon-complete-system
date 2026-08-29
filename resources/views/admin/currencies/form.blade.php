@extends('admin.layouts.app')

@section('title', $currency->exists ? 'Edit Currency' : 'Add Currency')

@section('content')
    <div class="max-w-xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ $currency->exists ? route('admin.currencies.update', $currency) : route('admin.currencies.store') }}"
              class="space-y-5">
            @csrf
            @if ($currency->exists) @method('PUT') @endif

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Currency Name</label>
                <input type="text" name="name" value="{{ old('name', $currency->name) }}" required placeholder="e.g. US Dollar"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Symbol</label>
                    <input type="text" name="symbol" value="{{ old('symbol', $currency->symbol) }}" required placeholder="e.g. $"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">ISO Code</label>
                    <input type="text" name="iso_code" value="{{ old('iso_code', $currency->iso_code) }}" required maxlength="3" placeholder="e.g. USD"
                           class="block w-full rounded-md border-gray-300 uppercase shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">
                    {{ $currency->exists ? 'Save Changes' : 'Create' }}
                </button>
                <a href="{{ route('admin.currencies.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
