<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\RoomStock;
use App\Models\RoomType;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class RoomStockController extends Controller
{
    use PartnerHelpers;

    private const MAX_DAYS = 365;

    public function edit(Request $request, RoomType $roomType)
    {
        $this->ownRoomType($request, $roomType);
        $roomType->load('translations', 'hotel.translations');

        return view('partner.stocks.edit', [
            'roomType' => $roomType,
            'ranges'   => $this->upcomingRanges($roomType),
        ]);
    }

    public function update(Request $request, RoomType $roomType)
    {
        $this->ownRoomType($request, $roomType);

        $data = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date'   => [
                'required', 'date', 'after_or_equal:start_date',
                'before_or_equal:' . today()->addDays(self::MAX_DAYS)->toDateString(),
            ],
            'available'  => ['required', 'integer', 'min:0', 'max:' . $roomType->total_rooms],
            'days'       => ['nullable', 'array'],
            'days.*'     => ['integer', 'between:0,6'],
        ]);

        // Hari dalam seminggu (0 = Minggu ... 6 = Sabtu). Kosong = semua hari.
        $days = collect($data['days'] ?? [])->map(fn ($d) => (int) $d);
        if ($days->isEmpty()) {
            $days = collect([0, 1, 2, 3, 4, 5, 6]);
        }

        $now  = now();
        $rows = [];

        foreach (CarbonPeriod::create($data['start_date'], $data['end_date']) as $date) {
            if (! $days->contains($date->dayOfWeek)) {
                continue;
            }

            $rows[] = [
                'room_type_id' => $roomType->id,
                'date'         => $date->toDateString(),
                'available'    => (int) $data['available'],
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            RoomStock::upsert($chunk, ['room_type_id', 'date'], ['available', 'updated_at']);
        }

        return redirect()->route('partner.room-types.stocks.edit', $roomType)
            ->with('success', __('ui.stock_saved', ['count' => count($rows)]));
    }

    /** Stok ke depan, tanggal berurutan dengan nilai sama digabung jadi satu rentang. */
    private function upcomingRanges(RoomType $roomType): array
    {
        $stocks = $roomType->stocks()
            ->whereBetween('date', [today()->toDateString(), today()->addDays(self::MAX_DAYS)->toDateString()])
            ->orderBy('date')
            ->get();

        $ranges = [];

        foreach ($stocks as $stock) {
            $available = (int) $stock->available;
            $last      = count($ranges) - 1;

            if ($last >= 0
                && $ranges[$last]['available'] === $available
                && $ranges[$last]['to']->copy()->addDay()->isSameDay($stock->date)) {
                $ranges[$last]['to'] = $stock->date;
            } else {
                $ranges[] = ['from' => $stock->date, 'to' => $stock->date, 'available' => $available];
            }
        }

        return $ranges;
    }
}