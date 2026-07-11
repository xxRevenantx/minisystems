<?php

namespace App\Policies;

use App\Models\Reconocimiento;
use App\Models\User;

class ReconocimientoPolicy
{
    public function viewAny(User $user): bool { return $user->puedeReconocimientos('ver'); }
    public function view(User $user, Reconocimiento $reconocimiento): bool { return $user->puedeReconocimientos('ver'); }
    public function create(User $user): bool { return $user->puedeReconocimientos('crear'); }
    public function update(User $user, Reconocimiento $reconocimiento): bool { return $user->puedeReconocimientos('editar'); }
    public function delete(User $user, Reconocimiento $reconocimiento): bool { return $user->puedeReconocimientos('cancelar'); }
    public function restore(User $user, Reconocimiento $reconocimiento): bool { return $user->puedeReconocimientos('administrar'); }
    public function forceDelete(User $user, Reconocimiento $reconocimiento): bool { return $user->puedeReconocimientos('administrar'); }
    public function approve(User $user, Reconocimiento $reconocimiento): bool { return $user->puedeReconocimientos('aprobar'); }
    public function download(User $user, Reconocimiento $reconocimiento): bool { return $user->puedeReconocimientos('descargar'); }
}
