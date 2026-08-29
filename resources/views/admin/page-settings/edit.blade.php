@extends('admin.layouts.app')

@section('title', $pageLabel.' Settings')

@section('content')
    <p class="mb-4 text-sm text-gray-500">Heading, intro copy, and SEO fields for this page only.</p>

    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.page-settings.update', $pageKey) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Heading (H1)</label>
                <input type="text" name="heading" value="{{ old('heading', $settings->heading) }}"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Subheading / Intro Copy</label>
                <textarea name="subheading" rows="2"
                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('subheading', $settings->subheading) }}</textarea>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $settings->meta_title) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Meta Description</label>
                    <input type="text" name="meta_description" value="{{ old('meta_description', $settings->meta_description) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">OG Title</label>
                    <input type="text" name="og_title" value="{{ old('og_title', $settings->og_title) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">OG Description</label>
                    <input type="text" name="og_description" value="{{ old('og_description', $settings->og_description) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>

            <div class="flex gap-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="robots_index" value="1" @checked(old('robots_index', $settings->exists ? $settings->robots_index : true))
                           class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                    <span class="text-sm text-gray-700">Robots: Index</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="robots_follow" value="1" @checked(old('robots_follow', $settings->exists ? $settings->robots_follow : true))
                           class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                    <span class="text-sm text-gray-700">Robots: Follow</span>
                </label>
            </div>

            <details class="rounded-md border border-gray-200 p-3">
                <summary class="cursor-pointer text-xs font-medium text-gray-500">Schema &amp; Script Injection</summary>
                <div class="mt-3 space-y-3">
                    <textarea name="schema_script" rows="2" placeholder="SEO Schema / custom tracking scripts"
                              class="block w-full rounded-md border-gray-300 font-mono text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('schema_script', $settings->schema_script) }}</textarea>
                    @foreach ([
                        'head_start_script' => 'Start of <head>',
                        'head_end_script' => 'End of <head>',
                        'body_start_script' => 'Start of <body>',
                        'body_end_script' => 'End of <body>',
                    ] as $field => $label)
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500">{{ $label }}</label>
                            <textarea name="{{ $field }}" rows="2"
                                      class="block w-full rounded-md border-gray-300 font-mono text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old($field, $settings->$field) }}</textarea>
                        </div>
                    @endforeach
                </div>
            </details>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $settings->exists ? $settings->is_active : true))
                       class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                <span class="text-sm text-gray-700">Published (draft renders a 404)</span>
            </label>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">
                    Save
                </button>
                <a href="{{ route('admin.pages-overview.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">Back to Pages</a>
            </div>
        </form>
    </div>
@endsection
