<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\CurrencyRateLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CurrencyController extends Controller
{
    /** Perubahan kurs di atas 25% dianggap berisiko salah ketik dan butuh konfirmasi. */
    private const LARGE_CHANGE = 0.25;

    public function index()
    {
        $currencies = Currency::query()
            ->withCount('bookings')
            ->orderByRaw('code = ? DESC', [config('sekalago.default_currency')])
            ->orderBy('code')
            ->get();

        return view('admin.currencies.index', compact('currencies'));
    }

    public function store(Request $request)
    {
        $request->merge(['code' => strtoupper((string) $request->input('code'))]);

        $data = $request->validate([
            'code'           => ['required', 'string', 'size:3', 'alpha', Rule::unique('currencies', 'code')],
            'name'           => ['required', 'string', 'max:60'],
            'symbol'         => ['required', 'string', 'max:5'],
            'decimal_places' => ['required', 'integer', 'between:0,4'],
            'rate_to_base'   => ['required', 'numeric', 'gt:0', 'max:1000000000'],
        ]);

        DB::transaction(function () use ($data, $request) {
            $currency = Currency::create($data + ['is_active' => true]);

            CurrencyRateLog::create([
                'currency_code' => $currency->code,
                'old_rate'      => null,
                'new_rate'      => $currency->rate_to_base,
                'changed_by'    => $request->user()->id,
            ]);
        });

        return redirect()->route('admin.currencies.index')->with('success', __('ui.currency_saved'));
    }

    public function edit(Currency $currency)
    {
        return view('admin.currencies.edit', [
            'currency' => $currency,
            'logs'     => $currency->rateLogs()->with('user')->limit(20)->get(),
        ]);
    }

    /** Nama, simbol, jumlah desimal, dan status. Kurs diubah lewat updateRate(). */
    public function update(Request $request, Currency $currency)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:60'],
            'symbol'         => ['required', 'string', 'max:5'],
            'decimal_places' => ['required', 'integer', 'between:0,4'],
        ]);

        $currency->update($data + [
            // Rupiah selalu aktif
            'is_active' => $currency->isBase() ? true : $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.currencies.index')->with('success', __('ui.currency_saved'));
    }

    public function updateRate(Request $request, Currency $currency)
    {
        if ($currency->isBase()) {
            return back()->with('error', __('ui.currency_base_locked'));
        }

        $bag = "rate_{$currency->code}";

        $data = $request->validateWithBag($bag, [
            'rate_to_base' => ['required', 'numeric', 'gt:0', 'max:1000000000'],
        ]);

        $old = (float) $currency->rate_to_base;
        $new = round((float) $data['rate_to_base'], 6);

        if (abs($new - $old) < 0.000001) {
            return back()->with('success', __('ui.currency_rate_unchanged'));
        }

        $change = abs($new - $old) / $old;

        if ($change > self::LARGE_CHANGE && ! $request->boolean('confirm_large')) {
            throw ValidationException::withMessages([
                'rate_to_base' => __('ui.currency_rate_large_change', ['percent' => round($change * 100)]),
            ])->errorBag($bag);
        }

        DB::transaction(function () use ($currency, $old, $new, $request) {
            $currency->update(['rate_to_base' => $new]);

            CurrencyRateLog::create([
                'currency_code' => $currency->code,
                'old_rate'      => $old,
                'new_rate'      => $new,
                'changed_by'    => $request->user()->id,
            ]);
        });

        return back()->with('success', __('ui.currency_rate_saved', ['code' => $currency->code]));
    }

    public function destroy(Currency $currency)
    {
        if ($currency->isBase()) {
            return back()->with('error', __('ui.currency_base_locked'));
        }

        if ($currency->bookings()->exists()) {
            return back()->with('error', __('ui.currency_in_use'));
        }

        DB::transaction(function () use ($currency) {
            CurrencyRateLog::where('currency_code', $currency->code)->delete();
            $currency->delete();
        });

        return redirect()->route('admin.currencies.index')->with('success', __('ui.currency_deleted'));
    }
}