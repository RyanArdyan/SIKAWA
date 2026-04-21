<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah sudah login DAN apakah rolenya adalah admin
        if (auth()->check() && auth()->user()->role == 'admin') {
            return $next($request);
        }

        // Jika bukan admin, tendang kembali ke halaman depan dengan pesan error
        return redirect('/')->with('error', 'Akses ditolak! Anda bukan Admin.');
    }
}
