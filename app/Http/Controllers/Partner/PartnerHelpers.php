<?php

namespace App\Http\Controllers\Partner;

use App\Models\Hotel;
use App\Models\Partner;
use App\Models\RoomType;
use Illuminate\Http\Request;
use App\Models\Vehicle;

trait PartnerHelpers
{
    protected function partner(Request $request): Partner
    {
        $partner = $request->user()->partner;
        abort_unless($partner, 403);

        return $partner;
    }

    protected function ownHotel(Request $request, Hotel $hotel): void
    {
        abort_unless($hotel->partner_id === $this->partner($request)->id, 403);
    }

    protected function ownRoomType(Request $request, RoomType $roomType): void
    {
        $this->ownHotel($request, $roomType->hotel);
    }

    /** Teks sumber dari data form yang sudah divalidasi. */
    protected function sourceText(array $data): array
    {
        return [
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ];
    }

    protected function ownVehicle(Request $request, Vehicle $vehicle): void
    {
        abort_unless($vehicle->partner_id === $this->partner($request)->id, 403);
    }
}