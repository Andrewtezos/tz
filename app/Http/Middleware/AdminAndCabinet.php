<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
class AdminAndCabinet
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      if(Auth::check() && Auth::user()->is_admin == 1) {
        return $next($request);
      } elseif(Auth::check()) {
        return redirect('profile');
      } else {
        abort(404);
      }
    }
}
