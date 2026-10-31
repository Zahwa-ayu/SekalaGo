<?php

namespace App\Http\Controllers\Corporate;

use App\Http\Controllers\Controller;
use App\Models\CompanyPosition;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PositionController extends Controller
{
    use CorporateHelpers;

    public function index(Request $request)
    {
        $company   = $this->company($request);
        $positions = $company->positions()->withCount('members')->orderBy('booking_limit')->get();

        return view('corporate.positions.index', compact('company', 'positions'));
    }

    public function store(Request $request)
    {
        $company = $this->company($request);

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:100',
                                 Rule::unique('company_positions', 'name')->where('company_id', $company->id)],
            'booking_limit'  => ['required', 'integer', 'min:0'],
            'monthly_budget' => ['nullable', 'integer', 'min:0'],
        ]);

        $company->positions()->create($data);

        return redirect()->route('corporate.positions.index')->with('success', __('ui.position_saved'));
    }

    public function edit(Request $request, CompanyPosition $position)
    {
        $this->ownPosition($request, $position);

        return view('corporate.positions.edit', compact('position'));
    }

    public function update(Request $request, CompanyPosition $position)
    {
        $this->ownPosition($request, $position);

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:100',
                                 Rule::unique('company_positions', 'name')
                                     ->where('company_id', $position->company_id)
                                     ->ignore($position->id)],
            'booking_limit'  => ['required', 'integer', 'min:0'],
            'monthly_budget' => ['nullable', 'integer', 'min:0'],
        ]);

        $position->update($data);

        return redirect()->route('corporate.positions.index')->with('success', __('ui.position_saved'));
    }

    public function destroy(Request $request, CompanyPosition $position)
    {
        $this->ownPosition($request, $position);

        // Karyawan di jabatan ini otomatis kembali memakai limit dan anggaran default perusahaan
        $position->delete();

        return redirect()->route('corporate.positions.index')->with('success', __('ui.position_deleted'));
    }

    /** Ubah limit dan anggaran bulanan default (berlaku untuk karyawan tanpa jabatan atau jabatan tanpa anggaran). */
    public function updatePolicy(Request $request)
    {
        $company = $this->company($request);

        $data = $request->validate([
            'travel_policy_limit'    => ['required', 'integer', 'min:0'],
            'default_monthly_budget' => ['nullable', 'integer', 'min:0'],
        ]);

        $company->update($data);

        return redirect()->route('corporate.positions.index')->with('success', __('ui.default_limit_saved'));
    }

    private function ownPosition(Request $request, CompanyPosition $position): void
    {
        abort_unless($position->company_id === $this->company($request)->id, 404);
    }
}