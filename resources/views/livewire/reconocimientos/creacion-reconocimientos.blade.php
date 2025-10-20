<div
  x-data="reconocimientosCardSingle()"
  x-init="init()"
  x-cloak
  class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-sm overflow-hidden"
>
<style>
  /* Opcional: oculta [x-cloak] hasta que Alpine inicialice */
  [x-cloak] { display: none !important; }
</style>

  <!-- HEADER (colapsable) -->
  <button type="button" @click="collapse = !collapse"
          class="w-full px-4 sm:px-5 py-3 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between cursor-pointer">
    <div class="flex items-center gap-3">
      <h2 class="text-sm sm:text-base font-semibold text-neutral-800 dark:text-neutral-100">
        Reconocimientos
      </h2>
      <span class="text-xs text-neutral-500 dark:text-neutral-400">
        @isset($reconocimientosImagenes) {{ count($reconocimientosImagenes) }} elementos @endisset
      </span>

    </div>
    <svg class="h-5 w-5 text-neutral-500 transition-transform duration-200"
         :class="collapse ? '-rotate-90' : 'rotate-0'"
         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
      <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.213l3.71-3.98a.75.75 0 111.08 1.04l-4.24 4.54a.75.75 0 01-1.08 0l-4.24-4.54a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
    </svg>
  </button>

  <!-- Toolbar -->
  <div class="px-4 sm:px-5 py-2 flex items-center gap-3 border-b border-neutral-200 dark:border-neutral-800">
    <button type="button" @click="clearSelection()" class="text-xs px-2 py-1 rounded border dark:border-neutral-700">
      Limpiar selección
    </button>
  </div>

  <!-- BODY colapsable (abre en primer clic gracias a x-collapse) -->
  <div x-show="!collapse" x-collapse class="overflow-hidden">
    @forelse($reconocimientosImagenes as $reconocimiento)
      @if($loop->first)
        <div class="p-4 sm:p-5">
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4" id="grid-reconocimientos">
      @endif

            <figure class="group relative rounded-xl overflow-hidden border border-neutral-200 dark:border-neutral-800 bg-white/60 dark:bg-neutral-900/60"
                    wire:key="recon-{{ $reconocimiento->id ?? $loop->index }}">
              <!-- Checkbox (solo uno a la vez) -->
              <label class="absolute left-2 top-2 z-10 inline-flex items-center gap-2">
                <input
                  type="checkbox"
                  value="{{ $reconocimiento->id }}"
                  :checked="selectedId === {{ $reconocimiento->id }}"
                  @change="onChange({{ $reconocimiento->id }}, $event.target.checked)"
                  class="h-4 w-4 rounded border-neutral-300 text-indigo-600 focus:ring-indigo-500"
                />
                <span class="sr-only">Seleccionar</span>
              </label>

              <!-- Campo real para el formulario -->
              <template x-if="selectedId === {{ $reconocimiento->id }}">
                <input type="hidden" name="selected_reconocimientos[]" value="{{ $reconocimiento->id }}">
              </template>

              @if(!empty($reconocimiento->imagen))
                <img
                  src="{{ asset('storage/imagenesReconocimientos/'.$reconocimiento->imagen) }}"
                  alt="Imagen reconocimiento {{ $reconocimiento->id }}"
                  class="h-40 w-full object-cover cursor-zoom-in transition duration-200 group-hover:scale-[1.02]"
                  data-src="{{ asset('storage/imagenesReconocimientos/'.$reconocimiento->imagen) }}"
                  data-title="{{ $reconocimiento->descripcion ?? 'Sin título' }}"
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
                {{ $reconocimiento->descripcion ?? 'Sin descripción' }}
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





  <!-- MODAL -->
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



  {{-- FORMULARIO PARA CREAR  RECONOCIMIENTOS --}}
  <form action="#"class="p-4">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <flux:input label="Nombre del Reconocimiento" description="Este será el nombre del reconocimiento." />
    <div class="col-span-1 sm:col-span-2">
        <label for="textarea" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Descripción del Reconocimiento
        </label>
        <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">
            Texto que aparecerá en la ficha del reconocimiento. Puedes usar el editor enriquecido.
        </p>

        <textarea  wire:ignore
            id="descripcionReconocimiento"
            name="descripcionReconocimiento"
            rows="6"
            class="block w-full min-h-[140px] rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 p-3 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y shadow-sm transition"
            placeholder="Escribe la descripción del reconocimiento..."
        ></textarea>

        <p class="mt-2 text-xs text-neutral-400">
            Máx. recomendado 500 caracteres.
        </p>
    </div>
    </div>
    <div class="mt-4">
      <button type="submit"
              class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-25 transition">
        Guardar Reconocimiento
      </button>
    </div>
    </form>


