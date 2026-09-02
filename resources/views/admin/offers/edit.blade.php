@extends('admin.layouts.app')

@section('title', 'Edit Offer')

@section('content')
    <div class="max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.offers.update', $offer) }}" data-offer-form class="space-y-5">
            @csrf
            @method('PUT')

            @include('admin.offers._fields', ['offer' => $offer])

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">
                    Save Changes
                </button>
                <a href="{{ route('admin.offers.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
