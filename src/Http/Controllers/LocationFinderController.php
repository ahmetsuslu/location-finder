<?php

namespace Sslah\LocationFinder\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Sslah\LocationFinder\Services\GeocodingService;

class LocationFinderController extends Controller
{
    protected GeocodingService $geocodingService;

    public function __construct(GeocodingService $geocodingService)
    {
        $this->geocodingService = $geocodingService;
    }

    /**
     * Search for locations by query string
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => 'required|string|min:3|max:255',
            ]);

            $query = $request->input('query');
            $results = $this->geocodingService->search($query);

            return response()->json([
                'success' => true,
                'query' => $query,
                'results' => $results,
                'count' => count($results),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Geocode address to coordinates
     */
    public function geocode(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'address' => 'required|string|max:255',
            ]);

            $address = $request->input('address');
            $result = $this->geocodingService->geocode($address);

            if ($result === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Address not found',
                    'address' => $address,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'address' => $address,
                'result' => $result,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Geocoding failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reverse geocode coordinates to address
     */
    public function reverseGeocode(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'lat' => 'required|numeric|between:-90,90',
                'lon' => 'required|numeric|between:-180,180',
            ]);

            $lat = (float) $request->input('lat');
            $lon = (float) $request->input('lon');
            
            $result = $this->geocodingService->reverseGeocode($lat, $lon);

            if ($result === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'No address found for coordinates',
                    'coordinates' => [
                        'lat' => $lat,
                        'lon' => $lon,
                    ],
                ], 404);
            }

            return response()->json([
                'success' => true,
                'coordinates' => [
                    'lat' => $lat,
                    'lon' => $lon,
                ],
                'result' => $result,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reverse geocoding failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
} 