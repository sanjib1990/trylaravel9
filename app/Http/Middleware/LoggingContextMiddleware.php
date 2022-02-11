<?php

namespace App\Http\Middleware;

use Closure;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class LoggingContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $requestId = Uuid::uuid4()->toString();

        Log::withContext([
            'request-id'    => $requestId,
            'subdomain'     => Session::get("subdomain", "None")
        ]);

        return $next($request)->header('Request-Id', $requestId);
    }
}
