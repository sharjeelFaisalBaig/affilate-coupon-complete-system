{{--
    Reusable store/brand category cascade filter (up to 4 levels of depth,
    per SRS: selecting a category reveals a subcategory dropdown if one
    exists, a lone subcategory auto-selects, every level offers "All").
    Shared between the admin Offers listing and the public Promo Codes /
    Stores Listing pages.

    Expects:
    - $categories: flat Collection of ALL store categories for the region (id, parent_id, name)
    - $paramName: the query param name the resulting hidden input submits as
    - $selectedId: the currently selected id, if any (e.g. request($paramName))
--}}
<div data-category-cascade data-cascade-label="Subcategories" class="contents">
    <script type="application/json" data-cascade-data>{!! json_encode($categories->map(fn ($c) => ['id' => $c->id, 'parent_id' => $c->parent_id, 'name' => $c->name])->values()) !!}</script>
    <input type="hidden" name="{{ $paramName }}" value="{{ $selectedId }}" data-cascade-value>
    <div data-cascade-selects class="contents"></div>
</div>
