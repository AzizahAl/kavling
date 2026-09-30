<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /** ID agen milik akun yang sedang login bila perannya agen; null untuk admin. */
    protected function agenLogin(): ?int
    {
        $user = auth()->user();

        return $user?->isAgen() ? $user->agen_id : null;
    }

    /** Akun agen hanya boleh mengakses data milik agennya sendiri. */
    protected function pastikanMilik(?int $agenId): void
    {
        $milik = $this->agenLogin();
        abort_if($milik !== null && $milik !== $agenId, 403, 'Data ini bukan milik Anda.');
    }
}
