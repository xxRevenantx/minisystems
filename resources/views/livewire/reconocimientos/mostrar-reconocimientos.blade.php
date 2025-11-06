<div class="relative"
  x-data ="{
        destroyReconocimiento(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `El reconocimiento se eliminará de forma permanente`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#EF4444',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('eliminarReconocimiento', id);
                }
            })
        }
    }"
>
  {{-- Barra de búsqueda --}}
  <div class="flex items-center gap-2 my-4">
    <flux:input
      wire:model.live.debounce.350ms="search"
      icon="magnifying-glass"
      placeholder="Buscar por nombre, descripción, fecha o directivo…"
      class="w-full"
    />
    @if($search !== '')
      <button
        wire:click="clearSearch"
        class="inline-flex items-center px-3 py-2 rounded-xl border border-neutral-200 dark:border-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
        Limpiar
      </button>
    @endif
  </div>

  {{-- Meta: resultados --}}
  <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">
    @if($search === '')
      Mostrando {{ $reconocimientos->total() }} reconocimiento(s)
    @else
      Resultado para “<span class="font-medium">{{ $search }}</span>”:
      {{ $reconocimientos->total() }} coincidencia(s)
    @endif
  </div>

  {{-- Contenedor con overlay de loader --}}
  <div class="relative rounded-2xl border border-neutral-200 dark:border-neutral-800 overflow-hidden">

    {{-- Loader overlay --}}
    <div
      wire:loading.delay.shortest
      class="absolute inset-0 z-10 flex items-center justify-center bg-white/65 dark:bg-neutral-900/65 backdrop-blur-sm">
      <div class="flex items-center gap-3 rounded-xl bg-white dark:bg-neutral-900 px-4 py-3 ring-1 ring-neutral-200 dark:ring-neutral-800 shadow">
        <svg class="h-5 w-5 animate-spin text-indigo-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
        <span class="text-sm">Buscando…</span>
      </div>
    </div>

    {{-- Tabla --}}
    <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-800">
      <thead class="bg-neutral-50 dark:bg-neutral-800">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Nombre</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Autoridades</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Fecha</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Acciones</th>
        </tr>
      </thead>

      {{-- Skeleton mientras carga --}}
     <tbody
  class="bg-white dark:bg-neutral-900 divide-y divide-neutral-200 dark:divide-neutral-800"
  wire:loading.class="opacity-60">

  @php $colspan = 5; @endphp

  @if($reconocimientos->total() === 0 && $search === '')
    {{-- Estado vacío: no hay nada en la BD --}}
    <tr>
      <td colspan="{{ $colspan }}" class="px-6 py-12 text-center">
        <div class="mx-auto max-w-md">
          <div class="mx-auto mb-3 h-10 w-10 rounded-full bg-neutral-100 dark:bg-neutral-800 grid place-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-neutral-500" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2m1 15h-2v-2h2zm0-4h-2V7h2z"/>
            </svg>
          </div>
          <p class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Aún no hay reconocimientos</p>
          <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Cuando registres el primero, aparecerá aquí.</p>
          {{-- Acción opcional --}}
          {{-- <a href="{{ route('reconocimiento.crear') }}" class="mt-4 inline-flex items-center px-3 py-2 rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Crear reconocimiento</a> --}}
        </div>
      </td>
    </tr>

  @elseif($reconocimientos->count() === 0 && $search !== '')
    {{-- Sin coincidencias para la búsqueda actual --}}
    <tr>
      <td colspan="{{ $colspan }}" class="px-6 py-10 text-center text-sm text-neutral-500 dark:text-neutral-300">
        No se encontraron resultados para
        “<span class="font-medium">{{ $search }}</span>”.
        <button wire:click="clearSearch" class="ml-2 underline hover:no-underline">Limpiar búsqueda</button>
      </td>
    </tr>

  @else
    {{-- Hay resultados --}}
    @foreach($reconocimientos as $reconocimiento)
      <tr>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-neutral-100">

          <flux:heading class="flex items-center gap-2">
                      {{ $reconocimiento->reconocimiento_a }}
            <flux:tooltip toggleable>
              <flux:button icon="information-circle" size="sm" variant="ghost" />
              <flux:tooltip.content class="max-w-[22rem] space-y-2">
                <p class="text-neutral-700 dark:text-neutral-300">{!! $reconocimiento->descripcion !!}</p>
              </flux:tooltip.content>
            </flux:tooltip>
          </flux:heading>
        </td>


        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-300">
          @forelse($reconocimiento->directivos as $directivo)
            <div>
              {{ $directivo->titulo }} {{ $directivo->nombre }} {{ $directivo->apellido_paterno }} {{ $directivo->apellido_materno }} - {{ $directivo->cargo }}
            </div>
          @empty
            <span class="text-neutral-400">Sin directivos</span>
          @endforelse
        </td>

        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-300">
          {{ \Carbon\Carbon::parse($reconocimiento->fecha)->format('d/m/Y') }}
        </td>

        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-300">
          <div class="flex space-x-2">
            <form action="{{ route('reconocimiento.pdf', $reconocimiento->id) }}" method="GET" target="_blank">
              <button type="submit" class="inline-flex items-center cursor-pointer px-3 py-1.5 rounded-md text-white bg-green-600 hover:bg-green-700 transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                PDF
              </button>
            </form>

            <a href="{{ route('reconocimiento.editar', $reconocimiento->id) }}" class="inline-flex items-center cursor-pointer px-3 py-1.5 rounded-md text-white bg-blue-600 hover:bg-blue-700 transition">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              Editar
            </a>

            <button class="inline-flex items-center cursor-pointer px-3 py-1.5 rounded-md text-white bg-red-600 hover:bg-red-700 transition"
                @click="destroyReconocimiento({{ $reconocimiento->id }})"
            >
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              Eliminar
            </button>
          </div>
        </td>
      </tr>
    @endforeach
  @endif
</tbody>

    </table>
  </div>

  {{-- Paginación --}}
  <div class="mt-4">
    {{ $reconocimientos->onEachSide(1)->links() }}
  </div>
</div>
