<?php

namespace App\Http\Controllers;

use App\Models\Agen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Kelola akun login (admin & agen). Hanya untuk administrator. */
class PenggunaController extends Controller
{
    public function index()
    {
        return view('pengguna.index', [
            'users' => User::with('agen')->orderBy('role')->orderBy('name')->get(),
            'semuaAgen' => Agen::orderBy('nama_agen')->pluck('nama_agen', 'id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $user = User::create($data);

        return back()->with('success', "Akun {$user->email} dibuat. Berikan kata sandi awal kepada pemiliknya dan minta segera diganti.");
    }

    public function update(Request $request, User $pengguna)
    {
        $data = $this->validasi($request, $pengguna);
        if ($pengguna->is($request->user()) && ($data['role'] !== 'admin' || ! $data['aktif'])) {
            return back()->with('error', 'Anda tidak bisa menurunkan peran atau menonaktifkan akun Anda sendiri.');
        }
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $pengguna->update($data);

        return back()->with('success', "Akun {$pengguna->email} diperbarui.");
    }

    public function destroy(Request $request, User $pengguna)
    {
        if ($pengguna->is($request->user())) {
            return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }
        if ($pengguna->isAdmin() && User::where('role', 'admin')->where('aktif', true)->count() <= 1) {
            return back()->with('error', 'Minimal harus ada satu administrator aktif.');
        }
        $pengguna->delete();

        return back()->with('success', 'Akun dihapus.');
    }

    private function validasi(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role'     => ['required', Rule::in(array_keys(User::PERAN))],
            'agen_id'  => ['nullable', 'required_if:role,agen', Rule::exists('agens', 'id'), Rule::unique('users', 'agen_id')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', Password::min(8)],
            'aktif'    => ['nullable', 'boolean'],
        ], [
            'agen_id.required_if' => 'Pilih data agen untuk akun berperan agen.',
            'agen_id.unique'      => 'Agen ini sudah punya akun.',
        ], ['name' => 'nama', 'role' => 'peran', 'agen_id' => 'agen', 'password' => 'kata sandi']);

        $data['aktif'] = $request->boolean('aktif');
        if ($data['role'] === 'admin') {
            $data['agen_id'] = null;
        }

        return $data;
    }
}
