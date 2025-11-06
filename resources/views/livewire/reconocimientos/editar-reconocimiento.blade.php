<div
  x-data="{ show: false, loading: false }"
  x-cloak
  x-trap.noscroll="show"
  x-show="show"
  @abrir-modal-editar.window="show = true; loading = true"
  @editar-cargado.window="loading = false"
  @cerrar-modal-editar.window="
      show = false;
      loading = false;
      $wire.cerrarModal()
  "
  @keydown.escape.window="show = false; $wire.cerrarModal()"
  class="fixed inset-0 z-50 flex items-center justify-center"
  aria-live="polite"
>
  <!-- Overlay -->
  <div class="absolute inset-0 bg-neutral-900/70 backdrop-blur-sm"
       x-show="show" x-transition.opacity
       @click.self="show = false; $wire.cerrarModal()"></div>


    <div
        class="relative w-[92vw] sm:w-[88vw] md:w-[70vw] max-w-2xl mx-4 sm:mx-6 bg-white dark:bg-neutral-900 rounded-2xl shadow-2xl ring-1 ring-black/5 dark:ring-white/10 overflow-hidden"
        role="dialog" aria-modal="true" aria-labelledby="titulo-modal-cuatrimestre"
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        wire:ignore.self
    >
    <!-- Acento -->
     <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500"></div>

    <!-- Header -->
    <div class="flex items-start justify-between gap-4 px-5 py-4 sm:px-6 sm:py-5">
      <h2 class="text-lg sm:text-xl font-semibold text-zinc-900 dark:text-zinc-100">
        Editar Reconocimiento
        {{-- <flux:badge color="indigo" class="align-middle">{{ $nombre }}</flux:badge> --}}
      </h2>

      <button
        @click="show = false; $wire.cerrarModal()"
        type="button"
        class="inline-flex h-9 w-9 items-center justify-center rounded-full text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-neutral-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
        aria-label="Cerrar"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Contenido -->
    <div class="px-5 sm:px-6 pb-4 sm:pb-6 max-h-[75vh] overflow-y-auto">

        <form
        x-on:submit="loading = true"
        wire:submit.prevent="actualizarReconocimiento"
        class="px-5 sm:px-6 pb-5"
        >
        <flux:field>
          <div class="grid grid-cols-1 md:grid-cols-1 gap-5">

            <!-- Col: Campos -->
            <div class="space-y-4">
               <flux:input
            label="Reconocimiento a:"
            placeholder="Nombre de la persona/institución"
            badge="Requerido"
            wire:model="reconocimiento_a"
          />
          <flux:input
            label="Por haber obtenido el lugar"
            placeholder="Ej. 1er lugar en..."
            badge="Opcional"
            wire:model.defer="lugar_obtenido"
          />
          <flux:input
            type="date"
            label="Fecha"
            badge="Requerido"
            wire:model="fecha"
          />
            </div>
          </div>
            </flux:field>

              <div >
          <label for="descripcionReconocimiento" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1 mt-2">
            Descripción del Reconocimiento
          </label>
          <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">
            Este texto aparecerá en la ficha del reconocimiento. Puedes usar el editor enriquecido.
          </p>



          <!-- Con TinyMCE -->
          <div wire:ignore>
            <textarea
              id="descripcionReconocimientoEditar"
              rows="6"
              class="block w-full min-h-[140px] rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 p-3 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y shadow-sm transition"
              placeholder="Escribe la descripción del reconocimiento...">
            </textarea>
          </div>
          @error('descripcion')
            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
          @enderror
          <p class="mt-2 text-xs text-neutral-400">Máx. recomendado 500 caracteres.</p>
        </div>



          <!-- Footer acciones -->
          <div class="pt-4 border-t border-zinc-200 dark:border-neutral-800 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <flux:button
              @click="show = false; $wire.cerrarModal()"
              type="button"
              class="cursor-pointer"
            >
              {{ __('Cancelar') }}
            </flux:button>

                            <flux:button
                                variant="primary"
                                type="submit"
                                class="w-full sm:w-auto cursor-pointer guardar-btn"
                                wire:loading.attr="disabled"
                                wire:target="actualizarLicenciatura"

                            >
                                {{ __('Actualizar') }}
                            </flux:button>
          </div>


        <!-- Loader interno -->
             <div x-show="loading"
                    class="absolute inset-0 z-20 flex items-center justify-center bg-white/70 dark:bg-neutral-900/70 backdrop-blur rounded-2xl">
                <div class="flex items-center gap-3 rounded-xl bg-white dark:bg-neutral-900 px-4 py-3 ring-1 ring-neutral-200 dark:ring-neutral-800 shadow">
                    <svg class="h-5 w-5 animate-spin text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span class="text-sm text-neutral-800 dark:text-neutral-200">Cargando…</span>
                </div>
                </div>


      </form>
    </div>
  </div>
</div>


<script>
(function () {
  const TINY_SRC = "https://cdn.tiny.cloud/1/1ebweotq439cl3bk11wscr1wf0h3iemo2t74u6ve9sjcy7cl/tinymce/8/tinymce.min.js";
  const ID = "descripcionReconocimientoEditar";
  const SELECTOR = "#"+ID;

  function loadTiny(cb){
    if (window.tinymce) return cb();
    const s = document.createElement("script");
    s.src = TINY_SRC; s.referrerPolicy="origin"; s.crossOrigin="anonymous";
    s.onload = cb; s.onerror = () => console.error("[TinyMCE] Falló la carga");
    document.head.appendChild(s);
  }

  function destroyTiny(){
    const inst = window.tinymce?.get(ID);
    if (inst) inst.remove();
  }

  function initTiny(){
    const el = document.querySelector(SELECTOR);
    if (!el) return;

    destroyTiny(); // por si acaso

    window.tinymce.init({
      selector: SELECTOR,
      plugins: "table lists",
      toolbar: "undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | table",
      setup: (editor) => {
        editor.on("init", () => {
          // Cargar valor actual desde Livewire
          const val = @json($this->descripcion ?? '');
          if (val) editor.setContent(val);
          // Recalc por si el modal hizo transición
          setTimeout(() => { try { editor.execCommand('mceRepaint'); } catch(_){} }, 50);
        });
        // Sincronizar con Livewire
        editor.on("change keyup blur", () => {
          @this.set('descripcion', editor.getContent());
        });
      },
    });
  }

  // IMPORTANTE: montar TinyMCE solo cuando el modal YA es visible
  // Tu modal usa estos eventos:
  // @abrir-modal-editar.window  -> show = true; loading = true
  // @editar-cargado.window      -> loading = false  (ya cargó data)
  // @cerrar-modal-editar.window -> cerrar modal

  window.addEventListener('editar-cargado', () => {
    // Espera un tick para que x-show abra y termine la transición
    requestAnimationFrame(() => {
      setTimeout(() => loadTiny(initTiny), 80);
    });
  });

  window.addEventListener('cerrar-modal-editar', () => {
    destroyTiny();
  });

  // Si navegas con Livewire v3 entre páginas
  document.addEventListener("livewire:navigated", () => destroyTiny());
})();
</script>

