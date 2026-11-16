<?php

namespace App\Support;

use App\Models\User;

class BookingDetails
{
    /** Aturan isian: $type adalah 'hotel' atau 'car'. */
    public static function rules(string $type, bool $corporate, bool $selfDrive = false): array
    {
        $rules = [
            'guest_name'       => ['required', 'string', 'max:255'],
            'contact_name'     => ['required', 'string', 'max:255'],
            'contact_email'    => ['required', 'email', 'max:255'],
            'contact_phone'    => ['required', 'string', 'regex:/^\+?[0-9\s\-]{8,20}$/'],
            'arrival_time'     => [$type === 'car' ? 'required' : 'nullable', 'date_format:H:i'],
            'special_requests' => ['nullable', 'string', 'max:500'],
        ];

        if ($type === 'car') {
            $rules['destination'] = ['nullable', 'string', 'max:255'];

            if ($selfDrive) {
                $rules['license_confirmed'] = ['accepted'];
            }
        }

        if ($corporate) {
            $rules['trip_purpose'] = ['required', 'string', 'max:100'];
            $rules['cost_center']  = ['nullable', 'string', 'max:60'];
        }

        return $rules;
    }

    /** Kolom bookings dari isian yang sudah divalidasi (nama tamu ditangani terpisah). */
    public static function attributes(array $data): array
    {
        return [
            'contact_name'      => $data['contact_name'] ?? null,
            'contact_email'     => $data['contact_email'] ?? null,
            'contact_phone'     => $data['contact_phone'] ?? null,
            'arrival_time'      => $data['arrival_time'] ?? null,
            'special_requests'  => $data['special_requests'] ?? null,
            'destination'       => $data['destination'] ?? null,
            'license_confirmed' => ! empty($data['license_confirmed']),
            'trip_purpose'      => $data['trip_purpose'] ?? null,
            'cost_center'       => $data['cost_center'] ?? null,
        ];
    }

    /** Akun yang belum punya nomor HP mendapat nomor yang baru diisi. */
    public static function rememberContact(User $user, array $data): void
    {
        if (blank($user->phone_number) && filled($data['contact_phone'] ?? null)) {
            $user->update(['phone_number' => $data['contact_phone']]);
        }
    }
}