<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GeoLocationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {   
        $latitude = $request->header('X-Latitude');
        $longitude = $request->header('X-Longitude');

        // Check latitude and longitude values and grant API permissions accordingly
        if ($latitude && $longitude) {
            // Example logic: Check if the coordinates are within a specific range
            if ($latitude >= 20 && $latitude <= 50 && $longitude >= 60 && $longitude <= 80) {
                // Grant API permissions based on the user's location
                $request->attributes->add(['api_permissions' => ['use_api_1', 'use_api_2']]);
            } else {
                // If the user is outside the allowed range, deny access
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } else {
            // If latitude or longitude headers are missing, deny access
            return response()->json(['error' => 'Latitude and longitude headers are required'], 401);
        }
        
        return $next($request);
    }
}
