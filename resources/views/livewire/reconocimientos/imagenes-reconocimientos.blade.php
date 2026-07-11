<div class="space-y-6">
    <form wire:submit="guardarImagenReconocimiento" class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div><h2 class="text-lg font-black">Subir diseño</h2><p class="text-sm text-neutral-500">Usa una imagen carta horizontal o vertical en buena resolución.</p></div>
            <a href="{{ route('reconocimiento',['tab'=>'plantillas']) }}" class="rounded-xl border border-[#006492] px-4 py-2 text-sm font-bold text-[#006492]">Configurar posiciones</a>
        </div>
        <div class="grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end">
            <label class="text-sm font-semibold">Archivo de imagen
                <input type="file" wire:model="reconocimiento" accept="image/png,image/jpeg" class="mt-1 block w-full rounded-xl border border-neutral-300 p-2 text-sm dark:border-neutral-700">
                @error('reconocimiento')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
            <label class="text-sm font-semibold">Nombre o descripción
                <input wire:model="descripcion" placeholder="Ej. Reconocimiento Primaria 2026" class="mt-1 w-full rounded-xl border-neutral-300 dark:border-neutral-700 dark:bg-neutral-800">
                @error('descripcion')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
            <button wire:loading.attr="disabled" class="rounded-xl bg-[#006492] px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50">
                <span wire:loading.remove wire:target="guardarImagenReconocimiento">Guardar diseño</span><span wire:loading wire:target="guardarImagenReconocimiento">Guardando…</span>
            </button>
        </div>
        @if($reconocimiento)<div class="mt-4 max-w-sm overflow-hidden rounded-xl border"><img src="{{ $reconocimiento->temporaryUrl() }}" class="h-40 w-full object-cover"><div class="p-2 text-xs text-neutral-500">Vista previa del archivo seleccionado</div></div>@endif
    </form>

    @if($isModalOpen)
        <section class="rounded-2xl border-2 border-[#88AC2E] bg-white p-5 shadow-sm dark:bg-neutral-900">
            <div class="mb-4 flex justify-between"><div><h2 class="text-lg font-black">Editar diseño</h2><p class="text-sm text-neutral-500">Puedes cambiar el nombre o reemplazar la imagen.</p></div><button type="button" wire:click="closeModal" class="rounded-lg border px-3 py-1 text-sm">Cerrar</button></div>
            <div class="grid gap-4 md:grid-cols-2"><label class="text-sm font-semibold">Nombre<input wire:model="descripcionEdit" class="mt-1 w-full rounded-xl border-neutral-300 dark:bg-neutral-800"></label><label class="text-sm font-semibold">Nueva imagen, opcional<input type="file" wire:model="nuevaImagen" accept="image/*" class="mt-1 block w-full rounded-xl border p-2"></label></div>
            @if($nuevaImagen)<img src="{{ $nuevaImagen->temporaryUrl() }}" class="mt-4 h-40 max-w-sm rounded-xl object-cover">@endif
            <div class="mt-4 flex justify-end"><button type="button" wire:click="actualizarImagenReconocimiento" class="rounded-xl bg-[#88AC2E] px-5 py-2.5 font-bold text-white">Guardar cambios</button></div>
        </section>
    @endif

    <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="mb-4"><h2 class="text-lg font-black">Diseños disponibles</h2><p class="text-sm text-neutral-500">Los diseños utilizados no se eliminan: se desactivan para conservar el historial.</p></div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($imagenes as $imagen)
                <article class="overflow-hidden rounded-2xl border border-neutral-200 dark:border-neutral-700">
                    <div class="relative"><img src="{{ asset('storage/imagenesReconocimientos/'.$imagen->imagen) }}" class="h-44 w-full object-cover"><span class="absolute right-2 top-2 rounded-full px-2 py-1 text-[10px] font-bold {{ $imagen->activo ? 'bg-green-100 text-green-800' : 'bg-neutral-800 text-white' }}">{{ $imagen->activo ? 'Activo' : 'Inactivo' }}</span></div>
                    <div class="p-3"><h3 class="truncate font-bold">{{ $imagen->nombre ?: ($imagen->descripcion ?: 'Plantilla '.$imagen->id) }}</h3><p class="mt-1 text-xs text-neutral-500">{{ ucfirst($imagen->orientacion ?? 'horizontal') }}</p><div class="mt-3 flex gap-2"><button wire:click="editarImagen({{ $imagen->id }}, @js($imagen->descripcion))" class="flex-1 rounded-lg bg-[#006492] px-3 py-2 text-xs font-bold text-white">Editar</button><button wire:click="eliminarImagenReconocimiento({{ $imagen->id }})" wire:confirm="Si está en uso se desactivará. ¿Continuar?" class="rounded-lg border border-red-300 px-3 py-2 text-xs font-bold text-red-600">Eliminar</button></div></div>
                </article>
            @empty<p class="col-span-full py-12 text-center text-neutral-500">No hay diseños registrados.</p>@endforelse
        </div>
    </section>
</div>
