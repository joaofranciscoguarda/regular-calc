<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $sessionId = $request->cookie('calc_session')
            ?? $request->header('X-Calc-Session');

        $isNew = false;

        if (!$sessionId) {
            $sessionId = Str::uuid()->toString();
            $isNew = true;
        }

        $request->attributes->set('calc_session_id', $sessionId);

        /** @var Response $response */
        $response = $next($request);

        if ($isNew) {
            $response->headers->setCookie(
                cookie(
                    name: 'calc_session',
                    value: $sessionId,
                    minutes: 60 * 24 * 30, // valid for a month
                    path: '/',
                    secure: $request->isSecure(),
                    httpOnly: true,
                    sameSite: 'Lax',
                )
            );
        }

        $response->headers->set('X-Calc-Session', $sessionId);

        return $response;
    }
}
