<?php

namespace Sslah\LocationFinder\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Sslah\LocationFinder\Services\GeocodingService;

class LocationFinderController extends Controller
{
    protected GeocodingService $geocodingService;

    public function __construct(GeocodingService $geocodingService)
    {
        $this->geocodingService = $geocodingService;
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:3|max:255',
        ]);

        $query = $request->input('query');
        $results = $this->geocodingService->search($query);

        return response()->json([
            'success' => true,
            'results' => $results,
            'count' => count($results),
        ]);
    }

    public function geocode(Request $request): JsonResponse
    {
        $request->validate([
            'address' => 'required|string|max:255',
        ]);

        $address = $request->input('address');
        $result = $this->geocodingService->geocode($address);

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'Adres bulunamadı',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'result' => $result,
        ]);
    }

    public function reverseGeocode(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $lat = (float) $request->input('lat');
        $lng = (float) $request->input('lng');
        
        $result = $this->geocodingService->reverseGeocode($lat, $lng);

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'Koordinatlar için adres bulunamadı',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'result' => $result,
        ]);
    }
} 