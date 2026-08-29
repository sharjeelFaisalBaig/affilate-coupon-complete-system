@extends('admin.layouts.app')

@section('title', $type->exists ? 'Edit Promotion Type' : 'Add Promotion Type')

@section('content')
    <div class="max-w-xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ $type->exists ? route('admin.promotion-types.update', $type) : route('admin.promotion-types.store') }}"
              class="space-y-5">
            @csrf
            @if ($type->exists) @method('PUT') @endif

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Type Title</label>
                <input type="text" name="title" value="{{ old('title', $type->title) }}" required placeholder="e.g. Free Shipping" maxlength="255"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <p class="mt-1 text-xs text-gray-400">Optimal length: ~20 characters.</p>
            </div>

            @if ($type->exists)
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Slug</label>
                    <p class="text-sm text-gray-500">{{ $type->slug }}</p>
                </div>
            @endif

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Discount Format</label>
                @if ($type->is_system_default)
                    <p class="text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $type->discount_format)) }} <span class="text-xs text-gray-400">(fixed for system defaults)</span></p>
                @else
                    <p class="text-sm text-gray-500">Custom (text-only) <span class="text-xs text-gray-400">— admin enters a badge label instead of a numeric rate, e.g. "Free Shipping", "Buy 1 Get 1 Free"</span></p>
                @endif
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">
                    {{ $type->exists ? 'Save Changes' : 'Create Type' }}
                </button>
                <a href="{{ route('admin.promotion-types.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
