<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    public function index(): View
    {
        $currencies = Currency::withCount('regions')->orderBy('name')->get();

        return view('admin.currencies.index', compact('currencies'));
    }

    public function create(): View
    {
        return view('admin.currencies.form', ['currency' => new Currency()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Currency::create($this->validated($request));

        return redirect()->route('admin.currencies.index')->with('status', 'Currency created.');
    }

    public function edit(Currency $currency): View
    {
        return view('admin.currencies.form', compact('currency'));
    }

    public function update(Request $request, Currency $currency): RedirectResponse
    {
        $currency->update($this->validated($request, $currency));

        return redirect()->route('admin.currencies.index')->with('status', 'Currency updated.');
    }

    public function destroy(Currency $currency): RedirectResponse
    {
        if ($currency->regions()->exists()) {
            return back()->with('error', 'This currency is assigned to one or more regions and cannot be deleted.');
        }

        $currency->delete();

        return redirect()->route('admin.currencies.index')->with('status', 'Currency deleted.');
    }

    private function validated(Request $request, ?Currency $currency = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:10'],
            'iso_code' => [
                'required', 'string', 'size:3', 'uppercase',
                Rule::unique('currencies', 'iso_code')->ignore($currency),
            ],
        ]);
    }
}
