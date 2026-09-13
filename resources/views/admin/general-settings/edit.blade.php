@extends('admin.layouts.app')

@section('title', 'General Settings')

@push('head')
    @vite(['resources/js/blog-editor.js', 'resources/js/image-dimension-check.js'])
@endpush

@section('content')
    <div class="max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.general-settings.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Header / Footer Logo</label>
                @if ($settings->logo_path)
                    <img data-live-preview src="{{ Storage::url($settings->logo_path) }}" alt="" width="120" height="32" class="mb-2 h-8 w-auto object-contain">
                @endif
                <input type="file" name="logo" accept="image/*"
                       class="block text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
                <p class="mt-1 text-xs text-gray-400">* Optimal size: 160x40px, transparent PNG/SVG.</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Footer Textual Content</label>
                <div data-quill-editor="footer_text" style="min-height: 140px;" class="bg-white"></div>
                <textarea name="footer_text" data-content-field="footer_text" class="hidden">{{ old('footer_text', $settings->footer_text) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Shown in the footer's fourth column (site description / how-it-works copy).</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Footer Disclaimer</label>
                <textarea name="footer_disclaimer" rows="3"
                          class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('footer_disclaimer', $settings->footer_disclaimer) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Optimal length: ~400 characters to avoid wrapping past 3 lines.</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Store Page Disclaimer</label>
                <textarea name="store_page_disclaimer" rows="3"
                          class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('store_page_disclaimer', $settings->store_page_disclaimer) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Shown just under the heading on every store detail page.</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">All Rights Reserved Text</label>
                <input type="text" name="rights_text" value="{{ old('rights_text', $settings->rights_text) }}" maxlength="255"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <p class="mt-1 text-xs text-gray-400">e.g. "© 2015 – 2026 dealhub. All rights reserved."</p>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
@endsection
