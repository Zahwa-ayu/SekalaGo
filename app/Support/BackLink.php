<?php

namespace App\Support;

use Illuminate\Http\Request;

class BackLink
{
    /** Halaman asal yang dianggap daftar: Beranda, Hotel, dan Rental Mobil. */
    private const LISTINGS = ['/', '/hotels', '/cars'];

    /**
     * Alamat kembali dari halaman detail: halaman daftar tempat pengunjung datang (beserta filter dan nomor halamannya),
     * atau halaman daftar yang sesuai jika ia datang dari tautan langsung.
     */
    public static function toListing(Request $request, string $fallbackRoute, array $params = []): string
    {
        $referer = (string) $request->headers->get('referer');
        $parts   = $referer !== '' ? (parse_url($referer) ?: []) : [];

        if (($parts['host'] ?? null) === $request->getHost()) {
            $path = rtrim($parts['path'] ?? '/', '/') ?: '/';

            if (in_array($path, self::LISTINGS, true)) {
                return $referer . (str_contains($parts['query'] ?? '', 'city=') ? '#results' : '');
            }
        }

        $params = array_filter($params, fn ($value) => filled($value));

        return route($fallbackRoute, $params) . ($params ? '#results' : '');
    }
}