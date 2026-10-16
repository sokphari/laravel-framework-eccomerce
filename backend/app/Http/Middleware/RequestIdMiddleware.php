<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestIdMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Server-generated ID to avoid trusting arbitrary client-controlled values.
        $id = (string) Str::uuid();
        $request->attributes->set('request_id', $id);
        Log::withContext(['request_id' => $id]);

        $start = microtime(true);
        try {
            $response = $next($request);
            $response->headers->set('X-Request-ID', $id);
            return $response;
        } finally {
            Log::info('http.request', [
                'method' => $request->method(),
                'path' => $request->path(),
                'duration_ms' => round((microtime(true) - $start) * 1000, 2),
                'user_id' => $request->user()?->getAuthIdentifier(),
            ]);
        }
    }
}
