<div class="grid gap-6 xl:grid-cols-[1fr_360px]">
    <form wire:submit="actualizarReconocimiento"
        class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="lg">Editar reconocimiento</flux:heading>
                <flux:text class="mt-1">
                    Versión {{ $reconocimiento->version }} · Creado {{ $reconocimiento->created_at?->format('d/m/Y H:i') }}
                </flux:text>
            </div>
            <flux:button href="{{ route('reconocimiento', ['tab' => 'reconocimientos']) }}" variant="filled">
                Volver
            </flux:button>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <flux:select wire:model="reconocimiento_evento_id" label="Evento">
                <flux:select.option value="">Sin evento</flux:select.option>
                @foreach($eventos as $e)
                    <flux:select.option value="{{ $e->id }}">{{ $e->nombre }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="reconocimiento_tipo_id" label="Tipo">
                <flux:select.option value="">Personalizado</flux:select.option>
                @foreach($tipos as $t)
                    <flux:select.option value="{{ $t->id }}">{{ $t->nombre }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model="estado" label="Estado">
                @foreach(\App\Models\Reconocimiento::ESTADOS as $e)
                    <flux:select.option value="{{ $e }}">{{ ucfirst($e) }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <flux:input wire:model="reconocimiento_a" label="Destinatario" badge="Obligatorio" />
            <flux:input wire:model="lugar_obtenido" label="Lugar obtenido" />
        </div>

        <div class="mt-4">
            <x-tinymce-editor
                model="descripcion"
                editor-id="reconocimiento-descripcion-editar"
                label="Descripción"
                badge="Obligatorio"
                :height="280"
                placeholder="Escribe el motivo del reconocimiento..."
                description="Puedes aplicar negritas, cursivas, subrayado y listas. El contenido se sanitiza antes de guardarse." />
        </div>

        <div class="mt-4 max-w-xs">
            <flux:input wire:model="fecha" type="date" label="Fecha" badge="Obligatorio" />
        </div>

        @if($estado === 'entregado')
            <div class="mt-5 rounded-xl border border-green-200 bg-green-50/60 p-4 dark:border-green-900 dark:bg-green-950/20">
                <flux:heading size="sm">Información de entrega</flux:heading>
                <div class="mt-3 grid gap-3 md:grid-cols-3">
                    <flux:select wire:model="delivery_method" label="Método de entrega">
                        <flux:select.option value="">Seleccionar</flux:select.option>
                        <flux:select.option value="impreso">Impreso</flux:select.option>
                        <flux:select.option value="correo">Correo</flux:select.option>
                        <flux:select.option value="whatsapp">WhatsApp</flux:select.option>
                        <flux:select.option value="digital">Digital</flux:select.option>
                        <flux:select.option value="ceremonia">Ceremonia</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="delivery_to" label="Recibido por" />
                    <flux:input wire:model="delivery_notes" label="Observaciones" />
                </div>
            </div>
        @endif

        @if($estado === 'cancelado')
            <div class="mt-5 rounded-xl border border-red-200 bg-red-50/60 p-4 dark:border-red-900 dark:bg-red-950/20">
                <flux:textarea wire:model="cancel_reason" label="Motivo de cancelación" badge="Obligatorio" rows="3" />
            </div>
        @endif

        <section class="mt-6">
            <div class="mb-3 flex items-center justify-between">
                <flux:heading size="sm">Firmantes</flux:heading>
                <flux:badge color="zinc">Máximo 5</flux:badge>
            </div>

            <div class="grid gap-2 md:grid-cols-2">
                @foreach($directivosLista as $d)
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-neutral-200 p-3 transition hover:border-[#88AC2E] dark:border-neutral-700">
                        <flux:checkbox wire:model="directivos" value="{{ $d->id }}" />
                        <span>
                            <strong>{{ $d->nombre_completo }}</strong>
                            <small class="block text-neutral-500">{{ $d->cargo }}</small>
                        </span>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="mt-6">
            <flux:heading size="sm" class="mb-3">Plantilla</flux:heading>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($reconocimientosImagenes as $p)
                    <label
                        class="relative cursor-pointer overflow-hidden rounded-xl border-2 transition {{ (int)$reconocimiento_imagen_id === $p->id ? 'border-[#88AC2E] ring-2 ring-[#88AC2E]/20' : 'border-neutral-200 dark:border-neutral-700' }}">
                        <input type="radio" wire:model="reconocimiento_imagen_id" value="{{ $p->id }}"
                            class="absolute left-3 top-3 z-10 h-4 w-4 accent-[#88AC2E]">
                        <img src="{{ asset('storage/imagenesReconocimientos/'.$p->imagen) }}"
                            class="h-28 w-full object-cover" alt="Plantilla">
                        <div class="p-2 text-xs font-bold">{{ $p->nombre ?: $p->descripcion }}</div>
                    </label>
                @endforeach
            </div>
        </section>

        @if($errors->any())
            <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/20 dark:text-red-300">
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-6 flex justify-end">
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled"
                class="!bg-[#006492] hover:!bg-[#00557b]">
                <span wire:loading.remove wire:target="actualizarReconocimiento">Guardar cambios</span>
                <span wire:loading wire:target="actualizarReconocimiento">Guardando…</span>
            </flux:button>
        </div>
    </form>

    <aside class="space-y-5">
        <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <flux:heading size="md">Enviar por correo</flux:heading>
            <flux:text class="mt-1 text-xs">Requiere que el correo SMTP esté configurado en el archivo .env.</flux:text>

            <div class="mt-4 space-y-3">
                <flux:input wire:model="delivery_to" type="email" label="Correo del destinatario"
                    placeholder="correo@ejemplo.com" />
                <flux:input wire:model="correo_asunto" label="Asunto" />
                <flux:textarea wire:model="correo_mensaje" label="Mensaje" rows="3" />
                <flux:button type="button" variant="primary" wire:click="enviarCorreo" wire:loading.attr="disabled"
                    class="w-full !bg-[#006492] hover:!bg-[#00557b]">
                    <span wire:loading.remove wire:target="enviarCorreo">Enviar PDF por correo</span>
                    <span wire:loading wire:target="enviarCorreo">Enviando…</span>
                </flux:button>
            </div>
        </section>

        <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <flux:heading size="md">Historial</flux:heading>
            <div class="mt-3 max-h-[600px] space-y-3 overflow-auto">
                @forelse($historial as $h)
                    <article class="border-l-2 border-[#006492] pl-3">
                        <strong class="text-sm">{{ str_replace('_', ' ', ucfirst($h->accion)) }}</strong>
                        <p class="text-xs text-neutral-500">{{ $h->descripcion }}</p>
                        <small class="text-[10px] text-neutral-400">
                            {{ $h->created_at?->format('d/m/Y H:i') }} · {{ $h->usuario?->name ?: 'Sistema' }}
                        </small>
                    </article>
                @empty
                    <flux:text>Sin movimientos registrados.</flux:text>
                @endforelse
            </div>
        </section>

        <flux:button href="{{ route('reconocimiento.pdf', $reconocimiento) }}" target="_blank"
            variant="primary" class="w-full !bg-[#88AC2E] hover:!bg-[#759726]">
            Abrir PDF
        </flux:button>
    </aside>
</div>
