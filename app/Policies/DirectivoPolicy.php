<?php

namespace App\Policies;

use App\Models\Directivo;
use App\Models\User;

class DirectivoPolicy
{
    public function viewAny(User $user): bool { return $user->puedeReconocimientos('ver'); }
    public function view(User $user, Directivo $directivo): bool { return $user->puedeReconocimientos('ver'); }
    public function create(User $user): bool { return $user->puedeReconocimientos('administrar'); }
    public function update(User $user, Directivo $directivo): bool { return $user->puedeReconocimientos('administrar'); }
    public function delete(User $user, Directivo $directivo): bool { return $user->puedeReconocimientos('administrar'); }
    public function restore(User $user, Directivo $directivo): bool { return $user->puedeReconocimientos('administrar'); }
    public function forceDelete(User $user, Directivo $directivo): bool { return $user->puedeReconocimientos('administrar'); }
}
