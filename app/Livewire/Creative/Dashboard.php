<?php

namespace App\Livewire\Creative;

use App\Models\ArchivoMultimedia;
use App\Models\HistorialExportacion;
use App\Models\Marca;
use App\Models\Persona;
use App\Models\PlantillaCreativa;
use App\Models\ProyectoCreativo;
use App\Models\PublicacionSocial;
use App\Models\SolicitudCreativa;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $ready = Schema::hasTable('marcas');

        $stats = $ready ? [
            'marcas' => Marca::where('activo', true)->count(),
            'personas' => Persona::where('activo', true)->count(),
            'proyectos' => ProyectoCreativo::whereNotIn('estado', ['entregado','archivado'])->count(),
            'archivos' => ArchivoMultimedia::where('activo', true)->count(),
            'plantillas' => PlantillaCreativa::where('activo', true)->count(),
            'solicitudes' => SolicitudCreativa::whereNotIn('estado', ['entregada','cancelada'])->count(),
            'publicaciones' => PublicacionSocial::whereIn('estado', ['aprobada','programada'])->count(),
            'exportaciones' => HistorialExportacion::where('created_at', '>=', now()->startOfMonth())->sum('cantidad'),
        ] : [];

        $upcoming = $ready
            ? SolicitudCreativa::with(['marca','proyecto'])
                ->whereNotIn('estado', ['entregada','cancelada'])
                ->orderByRaw("FIELD(prioridad, 'urgente','alta','media','baja')")
                ->orderBy('fecha_entrega')
                ->limit(6)->get()
            : collect();

        $scheduled = $ready
            ? PublicacionSocial::with(['marca','archivo'])
                ->whereIn('estado', ['aprobada','programada'])
                ->orderBy('programada_at')
                ->limit(6)->get()
            : collect();

        $recentAssets = $ready
            ? ArchivoMultimedia::where('activo', true)->latest()->limit(8)->get()
            : collect();

        return view('livewire.creative.dashboard', compact('ready','stats','upcoming','scheduled','recentAssets'));
    }
}
