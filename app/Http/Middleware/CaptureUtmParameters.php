<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureUtmParameters
{
    /**
     * Inspect incoming query parameters for marketing attribution.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid'];
        $cookiesToSet = [];

        foreach ($utmKeys as $key) {
            if ($request->has($key)) {
                $val = substr(strip_tags((string) $request->query($key)), 0, 100);
                $request->session()->put($key, $val);
                $cookiesToSet[$key] = $val;
            }
        }

        $response = $next($request);

        foreach ($cookiesToSet as $k => $v) {
            $response->cookie($k, $v, 60 * 24 * 30); // 30 days
        }

        return $response;
    }
}
