<div class="space-y-6" @if($generation && in_array($generation->estado, ['en_cola','analizando','preparando_imagenes'], true)) wire:poll.3s="refrescar" @endif>
    <section class="overflow-hidden rounded-[28px] border border-violet-200 bg-white shadow-xl shadow-violet-100/50 dark:border-violet-900/50 dark:bg-neutral-900 dark:shadow-none">
        <div class="relative overflow-hidden bg-gradient-to-br from-violet-600 via-fuchsia-600 to-pink-600 px-6 py-8 text-white sm:px-8">
            <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-white/15 blur-3xl"></div>
            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <span class="inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-[.18em]">Groq IA</span>
                    <h2 class="mt-3 text-3xl font-black">Redacción para redes sociales</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-violet-50">Analiza hasta 10 fotografías representativas y crea textos distintos para cada plataforma, con tono, emojis, hashtags, llamada a la acción y textos alternativos.</p>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center text-xs font-bold">
                    <span class="rounded-2xl bg-white/10 px-3 py-3">10 fotos</span>
                    <span class="rounded-2xl bg-white/10 px-3 py-3">5 redes</span>
                    <span class="rounded-2xl bg-white/10 px-3 py-3">3 variantes</span>
                </div>
            </div>
        </div>
    </section>

    @error('groq')<div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-700 dark:border-rose-900 dark:bg-rose-950/20 dark:text-rose-300">{{ $message }}</div>@enderror

    @if(!$generation)
        <form wire:submit="generar" class="space-y-6">
            <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:p-6">
                <div class="flex items-start gap-3">
                    <div class="flex size-11 items-center justify-center rounded-2xl bg-sky-100 text-xl dark:bg-sky-950/40">🖼️</div>
                    <div><h3 class="text-lg font-black text-slate-900 dark:text-white">1. Fotografías representativas</h3><p class="text-sm text-slate-500">Selecciona entre 1 y 10 imágenes. Las copias enviadas a IA se reducen y se guardan de forma privada durante 24 horas.</p></div>
                </div>

                <label class="mt-5 flex min-h-44 cursor-pointer flex-col items-center justify-center rounded-3xl border-2 border-dashed border-sky-200 bg-gradient-to-br from-sky-50 to-violet-50 p-8 text-center transition hover:border-sky-400 dark:border-sky-900/50 dark:from-sky-950/20 dark:to-violet-950/20">
                    <flux:input type="file" wire:model="images" multiple accept="image/jpeg,image/png,image/webp" class="sr-only" />
                    <span class="text-4xl">☁️</span>
                    <strong class="mt-3 text-slate-800 dark:text-white">Seleccionar fotografías</strong>
                    <small class="mt-1 text-slate-500">JPG, PNG o WebP · hasta 20 MB por imagen</small>
                </label>
                <div wire:loading wire:target="images" class="mt-3 text-sm font-semibold text-sky-600">Preparando selección...</div>
                @error('images')<p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror
                @error('images.*')<p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror

                @if($images)
                    <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        @foreach($images as $index => $image)
                            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-neutral-700 dark:bg-neutral-800">
                                <img src="{{ $image->temporaryUrl() }}" class="h-32 w-full object-cover" alt="Fotografía seleccionada {{ $index + 1 }}">
                                <div class="p-3"><p class="truncate text-xs font-bold">{{ $image->getClientOriginalName() }}</p><p class="mt-1 text-[11px] text-slate-500">{{ number_format($image->getSize()/1048576, 2) }} MB</p></div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:p-6">
                <div class="flex items-start gap-3"><div class="flex size-11 items-center justify-center rounded-2xl bg-emerald-100 text-xl dark:bg-emerald-950/40">📝</div><div><h3 class="text-lg font-black">2. Contexto confirmado</h3><p class="text-sm text-slate-500">La IA no inventará nombres, lugares ni resultados que no captures aquí.</p></div></div>
                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <flux:select wire:model.live="marcaId" label="Cliente o marca"><flux:select.option value="">Sin marca</flux:select.option>@foreach($marcas as $marca)<flux:select.option value="{{ $marca->id }}">{{ $marca->nombre }}</flux:select.option>@endforeach</flux:select>
                    <flux:select wire:model="proyectoId" label="Proyecto o campaña"><flux:select.option value="">Sin proyecto</flux:select.option>@foreach($proyectos as $proyecto)<flux:select.option value="{{ $proyecto->id }}">{{ $proyecto->nombre }}</flux:select.option>@endforeach</flux:select>
                    <flux:input wire:model="eventName" label="Nombre del evento" placeholder="Ej. Ceremonia de clausura" />
                    <flux:input type="date" wire:model="eventDate" label="Fecha" />
                    <flux:input wire:model="eventPlace" label="Lugar" />
                    <flux:input wire:model="eventType" label="Tipo de actividad" placeholder="Conferencia, ceremonia..." />
                    <flux:input wire:model="educationLevel" label="Nivel o público" placeholder="Licenciatura, comunidad..." />
                    <flux:input wire:model="authorizedPeople" label="Personas autorizadas" placeholder="Nombres que sí puede mencionar" />
                    <div class="md:col-span-2"><flux:textarea wire:model="objective" label="Objetivo de la publicación" badge="Obligatorio" rows="4" /></div>
                    <div class="md:col-span-2"><flux:textarea wire:model="achievements" label="Resultados o logros confirmados" rows="4" /></div>
                    <div class="md:col-span-2 xl:col-span-4"><flux:textarea wire:model="additionalContext" label="Contexto adicional" rows="4" /></div>
                </div>
            </section>

            <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:p-6">
                <div class="flex items-start gap-3"><div class="flex size-11 items-center justify-center rounded-2xl bg-amber-100 text-xl dark:bg-amber-950/40">🎨</div><div><h3 class="text-lg font-black">3. Estilo y plataformas</h3><p class="text-sm text-slate-500">Controla el tono y genera una versión adecuada para cada red.</p></div></div>
                <div class="mt-5 grid gap-4 lg:grid-cols-3">
                    <flux:select wire:model="tone" label="Tono"><flux:select.option value="institucional">🏛️ Institucional</flux:select.option><flux:select.option value="formal">📄 Formal</flux:select.option><flux:select.option value="emotivo">💙 Emotivo</flux:select.option><flux:select.option value="tranquilo">🌿 Tranquilo</flux:select.option><flux:select.option value="alegre">🎉 Alegre</flux:select.option><flux:select.option value="inspirador">✨ Inspirador</flux:select.option><flux:select.option value="juvenil">⚡ Juvenil</flux:select.option><flux:select.option value="promocional">📣 Promocional</flux:select.option><flux:select.option value="agradecimiento">🙏 Agradecimiento</flux:select.option><flux:select.option value="informativo">ℹ️ Informativo</flux:select.option><flux:select.option value="personalizado">✏️ Personalizado</flux:select.option></flux:select>
                    <flux:select wire:model="toneIntensity" label="Intensidad"><flux:select.option value="1">1 · Muy discreta</flux:select.option><flux:select.option value="2">2 · Suave</flux:select.option><flux:select.option value="3">3 · Equilibrada</flux:select.option><flux:select.option value="4">4 · Marcada</flux:select.option><flux:select.option value="5">5 · Muy marcada</flux:select.option></flux:select>
                    <flux:select wire:model="length" label="Extensión"><flux:select.option value="corta">Corta</flux:select.option><flux:select.option value="media">Media</flux:select.option><flux:select.option value="larga">Larga</flux:select.option></flux:select>
                    <flux:select wire:model="emojiLevel" label="Emojis"><flux:select.option value="ninguno">Sin emojis</flux:select.option><flux:select.option value="pocos">Pocos y discretos</flux:select.option><flux:select.option value="moderados">Moderados</flux:select.option><flux:select.option value="muchos">Muchos</flux:select.option></flux:select>
                    <flux:select wire:model="ctaType" label="Llamada a la acción"><flux:select.option value="automatico">Automática</flux:select.option><flux:select.option value="conoce_mas">Conoce más</flux:select.option><flux:select.option value="inscribete">Inscríbete</flux:select.option><flux:select.option value="comparte">Comparte</flux:select.option><flux:select.option value="felicita">Felicita</flux:select.option><flux:select.option value="contactanos">Contáctanos</flux:select.option><flux:select.option value="personalizado">Personalizada</flux:select.option><flux:select.option value="ninguno">Sin CTA</flux:select.option></flux:select>
                    <flux:input wire:model="customCta" label="CTA personalizada" />
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    @foreach(['facebook'=>'Facebook','instagram'=>'Instagram','whatsapp'=>'WhatsApp','tiktok'=>'TikTok','x'=>'X'] as $key=>$label)
                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border p-4 {{ in_array($key, $platforms, true) ? 'border-violet-400 bg-violet-50 dark:bg-violet-950/20' : 'border-slate-200 dark:border-neutral-700' }}"><flux:checkbox wire:model.live="platforms" value="{{ $key }}" /><strong>{{ $label }}</strong></label>
                    @endforeach
                </div>
                @error('platforms')<p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror
                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    <label class="flex items-center justify-between gap-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/20"><span><strong class="block">Las fotografías contienen menores</strong><small class="text-slate-500">Activa reglas de redacción y privacidad.</small></span><flux:checkbox wire:model.live="containsMinors" /></label>
                    <label class="flex items-center justify-between gap-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/20"><span><strong class="block">Existe autorización de difusión</strong><small class="text-slate-500">Será obligatoria antes de enviar a Publicaciones.</small></span><flux:checkbox wire:model.live="publicationAuthorized" /></label>
                </div>
            </section>

            <div class="flex justify-end"><flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="generar,images" class="!rounded-2xl !bg-gradient-to-r !from-violet-600 !to-fuchsia-600 !px-7"><span wire:loading.remove wire:target="generar">✨ Analizar y generar redacción</span><span wire:loading wire:target="generar">Preparando fotografías...</span></flux:button></div>
        </form>
    @else
        <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"><div><p class="text-xs font-black uppercase tracking-widest text-violet-600">Generación {{ $generation->uuid }}</p><h3 class="mt-1 text-xl font-black">{{ $generation->nombre_evento ?: 'Publicación para redes sociales' }}</h3></div><flux:badge color="{{ $generation->estado === 'completada' ? 'green' : ($generation->estado === 'con_errores' ? 'red' : 'violet') }}">{{ str_replace('_', ' ', strtoupper($generation->estado)) }}</flux:badge></div>

            @if(in_array($generation->estado, ['preparando_imagenes','en_cola','analizando'], true))
                <div class="mt-6 rounded-3xl border border-violet-200 bg-violet-50 p-8 text-center dark:border-violet-900 dark:bg-violet-950/20"><div class="mx-auto h-12 w-12 animate-spin rounded-full border-4 border-violet-200 border-t-violet-600"></div><h4 class="mt-4 font-black">Groq está analizando las fotografías</h4><p class="mt-1 text-sm text-slate-500">La página se actualizará automáticamente. Mantén activo el worker de la cola <code>ai-social</code>.</p></div>
            @elseif($generation->estado === 'con_errores')
                <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-700 dark:border-rose-900 dark:bg-rose-950/20 dark:text-rose-300"><strong>No fue posible completar la generación.</strong><p class="mt-1 text-sm">{{ $generation->mensaje_error }}</p></div>
            @else
                <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">@foreach($generation->imagenes as $image)<article class="overflow-hidden rounded-2xl border border-slate-200 dark:border-neutral-700"><img src="{{ route('images.social-ai.preview', $image) }}" class="h-32 w-full object-cover"><div class="p-3"><p class="truncate text-xs font-bold">{{ $image->nombre_original }}</p><p class="mt-1 line-clamp-2 text-[11px] text-slate-500">{{ $image->texto_alternativo ?: 'Texto alternativo pendiente' }}</p></div></article>@endforeach</div>

                <div class="mt-7 grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
                    <aside class="space-y-3">@foreach($generation->versiones->groupBy('plataforma') as $platform=>$versions)<div class="rounded-2xl border border-slate-200 p-3 dark:border-neutral-700"><h4 class="mb-2 font-black capitalize">{{ $platform }}</h4>@foreach($versions as $version)<button type="button" wire:click="seleccionarVersion({{ $version->id }})" class="mb-2 w-full rounded-xl border px-3 py-2 text-left text-sm font-semibold {{ $selectedVersionId === $version->id ? 'border-violet-500 bg-violet-50 text-violet-700 dark:bg-violet-950/30' : 'border-slate-200 dark:border-neutral-700' }}">{{ ucfirst($version->variante) }} · {{ $version->caracteres }} caracteres</button>@endforeach</div>@endforeach</aside>
                    <div class="rounded-3xl border border-slate-200 p-5 dark:border-neutral-700">
                        @if($selectedVersionId)
                            <div class="grid gap-4 md:grid-cols-2"><flux:input wire:model="editingTitle" label="Título" /><flux:input wire:model="editingCta" label="Llamada a la acción" /></div>
                            <div class="mt-4"><x-tinymce-editor model="editingCopy" editor-id="social-copy-editor" label="Redacción editable" :height="320" placeholder="Edita la redacción generada..." /></div>
                            <div class="mt-4"><flux:textarea wire:model="editingHashtags" label="Hashtags" rows="3" /></div>
                            <div class="mt-4 flex flex-wrap justify-end gap-2"><flux:button type="button" variant="filled" wire:click="guardarEdicion">Guardar cambios</flux:button></div>
                        @else<p class="text-center text-slate-500">Selecciona una variante.</p>@endif
                    </div>
                </div>

                @error('authorization')<p class="mt-4 rounded-2xl bg-rose-50 p-4 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                @error('versions')<p class="mt-4 rounded-2xl bg-rose-50 p-4 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><flux:button type="button" variant="ghost" wire:click="nuevaGeneracion">Nueva generación</flux:button><flux:button type="button" variant="primary" wire:click="crearBorradoresPublicaciones" wire:loading.attr="disabled">Enviar versiones seleccionadas a Publicaciones</flux:button></div>
            @endif
        </section>
    @endif
</div>
