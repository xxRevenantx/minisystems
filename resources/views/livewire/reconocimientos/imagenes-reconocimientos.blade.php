<div class="space-y-6">
    <form wire:submit="guardarImagenReconocimiento"
        class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="lg">Subir diseño</flux:heading>
                <flux:text class="mt-1">Usa una imagen carta horizontal o vertical en buena resolución.</flux:text>
            </div>
            <flux:button href="{{ route('reconocimiento', ['tab' => 'plantillas']) }}" variant="filled">
                Configurar posiciones
            </flux:button>
        </div>

        <div class="grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end">
            <flux:input type="file" wire:model="reconocimiento" label="Archivo de imagen"
                badge="Obligatorio" accept="image/png,image/jpeg" />

            <flux:input wire:model="descripcion" label="Nombre o descripción" badge="Obligatorio"
                placeholder="Ej. Reconocimiento Primaria 2026" />

            <flux:button type="submit" variant="primary" wire:loading.attr="disabled"
                class="!bg-[#006492] hover:!bg-[#00557b] disabled:opacity-50">
                <span wire:loading.remove wire:target="guardarImagenReconocimiento">Guardar diseño</span>
                <span wire:loading wire:target="guardarImagenReconocimiento">Guardando…</span>
            </flux:button>
        </div>

        @if($reconocimiento)
            <div class="mt-5 max-w-sm overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <img src="{{ $reconocimiento->temporaryUrl() }}" class="h-40 w-full object-cover" alt="Vista previa">
                <div class="p-2 text-xs text-neutral-500">Vista previa del archivo seleccionado</div>
            </div>
        @endif
    </form>

    @if($isModalOpen)
        <section class="rounded-2xl border-2 border-[#88AC2E] bg-white p-5 shadow-sm dark:bg-neutral-900">
            <div class="mb-5 flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">Editar diseño</flux:heading>
                    <flux:text class="mt-1">Puedes cambiar el nombre o reemplazar la imagen.</flux:text>
                </div>
                <flux:button type="button" variant="filled" wire:click="closeModal">Cerrar</flux:button>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="descripcionEdit" label="Nombre" badge="Obligatorio" />
                <flux:input type="file" wire:model="nuevaImagen" label="Nueva imagen, opcional" accept="image/*" />
            </div>

            @if($nuevaImagen)
                <img src="{{ $nuevaImagen->temporaryUrl() }}" class="mt-4 h-40 max-w-sm rounded-xl object-cover"
                    alt="Nueva imagen">
            @endif

            <div class="mt-5 flex justify-end">
                <flux:button type="button" variant="primary" wire:click="actualizarImagenReconocimiento"
                    wire:loading.attr="disabled" class="!bg-[#88AC2E] hover:!bg-[#759726]">
                    <span wire:loading.remove wire:target="actualizarImagenReconocimiento">Guardar cambios</span>
                    <span wire:loading wire:target="actualizarImagenReconocimiento">Guardando…</span>
                </flux:button>
            </div>
        </section>
    @endif

    <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="mb-5">
            <flux:heading size="lg">Diseños disponibles</flux:heading>
            <flux:text class="mt-1">Los diseños utilizados no se eliminan: se desactivan para conservar el historial.</flux:text>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($imagenes as $imagen)
                <article class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-900">
                    <button type="button" wire:click="editarImagen({{ $imagen->id }}, @js($imagen->descripcion))" class="block w-full text-left">
                        <img src="{{ asset('storage/imagenesReconocimientos/'.$imagen->imagen) }}"
                            class="h-44 w-full object-cover" alt="{{ $imagen->nombre ?: $imagen->descripcion }}">
                    </button>

                    <div class="space-y-3 p-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="font-bold">{{ $imagen->nombre ?: ($imagen->descripcion ?: 'Diseño '.$imagen->id) }}</div>
                                <div class="text-xs text-neutral-500">{{ ucfirst($imagen->orientacion ?? 'horizontal') }}</div>
                            </div>
                            <flux:badge :color="$imagen->activo ? 'green' : 'zinc'">
                                {{ $imagen->activo ? 'Activo' : 'Inactivo' }}
                            </flux:badge>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <flux:button type="button" size="sm" variant="filled" wire:click="editarImagen({{ $imagen->id }}, @js($imagen->descripcion))">
                                Editar
                            </flux:button>

                            <flux:button type="button" size="sm"
                                variant="danger"
                                wire:click="eliminarImagenReconocimiento({{ $imagen->id }})"
                                wire:confirm="Si está en uso se desactivará. ¿Continuar?">
                                Eliminar
                            </flux:button>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-neutral-300 p-10 text-center dark:border-neutral-700">
                    <flux:heading size="md">Sin diseños registrados</flux:heading>
                    <flux:text class="mt-1">Sube el primer diseño para comenzar.</flux:text>
                </div>
            @endforelse
        </div>
    </section>
</div>
