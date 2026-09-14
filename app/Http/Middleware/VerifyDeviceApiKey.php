<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyDeviceApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $provided = $request->header('X-API-Key');
        $expected = config('services.device.api_key');

        if (! $expected) {
            abort(500, 'DEVICE_API_KEY belum diset di .env backend.');
        }

        // hash_equals mencegah timing attack dibanding perbandingan string biasa (===).
        if (! $provided || ! hash_equals($expected, $provided)) {
            abort(401, 'API key tidak valid atau tidak dikirim.');
        }

        return $next($request);
    }
}
