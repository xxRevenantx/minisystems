<div>
  <form wire:submit.prevent="guardarReconocimiento">
    <div
      x-data="{
        open:false, src:'', title:'',
        // flags
        collapseA:false,
        collapseB:false,
        // cargar desde localStorage
        load() {
          try {
            this.collapseA = JSON.parse(localStorage.getItem('recon:collapseA') ?? 'false');
            this.collapseB = JSON.parse(localStorage.getItem('recon:collapseB') ?? 'false');
          } catch (_) {
            this.collapseA = false; this.collapseB = false;
          }
        }
      }"
      x-init="
        load();
        $watch('collapseA', v => localStorage.setItem('recon:collapseA', JSON.stringify(v)));
        $watch('collapseB', v => localStorage.setItem('recon:collapseB', JSON.stringify(v)));
      "
      x-cloak
      class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-sm overflow-hidden"
    >
      <style>[x-cloak]{display:none!important}</style>

      <!-- HEADER #1 -->
      <button type="button" @click="collapseA = !collapseA"
              class="w-full px-4 sm:px-5 py-3 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between cursor-pointer">
        <div class="flex items-center gap-3">
          <h2 class="text-sm sm:text-base font-semibold text-neutral-800 dark:text-neutral-100">
            Reconocimientos
          </h2>
          <span class="text-xs text-neutral-500 dark:text-neutral-400">
            @isset($reconocimientosImagenes) {{ $reconocimientosImagenes->count() }} elementos @endisset
          </span>
        </div>
        <svg class="h-5 w-5 text-neutral-500 transition-transform duration-200"
             :class="collapseA ? '-rotate-90' : 'rotate-0'"
             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.213l3.71-3.98a.75.75 0 111.08 1.04l-4.24 4.54a.75.75 0 01-1.08 0l-4.24-4.54a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
        </svg>
      </button>

      <!-- Toolbar -->
      <div class="px-4 sm:px-5 py-2 flex items-center gap-3 border-b border-neutral-200 dark:border-neutral-800">
        <button type="button"
                class="text-xs px-2 py-1 rounded border dark:border-neutral-700"
                x-on:click="$wire.set('reconocimiento_id', null)">
          Limpiar selección
        </button>
        @error('reconocimiento_id')
          <span class="text-xs text-red-600">{{ $message }}</span>
        @enderror
      </div>

      <!-- GRID PLANTILLAS (COLLAPSE #1) -->
      <!-- Nota: se muestra cuando collapseA es FALSE -->
      <div x-show="!collapseA" x-collapse class="overflow-hidden">
        @forelse($reconocimientosImagenes as $plantilla)
          @if($loop->first)
            <div class="p-4 sm:p-5">
              <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4" id="grid-reconocimientos">
          @endif

                <figure class="group relative rounded-xl overflow-hidden border border-neutral-200 dark:border-neutral-800 bg-white/60 dark:bg-neutral-900/60"
                        wire:key="recon-{{ $plantilla->id }}">
                  <!-- Radio: una sola plantilla -->
                  <label class="absolute left-2 top-2 z-10 inline-flex items-center gap-2">
                    <input
                      type="radio"
                      name="reconocimiento_imagen_id"
                      wire:model="reconocimiento_imagen_id"
                      value="{{ $plantilla->id }}"
                      class="h-4 w-4 rounded border-neutral-300 text-indigo-600 focus:ring-indigo-500"
                    />
                    <span class="sr-only">Seleccionar</span>
                  </label>

                  @if(!empty($plantilla->imagen))
                    <img
                      src="{{ asset('storage/imagenesReconocimientos/'.$plantilla->imagen) }}"
                      alt="Plantilla {{ $plantilla->id }}"
                      class="h-40 w-full object-cover cursor-zoom-in transition duration-200 group-hover:scale-[1.02]"
                      data-src="{{ asset('storage/imagenesReconocimientos/'.$plantilla->imagen) }}"
                      data-title="{{ $plantilla->descripcion ?? 'Sin título' }}"
                      @click="src = $el.dataset.src; title = $el.dataset.title; open = true"
                    >
                  @else
                    <div class="h-40 w-full grid place-content-center text-neutral-400 bg-neutral-50 dark:bg-neutral-800">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M21 19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7h2l2-3h10l2 3h2zM8 13a4 4 0 1 0 8 0a4 4 0 0 0-8 0z"/>
                      </svg>
                    </div>
                  @endif

                  <figcaption class="px-3 py-2 text-xs text-neutral-600 dark:text-neutral-300 truncate">
                    {{ $plantilla->descripcion ?? 'Sin descripción' }}
                  </figcaption>
                </figure>

          @if($loop->last)
              </div>
            </div>
          @endif
        @empty
          <div class="p-8 text-center text-neutral-500">
            No hay reconocimientos.
          </div>
        @endforelse
      </div>

      <!-- HEADER #2 -->
      <button type="button" @click="collapseB = !collapseB"
              class="w-full px-4 sm:px-5 py-3 border-t border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between cursor-pointer">
        <div class="flex items-center gap-3">
          <h2 class="text-sm sm:text-base font-semibold text-neutral-800 dark:text-neutral-100">
            Formulario del reconocimiento
          </h2>
          <span class="text-xs text-neutral-500 dark:text-neutral-400">Completa los datos</span>
        </div>
        <svg class="h-5 w-5 text-neutral-500 transition-transform duration-200"
             :class="collapseB ? '-rotate-90' : 'rotate-0'"
             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.213l3.71-3.98a.75.75 0 111.08 1.04l-4.24 4.54a.75.75 0 01-1.08 0l-4.24-4.54a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
        </svg>
      </button>

      <!-- CONTENIDO #2 (COLLAPSE B) -->
      <!-- Nota: se muestra cuando collapseB es FALSE -->
      <div x-show="!collapseB" x-collapse class="overflow-hidden">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 px-4 sm:px-5 pb-4 mt-4">
          <flux:input
            label="Reconocimiento a:"
            placeholder="Nombre de la persona/institución"
            badge="Requerido"
            wire:model.defer="reconocimiento"
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

        <div class="px-4 sm:px-5">
          <label for="descripcionReconocimiento" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Descripción del Reconocimiento
          </label>
          <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">
            Este texto aparecerá en la ficha del reconocimiento. Puedes usar el editor enriquecido.
          </p>

          <!-- Con TinyMCE -->
          <div wire:ignore>
            <textarea
              id="descripcionReconocimiento"
              rows="6"
              class="block w-full min-h-[140px] rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 p-3 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y shadow-sm transition"
              placeholder="Escribe la descripción del reconocimiento..."></textarea>
          </div>
          @error('descripcion')
            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
          @enderror
          <p class="mt-2 text-xs text-neutral-400">Máx. recomendado 500 caracteres.</p>
        </div>

        <!-- DIRECTIVOS (múltiple) -->
        <div class="px-4 sm:px-5 mt-4">
          <p class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
            Selecciona los directivos para el reconocimiento
          </p>

          <div class="grid sm:grid-cols-2 gap-2">
            <flux:checkbox.group wire:model="directivos">
              @foreach ($directivosLista as $d)
                <flux:checkbox label="{{ $d->titulo }} {{ $d->nombre }} {{ $d->apellido }}" value="{{ $d->id }}" />
              @endforeach
            </flux:checkbox.group>
          </div>

          @error('directivos')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <!-- BOTÓN -->
        <div class="px-4 sm:px-5 mt-4 pb-5">
          <button type="submit"
                  class="cursor-pointer inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-25 transition">
            Guardar Reconocimiento
          </button>
        </div>
      </div>

      <!-- MODAL PREVIEW -->
      <div x-show="open" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center" style="display:none;">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="open=false" aria-hidden="true"></div>
        <div x-show="open" x-transition @keydown.escape.window="open=false"
             class="relative z-10 max-w-3xl w-full mx-4 sm:mx-6 bg-white dark:bg-neutral-900 rounded-2xl overflow-hidden shadow-2xl ring-1 ring-black/5 dark:ring-white/10"
             role="dialog" aria-modal="true" aria-label="Imagen ampliada">
          <div class="flex justify-between items-start p-4 border-b dark:border-neutral-800">
            <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100" x-text="title"></h3>
            <button @click="open=false" class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200">
              <span class="sr-only">Cerrar</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 8.586L4.293 2.879A1 1 0 102.879 4.293L8.586 10l-5.707 5.707a1 1 0 101.414 1.414L10 11.414l5.707 5.707a1 1 0 001.414-1.414L11.414 10l5.707-5.707a1 1 0 00-1.414-1.414L10 8.586z" clip-rule="evenodd"/>
              </svg>
            </button>
          </div>
          <div class="p-4 flex justify-center items-center">
            <img :src="src" alt="" class="max-h-[70vh] w-auto object-contain rounded">
          </div>
        </div>
      </div>
    </div>
  </form>

  {{-- MOSTRAR EN UNA TABLA --}}
  <div class="px-4 sm:px-5 mt-4 bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm">
    <h2 class="text-sm sm:text-base font-semibold text-neutral-800 dark:text-neutral-100 mb-3 p-3">
      Reconocimientos creados
    </h2>
    <livewire:reconocimientos.mostrar-reconocimientos />
  </div>

{{-- TinyMCE v8 --}}
<script>
(function () {
  const TINY_SRC = "https://cdn.tiny.cloud/1/1ebweotq439cl3bk11wscr1wf0h3iemo2t74u6ve9sjcy7cl/tinymce/8/tinymce.min.js";
  const SELECTOR = "#descripcionReconocimiento";

  function loadTiny(cb){
    if(window.tinymce) return cb();
    const s=document.createElement("script");
    s.src=TINY_SRC; s.referrerPolicy="origin"; s.crossOrigin="anonymous";
    s.onload=cb; s.onerror=()=>console.error("[TinyMCE] Falló la carga");
    document.head.appendChild(s);
  }

  function initTiny(){
    const el = document.querySelector(SELECTOR);
    if(!el){ console.warn("[TinyMCE] No se encontró", SELECTOR); return; }

    const inst = window.tinymce.get(el.id);
    if(inst) inst.remove();

    window.tinymce.init({
      selector: SELECTOR,
      plugins: "table lists",
      toolbar: "undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | table",
      setup: (editor) => {
        editor.on("init", () => {
          const val = @json($this->descripcion ?? '');
          if(val) editor.setContent(val);
        });
        editor.on("change keyup blur", () => {
          @this.set('descripcion', editor.getContent());
        });
      },
    });
  }

  document.addEventListener("livewire:navigated", () => loadTiny(initTiny)); // Livewire v3
  document.addEventListener("DOMContentLoaded", () => loadTiny(initTiny));
})();
</script>

<!-- Asegúrate de tener Alpine + plugin collapse en tu layout principal -->
<!-- <script src="//unpkg.com/alpinejs" defer></script> -->
<!-- <script src="//unpkg.com/@alpinejs/collapse" defer></script> -->
