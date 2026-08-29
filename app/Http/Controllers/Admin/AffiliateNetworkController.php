<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\AffiliateNetwork;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AffiliateNetworkController extends Controller
{
    use GuardsRegionOwnership;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $networks = AffiliateNetwork::where('region_id', $region->id)->orderBy('network_name')->get();

        return view('admin.affiliate-networks.index', compact('networks'));
    }

    public function create(): View
    {
        return view('admin.affiliate-networks.form', ['network' => new AffiliateNetwork()]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;

        AffiliateNetwork::create($data);

        return redirect()->route('admin.affiliate-networks.index')->with('status', 'Affiliate network created.');
    }

    public function edit(Request $request, AffiliateNetwork $affiliateNetwork): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $affiliateNetwork->region_id);

        return view('admin.affiliate-networks.form', ['network' => $affiliateNetwork]);
    }

    public function update(Request $request, AffiliateNetwork $affiliateNetwork): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $affiliateNetwork->region_id);

        $affiliateNetwork->update($this->validated($request));

        return redirect()->route('admin.affiliate-networks.index')->with('status', 'Affiliate network updated.');
    }

    public function destroy(Request $request, AffiliateNetwork $affiliateNetwork): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $affiliateNetwork->region_id);

        $affiliateNetwork->delete();

        return redirect()->route('admin.affiliate-networks.index')->with('status', 'Affiliate network deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'network_name' => ['required', 'string', 'max:255'],
            'tracking_id' => ['nullable', 'string', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:1000'],
            'api_secret' => ['nullable', 'string', 'max:1000'],
            'sync_status' => ['required', 'in:pending,synced,failed'],
        ]);

        // Blank means "leave unchanged" for secrets already on file.
        if ($data['api_key'] === null || $data['api_key'] === '') {
            unset($data['api_key']);
        }
        if ($data['api_secret'] === null || $data['api_secret'] === '') {
            unset($data['api_secret']);
        }

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
