<?php

namespace App\Http\Middleware;

use Closure;

class DisablePostSizeValidation
{
    public function handle($request, Closure $next)
    {
        // This middleware does nothing - just passes through
        // It replaces Laravel's ValidatePostSize middleware
        return $next($request);
    }
}
