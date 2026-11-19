<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformBankAccount;
use Illuminate\Http\Request;

class PlatformBankAccountController extends Controller
{
    public function index()
    {
        return view('admin.bank-accounts.index', [
            'accounts' => PlatformBankAccount::withCount('topupRequests')->orderBy('sort_order')->orderBy('bank_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        PlatformBankAccount::create($this->attributes($request));

        return redirect()->route('admin.bank-accounts.index')->with('success', __('ui.bank_account_saved'));
    }

    public function edit(PlatformBankAccount $bankAccount)
    {
        return view('admin.bank-accounts.edit', ['account' => $bankAccount]);
    }

    public function update(Request $request, PlatformBankAccount $bankAccount)
    {
        $bankAccount->update($this->attributes($request));

        return redirect()->route('admin.bank-accounts.index')->with('success', __('ui.bank_account_saved'));
    }

    public function destroy(PlatformBankAccount $bankAccount)
    {
        if ($bankAccount->topupRequests()->exists()) {
            return back()->with('error', __('ui.bank_account_in_use'));
        }

        $bankAccount->delete();

        return redirect()->route('admin.bank-accounts.index')->with('success', __('ui.bank_account_deleted'));
    }

    private function attributes(Request $request): array
    {
        $data = $request->validate([
            'bank_name'      => ['required', 'string', 'max:60'],
            'account_number' => ['required', 'string', 'regex:/^[0-9\s\-]{5,40}$/'],
            'account_name'   => ['required', 'string', 'max:100'],
            'note'           => ['nullable', 'string', 'max:300'],
            'sort_order'     => ['nullable', 'integer', 'between:0,9999'],
        ]);

        return $data + ['sort_order' => 0, 'is_active' => $request->boolean('is_active')];
    }
}