</div>

{{-- <script src="https://cdn.tiny.cloud/1/1ebweotq439cl3bk11wscr1wf0h3iemo2t74u6ve9sjcy7cl/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script> --}}

<script>
(function () {
  // Usa SOLO una versión. Cambia a v8 o v7, pero no mezcles.
  const TINY_SRC = "https://cdn.tiny.cloud/1/1ebweotq439cl3bk11wscr1wf0h3iemo2t74u6ve9sjcy7cl/tinymce/8/tinymce.min.js";

  const TEXTAREA_SELECTOR = "textarea#descripcionReconocimiento";

  function loadTiny(callback) {
    if (window.tinymce) return callback();
    const s = document.createElement("script");
    s.src = TINY_SRC;
    s.referrerPolicy = "origin";
    s.crossOrigin = "anonymous";
    s.onload = callback;
    s.onerror = () => console.error("[TinyMCE] No se pudo cargar el script:", TINY_SRC);
    document.head.appendChild(s);
  }

  function initTiny() {
    const el = document.querySelector(TEXTAREA_SELECTOR);
    if (!el) {
      console.warn("[TinyMCE] No se encontró", TEXTAREA_SELECTOR);
      return;
    }

    // Si ya existe una instancia sobre ese id, la removemos para re-inicializar
    const existing = window.tinymce?.get(el.id);
    if (existing) existing.remove();

    window.tinymce.init({
      selector: TEXTAREA_SELECTOR,
      plugins: "code table lists",
      toolbar: "undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table",
      setup: (editor) => {
        editor.on("init", () => console.log("[TinyMCE] Editor listo:", editor.id));
      },
    });
  }

  // Inicial: carga + init cuando el DOM está listo
  document.addEventListener("DOMContentLoaded", () => {
    loadTiny(initTiny);
  });

  // Livewire: re-init tras updates del DOM
  document.addEventListener("livewire:load", () => {
    // Para Livewire v3 (morphdom)
    if (window.Livewire?.hook) {
      Livewire.hook("morph.updated", () => loadTiny(initTiny));
    }
  });
})();
</script>


<script>
  function reconocimientosCardSingle() {
    const STORAGE_KEY = 'reconocimientos:selected:single'; // persistencia single
    return {
      // UI
      open: false,
      src: '',
      title: '',
      collapse: false, // inicia abierto (cámbialo a true si quieres iniciar cerrado)
      // Estado persistente: un solo ID o null
      selectedId: null,

      init() {
        // Cargar selección previa (single)
        try {
          const saved = localStorage.getItem(STORAGE_KEY);
          if (saved !== null && saved !== 'null') {
            this.selectedId = Number(saved);
          }
        } catch(_) {}

        // Sincroniza los checkboxes visibles
        queueMicrotask(() => this.syncCheckboxes());
      },

      persist() {
        localStorage.setItem(STORAGE_KEY, this.selectedId ?? 'null');
      },

      onChange(id, checked) {
        if (checked) {
          // Selecciona este y desmarca los demás
          this.selectedId = Number(id);
          this.syncCheckboxes();
        } else {
          // Si se desmarca el actual, deja ninguno
          if (this.selectedId === Number(id)) this.selectedId = null;
        }
        this.persist();
      },

      syncCheckboxes() {
        document.querySelectorAll('#grid-reconocimientos input[type="checkbox"]').forEach(cb => {
          const id = Number(cb.value || cb.getAttribute('data-id'));
          cb.checked = (this.selectedId === id);
        });
      },

      clearSelection() {
        this.selectedId = null;
        this.persist();
        this.syncCheckboxes();
      }
    }
  }
</script>



