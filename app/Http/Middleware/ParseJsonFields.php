<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ParseJsonFields
{
    /**
     * Auto-decode JSON-encoded string fields in multipart/form-data requests.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            foreach ($request->request->all() as $key => $value) {
                if (is_string($value)) {
                    $trimmed = ltrim($value);

                    if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
                        $decoded = json_decode($value, true);

                        if (json_last_error() === JSON_ERROR_NONE) {
                            $request->request->set($key, $decoded);
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}
