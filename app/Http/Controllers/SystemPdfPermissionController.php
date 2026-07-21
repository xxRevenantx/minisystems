<?php

namespace App\Http\Controllers;

use App\Models\PdfPermiso;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemPdfPermissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->puedePdf('administrar'), 403);

        $users = User::query()
            ->with('permisoPdf')
            ->orderBy('name')
            ->get()
            ->map(function (User $user): array {
                $permission = $user->permisoPdf;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_primary_admin' => (int) $user->id === 1,
                    'permissions' => [
                        'ver' => (bool) ($permission?->ver ?? false),
                        'procesar' => (bool) ($permission?->procesar ?? false),
                        'descargar' => (bool) ($permission?->descargar ?? false),
                        'eliminar' => (bool) ($permission?->eliminar ?? false),
                        'administrar' => (bool) ($permission?->administrar ?? false),
                    ],
                ];
            });

        return response()->json(['users' => $users]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()?->puedePdf('administrar'), 403);

        $data = $request->validate([
            'ver' => ['required', 'boolean'],
            'procesar' => ['required', 'boolean'],
            'descargar' => ['required', 'boolean'],
            'eliminar' => ['required', 'boolean'],
            'administrar' => ['required', 'boolean'],
        ]);

        if ((int) $user->id === 1) {
            $data = array_fill_keys(array_keys($data), true);
        }

        $permission = PdfPermiso::query()->updateOrCreate(
            ['user_id' => $user->id],
            $data,
        );

        return response()->json([
            'message' => 'Permisos de System PDF actualizados.',
            'permissions' => [
                'ver' => (bool) $permission->ver,
                'procesar' => (bool) $permission->procesar,
                'descargar' => (bool) $permission->descargar,
                'eliminar' => (bool) $permission->eliminar,
                'administrar' => (bool) $permission->administrar,
            ],
        ]);
    }
}
