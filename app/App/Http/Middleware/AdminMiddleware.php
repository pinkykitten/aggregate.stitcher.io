<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \Domain\User\Models\User $user */
        $user = $request->user();

        if ($user && $user->isAdmin()) {
            return $next($request);
        }

        return abort(Response::HTTP_FORBIDDEN);
    }
}
