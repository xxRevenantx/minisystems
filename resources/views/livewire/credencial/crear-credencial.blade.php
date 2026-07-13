<div
    x-data="{
        abierto: JSON.parse(localStorage.getItem('collapse_crear_credencial') ?? 'true'),
        guardado: @entangle('guardado'),
        alternar() {
            this.abierto = !this.abierto;
            localStorage.setItem('collapse_crear_credencial', JSON.stringify(this.abierto));
        }
    }"
    x-init="$watch('guardado', value => { if(value) setTimeout(() => guardado = false, 2800) })"
    class="space-y-6"
>
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50 dark:border-zinc-800 dark:bg-zinc-950 dark:shadow-none">
        <flux:button variant="primary" type="button" @click="alternar"
            class="relative flex w-full items-center justify-between overflow-hidden bg-gradient-to-r from-[#006492] via-sky-600 to-[#88AC2E] px-6 py-6 text-left text-white">
            <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="relative">
                <p class="text-xs font-black uppercase tracking-[.26em] text-white/75">Credenciales generales</p>
                <h2 class="mt-2 text-2xl font-black">Crear una identificación</h2>
                <p class="mt-1 text-sm text-white/85">Gafetes de evento, personal, visitantes, membresías o credenciales escolares.</p>
            </div>
            <span class="relative rounded-2xl bg-white/15 px-4 py-2 text-xs font-black" x-text="abierto ? 'Cerrar formulario' : 'Abrir formulario'"></span>
        </flux:button>

        <div x-show="abierto" x-cloak x-transition class="p-6">
            <template x-if="guardado">
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-200">
                    <strong>Credencial registrada correctamente.</strong>
                    <span class="block text-xs">Ya está disponible para consulta y exportación.</span>
                </div>
            </template>

            @if($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-200">
                    <p class="font-black">Revisa los campos marcados:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form wire:submit.prevent="guardar" class="space-y-7">
                <section>
                    <div class="mb-4">
                        <p class="text-xs font-black uppercase tracking-widest text-[#006492]">Contexto</p>
                        <h3 class="text-lg font-black">Tipo y vinculación</h3>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <label class="text-sm font-bold">Tipo de credencial
                            <flux:select wire:model.live="tipo" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">
                                @foreach($tipos as $value => $label)<flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>@endforeach
                            </flux:select>
                        </label>
                        <label class="text-sm font-bold">Persona existente
                            <flux:select wire:model.live="persona_id" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">
                                <flux:select.option value="">Captura manual</flux:select.option>
                                @foreach($personas as $persona)<flux:select.option value="{{ $persona->id }}">{{ $persona->nombre }}</flux:select.option>@endforeach
                            </flux:select>
                        </label>
                        <label class="text-sm font-bold">Marca o cliente
                            <flux:select wire:model="marca_id" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">
                                <flux:select.option value="">Sin marca</flux:select.option>
                                @foreach($marcas as $marca)<flux:select.option value="{{ $marca->id }}">{{ $marca->nombre }}</flux:select.option>@endforeach
                            </flux:select>
                        </label>
                        <label class="text-sm font-bold">Proyecto o evento
                            <flux:select wire:model="proyecto_creativo_id" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">
                                <flux:select.option value="">Sin proyecto</flux:select.option>
                                @foreach($proyectos as $proyecto)<flux:select.option value="{{ $proyecto->id }}">{{ $proyecto->nombre }}</flux:select.option>@endforeach
                            </flux:select>
                        </label>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-slate-50/60 p-5 dark:border-zinc-800 dark:bg-zinc-900/40">
                    <div class="mb-4">
                        <p class="text-xs font-black uppercase tracking-widest text-[#006492]">Información principal</p>
                        <h3 class="text-lg font-black">Datos visibles en la credencial</h3>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <label class="text-sm font-bold xl:col-span-2">Nombre completo
                            <flux:input wire:model.live="nombre" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-950" placeholder="Nombre de la persona" />
                        </label>
                        <div class="grid grid-cols-[1fr_auto] gap-2">
                            <label class="text-sm font-bold">Folio o identificador
                                <flux:input wire:model="folio" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-950" placeholder="Se genera automáticamente" />
                            </label>
                            <flux:button type="button" wire:click="generarFolio" class="self-end rounded-2xl border border-slate-300 px-4 py-3 text-xs font-black dark:border-zinc-700">Generar</flux:button>
                        </div>
                        <label class="text-sm font-bold">Cargo o función
                            <flux:input wire:model="cargo" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-950" placeholder="Ponente, empleado, visitante..." />
                        </label>
                        <label class="text-sm font-bold">Organización
                            <flux:input wire:model="organizacion" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-950" />
                        </label>
                        <label class="text-sm font-bold">Vigencia
                            <flux:input wire:model="vigencia" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-950" placeholder="Ej. 31 de diciembre de 2026" />
                        </label>
                        <label class="text-sm font-bold">Correo
                            <flux:input wire:model="correo" type="email" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-950" />
                        </label>
                        <label class="text-sm font-bold">Teléfono
                            <flux:input wire:model="telefono" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-950" />
                        </label>
                        <label class="text-sm font-bold">Estado
                            <flux:select wire:model="estado" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-950">
                                @foreach(['activa','inactiva','vencida','cancelada'] as $value)<flux:select.option value="{{ $value }}">{{ ucfirst($value) }}</flux:select.option>@endforeach
                            </flux:select>
                        </label>
                        <label class="text-sm font-bold xl:col-span-2">Fotografía
                            <flux:input wire:model="foto" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full rounded-2xl border border-dashed border-slate-300 p-3 text-xs dark:border-zinc-700" />
                        </label>
                        <label class="text-sm font-bold xl:col-span-3">Domicilio o información adicional
                            <flux:textarea wire:model="domicilio" rows="3" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-950"></flux:textarea>
                        </label>
                    </div>
                </section>

                @if($tipo === 'escolar')
                    <section class="rounded-3xl border border-blue-200 bg-blue-50/70 p-5 dark:border-blue-900 dark:bg-blue-950/20">
                        <div class="mb-4">
                            <p class="text-xs font-black uppercase tracking-widest text-blue-700 dark:text-blue-300">Datos escolares opcionales</p>
                            <h3 class="text-lg font-black">Información académica</h3>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <label class="text-sm font-bold">Matrícula
                                <flux:input wire:model="matricula" class="mt-1 w-full rounded-2xl border-blue-200 dark:border-blue-900 dark:bg-zinc-950" />
                            </label>
                            <label class="text-sm font-bold">CURP
                                <flux:input wire:model="curp" maxlength="18" class="mt-1 w-full rounded-2xl border-blue-200 uppercase dark:border-blue-900 dark:bg-zinc-950" />
                            </label>
                            <label class="text-sm font-bold">Nivel
                                <flux:select wire:model.live="nivel" class="mt-1 w-full rounded-2xl border-blue-200 dark:border-blue-900 dark:bg-zinc-950">
                                    <flux:select.option value="">Seleccionar</flux:select.option>
                                    @foreach($niveles as $item)<flux:select.option value="{{ $item }}">{{ $item }}</flux:select.option>@endforeach
                                </flux:select>
                            </label>
                            <label class="text-sm font-bold">Ciclo escolar
                                <flux:input wire:model="ciclo_escolar" class="mt-1 w-full rounded-2xl border-blue-200 dark:border-blue-900 dark:bg-zinc-950" />
                            </label>

                            @if($nivel !== 'Licenciatura')
                                <label class="text-sm font-bold">Grado
                                    <flux:select wire:model="grado" class="mt-1 w-full rounded-2xl border-blue-200 dark:border-blue-900 dark:bg-zinc-950">
                                        <flux:select.option value="">Seleccionar</flux:select.option>
                                        @foreach($grados as $item)<flux:select.option value="{{ $item }}">{{ $item }}</flux:select.option>@endforeach
                                    </flux:select>
                                </label>
                                <label class="text-sm font-bold">Grupo
                                    <flux:select wire:model="grupo" class="mt-1 w-full rounded-2xl border-blue-200 dark:border-blue-900 dark:bg-zinc-950">
                                        <flux:select.option value="">Seleccionar</flux:select.option>
                                        @foreach($grupos as $item)<flux:select.option value="{{ $item }}">{{ $item }}</flux:select.option>@endforeach
                                    </flux:select>
                                </label>
                            @else
                                <label class="text-sm font-bold md:col-span-2">Licenciatura
                                    <flux:select wire:model="licenciatura" class="mt-1 w-full rounded-2xl border-blue-200 dark:border-blue-900 dark:bg-zinc-950">
                                        <flux:select.option value="">Seleccionar</flux:select.option>
                                        @foreach($licenciaturas as $item)<flux:select.option value="{{ $item }}">{{ $item }}</flux:select.option>@endforeach
                                    </flux:select>
                                </label>
                            @endif
                        </div>
                    </section>
                @endif

                <section class="rounded-3xl border border-violet-200 bg-violet-50/70 p-5 dark:border-violet-900 dark:bg-violet-950/20">
                    <label class="flex items-center justify-between gap-4">
                        <span><strong class="block text-sm text-violet-900 dark:text-violet-200">Diseñar reverso</strong><small class="text-xs text-slate-500">Agrega información adicional o una imagen para la parte posterior.</small></span>
                        <flux:checkbox wire:model.live="tiene_reverso" class="h-5 w-5 rounded border-violet-300 text-violet-600" />
                    </label>
                    @if($tiene_reverso)
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <label class="text-sm font-bold">Texto del reverso
                                <flux:textarea wire:model="reverso_texto" rows="5" class="mt-1 w-full rounded-2xl border-violet-200 dark:border-violet-900 dark:bg-zinc-950" placeholder="Indicaciones, contacto, términos, datos del evento..."></flux:textarea>
                            </label>
                            <label class="text-sm font-bold">Imagen de fondo del reverso
                                <flux:input wire:model="reverso_imagen" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full rounded-2xl border border-dashed border-violet-300 p-3 text-xs dark:border-violet-800" />
                                <small class="mt-2 block font-normal text-slate-500">Opcional. Si no se carga, se utilizarán los colores de la marca.</small>
                            </label>
                        </div>
                    @endif
                </section>

                <section class="flex flex-col gap-4 rounded-3xl border border-emerald-200 bg-emerald-50/70 p-5 dark:border-emerald-900 dark:bg-emerald-950/20 sm:flex-row sm:items-center sm:justify-between">
                    <label class="flex items-center gap-3">
                        <flux:checkbox wire:model="generarValidacion" class="h-5 w-5 rounded border-emerald-300 text-emerald-600" />
                        <span>
                            <strong class="block text-sm">Crear folio de validación pública</strong>
                            <small class="text-xs text-slate-500">Genera un código consultable desde una URL pública.</small>
                        </span>
                    </label>
                    <span class="rounded-full bg-white px-3 py-2 text-xs font-black text-emerald-700 shadow-sm dark:bg-zinc-950">Recomendado</span>
                </section>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end dark:border-zinc-800">
                    <flux:button type="button" wire:click="limpiarFormulario" class="rounded-2xl border border-slate-300 px-5 py-3 text-sm font-black dark:border-zinc-700">Limpiar</flux:button>
                    <flux:button variant="primary" type="submit" wire:loading.attr="disabled" class="rounded-2xl bg-[#006492] px-6 py-3 text-sm font-black text-white shadow-lg hover:bg-[#005477]">
                        <span wire:loading.remove wire:target="guardar">Guardar credencial</span>
                        <span wire:loading wire:target="guardar">Guardando...</span>
                    </flux:button>
                </div>
            </form>
        </div>
    </section>
</div>
