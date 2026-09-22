<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  (Parameter role dinamis dari route)
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Cek apakah user sudah login
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // 2. Cek apakah role user saat ini ada dalam daftar $roles yang diperbolehkan
        if (in_array($request->user()->role, $roles)) {
            return $next($request);
        }

        // 3. Jika role tidak cocok, batalkan akses dengan error 403 Forbidden
        abort(403, 'Anda tidak memiliki hak akses untuk membuka halaman ini.');
    }
}
