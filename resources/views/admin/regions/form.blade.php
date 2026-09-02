@extends('admin.layouts.app')

@section('title', $region->exists ? 'Edit Region' : 'Add Region')

@section('content')
    <div class="max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ $region->exists ? route('admin.regions.update', $region) : route('admin.regions.store') }}"
              enctype="multipart/form-data" class="space-y-5">
            @csrf
            @if ($region->exists) @method('PUT') @endif

            @unless ($region->exists)
                <p class="rounded-md bg-amber-50 px-3 py-2 text-xs text-amber-700">New regions are created Disabled — enable it from the list once it's ready to publish.</p>
            @endunless

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Prefix Slug</label>
                    <input type="text" name="code" value="{{ old('code', $region->code) }}" required maxlength="4" placeholder="e.g. us"
                           class="block w-full rounded-md border-gray-300 lowercase shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">URL prefix, e.g. /us, /au. 2-4 letters.</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Region Title</label>
                    <input type="text" name="name" value="{{ old('name', $region->name) }}" required placeholder="e.g. USA"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Favicon</label>
                @if ($region->favicon_path)
                    <img src="{{ Storage::url($region->favicon_path) }}" alt="" width="32" height="32" class="mb-2 h-8 w-8 rounded border border-gray-200 object-contain">
                @endif
                <input type="file" name="favicon" accept="image/*,.ico"
                       class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
                <p class="mt-1 text-xs text-gray-400">* Optimal size: 32x32px, .ico or .png.</p>
            </div>

            @if ($region->exists)
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Sort Order</label>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $region->sort_order) }}"
                           class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            @endif

            <fieldset class="rounded-md border border-gray-200 p-4 space-y-3">
                <legend class="px-1 text-sm font-medium text-gray-700">Global Region Scripts</legend>
                <p class="text-xs text-gray-400">Injected on every page in this region — tracking pixels, analytics, etc.</p>
                @foreach ([
                    'head_start_script' => 'Start of <head>',
                    'head_end_script' => 'End of <head>',
                    'body_start_script' => 'Start of <body>',
                    'body_end_script' => 'End of <body>',
                ] as $field => $label)
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">{{ $label }}</label>
                        <textarea name="{{ $field }}" rows="2"
                                  class="block w-full rounded-md border-gray-300 font-mono text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old($field, $region->$field) }}</textarea>
                    </div>
                @endforeach
            </fieldset>

            @if ($region->exists)
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $region->is_active)) @disabled($region->is_default)
                           class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                    <span class="text-sm text-gray-700">Enabled</span>
                    @if ($region->is_default)
                        <span class="text-xs text-gray-400">(the default region can't be disabled)</span>
                    @endif
                </label>
            @endif

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">
                    {{ $region->exists ? 'Save Changes' : 'Create Region' }}
                </button>
                <a href="{{ route('admin.regions.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
