<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Photo;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PhotoController extends Controller
{
    use PartnerHelpers;

    private const MAX_HOTEL = 10;
    private const MAX_ROOM  = 6;

    public function hotelIndex(Request $request, Hotel $hotel)
    {
        $this->ownHotel($request, $hotel);
        $hotel->load('translations', 'photos');

        return view('partner.photos.index', [
            'title'     => $hotel->name,
            'photos'    => $hotel->photos,
            'max'       => self::MAX_HOTEL,
            'storeUrl'  => route('partner.hotels.photos.store', $hotel),
            'backUrl'   => route('partner.hotels.index'),
            'backLabel' => __('ui.my_hotels'),
        ]);
    }

    public function hotelStore(Request $request, Hotel $hotel)
    {
        $this->ownHotel($request, $hotel);

        return $this->upload($request, $hotel, 'hotels', self::MAX_HOTEL, route('partner.hotels.photos.index', $hotel));
    }

    public function roomTypeIndex(Request $request, RoomType $roomType)
    {
        $this->ownRoomType($request, $roomType);
        $roomType->load('translations', 'photos', 'hotel.translations');

        return view('partner.photos.index', [
            'title'     => $roomType->hotel->name . ' / ' . $roomType->name,
            'photos'    => $roomType->photos,
            'max'       => self::MAX_ROOM,
            'storeUrl'  => route('partner.room-types.photos.store', $roomType),
            'backUrl'   => route('partner.hotels.room-types.index', $roomType->hotel),
            'backLabel' => __('ui.room_types'),
        ]);
    }

    public function roomTypeStore(Request $request, RoomType $roomType)
    {
        $this->ownRoomType($request, $roomType);

        return $this->upload($request, $roomType, 'room-types', self::MAX_ROOM, route('partner.room-types.photos.index', $roomType));
    }

    /** Jadikan satu foto sebagai sampul. */
    public function cover(Request $request, Photo $photo)
    {
        $this->ownPhoto($request, $photo);

        DB::transaction(function () use ($photo) {
            Photo::where('photoable_type', $photo->photoable_type)
                ->where('photoable_id', $photo->photoable_id)
                ->update(['is_cover' => false]);

            $photo->update(['is_cover' => true]);
        });

        return back()->with('success', __('ui.photo_cover_set'));
    }

    public function destroy(Request $request, Photo $photo)
    {
        $this->ownPhoto($request, $photo);

        $owner    = $photo->photoable;
        $wasCover = $photo->is_cover;

        $photo->delete();   // berkas ikut terhapus lewat event model

        // Jika sampul dihapus, foto berikutnya jadi sampul
        if ($wasCover) {
            $owner->photos()->first()?->update(['is_cover' => true]);
        }

        return back()->with('success', __('ui.photo_deleted'));
    }

    private function upload(Request $request, Model $owner, string $folder, int $max, string $redirect)
    {
        $request->validate([
            'photos'   => ['required', 'array', 'min:1'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $files    = $request->file('photos');
        $existing = $owner->photos()->count();

        if ($existing + count($files) > $max) {
            throw ValidationException::withMessages(['photos' => __('ui.photo_limit', ['max' => $max])]);
        }

        DB::transaction(function () use ($owner, $files, $folder, $existing) {
            foreach (array_values($files) as $i => $file) {
                $owner->photos()->create([
                    'path'       => $file->store("{$folder}/{$owner->id}", 'public'),
                    'is_cover'   => $existing === 0 && $i === 0,   // foto pertama jadi sampul
                    'sort_order' => $existing + $i,
                ]);
            }
        });

        return redirect($redirect)->with('success', __('ui.photo_uploaded'));
    }

    private function ownPhoto(Request $request, Photo $photo): void
    {
        $owner = $photo->photoable;

        if ($owner instanceof Hotel) {
            $this->ownHotel($request, $owner);

            return;
        }

        if ($owner instanceof RoomType) {
            $this->ownRoomType($request, $owner);

            return;
        }

        abort(404);
    }
}