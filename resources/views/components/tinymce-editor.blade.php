@props([
    'model',
    'editorId' => 'tinymce-editor',
    'label' => 'Descripción',
    'badge' => null,
    'description' => null,
    'placeholder' => '',
    'height' => 260,
])

<div class="space-y-2" wire:key="tinymce-field-{{ $editorId }}">
    <div class="flex flex-wrap items-center gap-2">
        <label for="{{ $editorId }}"
            class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
            {{ $label }}
        </label>

        @if($badge)
            <span
                class="rounded-md bg-zinc-100 px-1.5 py-0.5 text-[11px] font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                {{ $badge }}
            </span>
        @endif
    </div>

    @if($description)
        <p class="text-sm leading-5 text-zinc-500 dark:text-zinc-400">
            {{ $description }}
        </p>
    @endif

    <div wire:ignore
        x-data="miniSystemsTinyMce({
            editorId: @js($editorId),
            height: {{ (int) $height }},
            placeholder: @js($placeholder),
        })"
        x-init="init()"
        x-on:reconocimiento-descripcion-actualizada.window="setContent($event.detail.html ?? '')"
        class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition focus-within:border-[#006492] focus-within:ring-2 focus-within:ring-[#006492]/15 dark:border-zinc-700 dark:bg-zinc-900">
        <textarea id="{{ $editorId }}" x-ref="textarea"
            wire:model.live.debounce.400ms="{{ $model }}"
            class="min-h-56 w-full resize-y border-0 bg-transparent p-3 text-sm text-zinc-900 outline-none dark:text-zinc-100"
            placeholder="{{ $placeholder }}"></textarea>
    </div>

    @error($model)
        <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
