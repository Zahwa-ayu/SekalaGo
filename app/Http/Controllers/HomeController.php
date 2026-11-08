<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Vehicle;
use App\Services\Search\CarSearchService;
use App\Services\Search\HotelSearchService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request, HotelSearchService $hotels, CarSearchService $cars)
    {
        $tab = $request->query('tab');

        $data = [
            'cities'    => Hotel::where('status', 'approved')->whereHas('roomTypes')->distinct()->orderBy('city')->pluck('city'),
            'carCities' => Vehicle::where('status', 'approved')->distinct()->orderBy('city')->pluck('city'),
            'params'    => [],
            'carParams' => [],
            // Tab mobil aktif jika diminta lewat alamat, atau jika form mobil baru saja gagal validasi
            'activeTab' => ($tab === 'cars' || $request->old('pickup_date')) ? 'cars' : 'hotels',
        ];

        if ($tab === 'hotels' && $request->filled('city')) {
            $search            = $hotels->search($request);
            $data['hotelSearch'] = $search;
            $data['params']      = $search['params'];
        }

        if ($tab === 'cars' && $request->filled('city')) {
            $search             = $cars->search($request);
            $data['carSearch']  = $search;
            $data['carParams']  = $search['params'];
            $data['activeTab']  = 'cars';
        }

        return view('home', $data);
    }
}