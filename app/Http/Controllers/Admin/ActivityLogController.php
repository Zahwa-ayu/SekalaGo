<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Partner;
use App\Models\PartnerAgreement;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ActivityLogController extends Controller
{
    private const TYPES = [
        'hotel'             => Hotel::class,
        'room_type'         => RoomType::class,
        'partner'           => Partner::class,
        'partner_agreement' => PartnerAgreement::class,
        'booking'           => Booking::class,
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'type' => ['nullable', Rule::in(array_keys(self::TYPES))],
            'user' => ['nullable', 'string', 'max:100'],
            'q'    => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $like = fn (string $value) => '%' . addcslashes($value, '%_\\') . '%';

        $logs = ActivityLog::query()
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('subject_type', self::TYPES[$type]))
            ->when($filters['user'] ?? null, fn ($q, $user) => $q->where('causer_label', 'like', $like($user)))
            ->when($filters['q'] ?? null, fn ($q, $text) => $q->where('subject_label', 'like', $like($text)))
            ->when($filters['from'] ?? null, fn ($q, $d) => $q->where('created_at', '>=', Carbon::parse($d)->startOfDay()))
            ->when($filters['to'] ?? null, fn ($q, $d) => $q->where('created_at', '<=', Carbon::parse($d)->endOfDay()))
            ->latest('created_at')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.activity.index', [
            'logs'    => $logs,
            'types'   => array_keys(self::TYPES),
            'filters' => $filters,
        ]);
    }
}