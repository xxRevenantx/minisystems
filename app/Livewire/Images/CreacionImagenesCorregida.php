<?php

namespace App\Livewire\Images;

/**
 * Variante del componente de System Images que evita inspeccionar todas las
 * fotografías dentro de la misma petición de carga temporal de Livewire.
 *
 * La clase padre sigue realizando la validación, lectura de dimensiones y EXIF
 * cuando el usuario pulsa "Continuar a configuración". De esta manera la barra
 * de subida deja de permanecer bloqueada visualmente en 100 %.
 */
class CreacionImagenesCorregida extends CreacionImagenes
{
    public function updatedImages(): void
    {
        $this->imageSettings = [];
        $this->selectedPreviewKey = null;

        $this->resetValidation('images');
        $this->resetValidation('images.*');
    }
}
