<?php

namespace App\Policies;

use App\Models\ReconocimientoImagen;
use App\Models\User;

class ReconocimientoImagenPolicy
{
    public function viewAny(User $user): bool { return $user->puedeReconocimientos('ver'); }
    public function view(User $user, ReconocimientoImagen $reconocimientoImagen): bool { return $user->puedeReconocimientos('ver'); }
    public function create(User $user): bool { return $user->puedeReconocimientos('administrar'); }
    public function update(User $user, ReconocimientoImagen $reconocimientoImagen): bool { return $user->puedeReconocimientos('administrar'); }
    public function delete(User $user, ReconocimientoImagen $reconocimientoImagen): bool { return $user->puedeReconocimientos('administrar'); }
    public function restore(User $user, ReconocimientoImagen $reconocimientoImagen): bool { return $user->puedeReconocimientos('administrar'); }
    public function forceDelete(User $user, ReconocimientoImagen $reconocimientoImagen): bool { return $user->puedeReconocimientos('administrar'); }
}
