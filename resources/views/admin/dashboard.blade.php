@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @php
            $cards = [
                ['label' => 'Total Stores', 'value' => $metrics['total_stores']],
                ['label' => 'Total Coupons', 'value' => $metrics['total_coupons']],
                ['label' => 'Total Categories', 'value' => $metrics['total_categories']],
                ['label' => 'Total Blogs', 'value' => $metrics['total_blogs']],
                ['label' => 'Total Admin Users', 'value' => $metrics['total_admin_users']],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">{{ $card['label'] }}</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($card['value']) }}</p>
            </div>
        @endforeach
    </div>

    <p class="mt-6 text-sm text-gray-500">
        Showing metrics for <strong>{{ $activeRegion->name }}</strong>. Use the region switcher in the top bar to view another region's data.
    </p>
@endsection
