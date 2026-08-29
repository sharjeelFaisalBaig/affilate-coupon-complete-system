{{-- Expects: $region, $placement ('head'|'body_end'), $pageType, optional $storeId --}}
@php
    $injections = \App\Models\ScriptInjection::where('region_id', $region->id)
        ->where('placement', $placement)
        ->forPage($pageType, $storeId ?? null)
        ->get();
@endphp
@foreach ($injections as $injection)
    {!! $injection->script_content !!}
@endforeach
