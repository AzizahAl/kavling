<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Batasi route berdasarkan peran: ->middleware('peran:admin') atau 'peran:admin,agen'.
 * Akun yang dinonaktifkan langsung dikeluarkan.
 */
class Peran
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();

        if ($user->aktif === false) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan. Hubungi administrator.');
        }

        if ($user->isAgen() && ! $user->agen_id) {
            abort(403, 'Akun agen ini belum dihubungkan ke data agen. Hubungi administrator.');
        }

        abort_unless(in_array($user->role, $peran, true), 403, 'Halaman ini hanya untuk administrator.');

        return $next($request);
    }
}
