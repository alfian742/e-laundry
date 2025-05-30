<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    // Daftar role yang valid
    protected $validRoles = ['owner', 'admin', 'employee', 'customer'];

    // Cek apakah user terautentikasi dan memiliki role yang valid
    protected function isAuthenticatedAndValidRole($user)
    {
        return $user && in_array($user->role, $this->validRoles);
    }

    // Cek apakah role user termasuk dalam role yang diperbolehkan
    protected function isAllowed(array $roles)
    {
        $user = Auth::user();

        // Cek apakah user valid
        if (!$this->isAuthenticatedAndValidRole($user)) {
            abort(403, 'Unauthorized');
        }

        // Cek apakah role user termasuk dalam role yang diperbolehkan
        if (!in_array($user->role, $roles)) {
            abort(403, 'Unauthorized');
        }
    }

    // Cek apakah role user termasuk dalam role yang tidak diperbolehkan
    protected function isNotAllowed(array $roles)
    {
        $user = Auth::user();

        // Cek apakah user valid
        if (!$this->isAuthenticatedAndValidRole($user)) {
            abort(403, 'Unauthorized');
        }

        // Cek apakah role user termasuk yang tidak diperbolehkan
        if (in_array($user->role, $roles)) {
            abort(403, 'Unauthorized');
        }
    }
}
