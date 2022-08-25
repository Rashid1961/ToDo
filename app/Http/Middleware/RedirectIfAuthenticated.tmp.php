<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            return redirect('/');
        }

        return $next($request);
    }

/*    //Перенаправление на страницу логина после выхода пользователя из системы
    public function logout()
    {        
        //$user= auth()->user();
        Auth::logout();
        //Auth::login($user, false);
        //Auth::login();
        return redirect('/');
    }    
*/
}
