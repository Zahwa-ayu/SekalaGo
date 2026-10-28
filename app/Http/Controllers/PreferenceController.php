<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PreferenceController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'locale'   => ['nullable', Rule::in(array_keys(config('sekalago.locales')))],
            'currency' => ['nullable', 'exists:currencies,code'],
        ]);

        $prefs = array_filter($data);

        if ($user = $request->user()) {
            $user->update(
                collect($prefs)->mapWithKeys(fn ($v, $k) => ["preferred_{$k}" => $v])->all()
            );
        } else {
            session($prefs);
        }

        return back();
    }
}