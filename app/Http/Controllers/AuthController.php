<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function formMasuk()
    {
        return view('auth.login');
    }

    public function masuk(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [], ['email' => 'email atau nama pengguna', 'password' => 'kata sandi']);
        $data['email'] = trim($data['email']);

        // Maksimal 5 percobaan gagal per menit per email + IP
        $kunci = Str::lower($data['email']) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan masuk. Coba lagi dalam ' . RateLimiter::availableIn($kunci) . ' detik.',
            ]);
        }

        if (! Auth::attempt($data + ['aktif' => true], $request->boolean('ingat'))) {
            RateLimiter::hit($kunci);
            throw ValidationException::withMessages(['email' => 'Email/nama pengguna atau kata sandi salah, atau akun dinonaktifkan.']);
        }

        RateLimiter::clear($kunci);
        $request->session()->regenerate();
        $request->user()->forceFill(['terakhir_masuk' => now()])->save();

        return redirect()->intended(route('beranda'));
    }

    public function keluar(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah keluar.');
    }

    /** Beranda sesuai peran: admin ke dashboard, agen ke halaman kinerjanya sendiri. */
    public function beranda(Request $request)
    {
        $user = $request->user();

        return $user->isAgen() && $user->agen_id
            ? redirect()->route('agen.show', $user->agen_id)
            : redirect()->route('dashboard');
    }

    public function profil(Request $request)
    {
        return view('auth.profil', ['user' => $request->user()->load('agen')]);
    }

    public function ubahProfil(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            // Akun agen boleh memakai nama pengguna (tanpa @) sebagai pengganti email
            'email'            => ['required', 'string', 'max:255', str_contains((string) $request->input('email'), '@') ? 'email' : 'regex:/^[A-Za-z0-9._-]{3,}$/', 'unique:users,email,' . $user->id],
            'password_lama'    => ['nullable', 'required_with:password', 'current_password'],
            'password'         => ['nullable', 'confirmed', Password::min(8)],
        ], [
            'password_lama.current_password' => 'Kata sandi lama salah.',
            'password_lama.required_with'    => 'Masukkan kata sandi lama untuk mengganti kata sandi.',
            'email.regex'                    => 'Nama pengguna minimal 3 karakter (huruf, angka, titik, garis bawah, tanda hubung).',
        ], ['name' => 'nama', 'email' => 'email atau nama pengguna', 'password_lama' => 'kata sandi lama', 'password' => 'kata sandi baru']);

        $user->fill(['name' => $data['name'], 'email' => $data['email']]);
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
