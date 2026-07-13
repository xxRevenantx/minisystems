@props([
    'model',
    'editorId' => 'tinymce-editor',
    'label' => 'Descripción',
    'badge' => null,
    'description' => null,
    'placeholder' => '',
    'height' => 260,
])

<flux:field class="space-y-2" wire:key="tinymce-field-{{ $editorId }}">
    <div class="flex flex-wrap items-center gap-2">
        <flux:label for="{{ $editorId }}">{{ $label }}</flux:label>

        @if($badge)
            <flux:badge size="sm" color="zinc">{{ $badge }}</flux:badge>
        @endif
    </div>

    @if($description)
        <flux:description>{{ $description }}</flux:description>
    @endif

    <div
        wire:ignore
        x-data="miniSystemsTinyMce({
            editorId: @js($editorId),
            height: {{ (int) $height }},
            placeholder: @js($placeholder),
        })"
        x-init="init()"
        x-on:reconocimiento-descripcion-actualizada.window="setContent($event.detail.html ?? '')"
        class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition focus-within:border-[#006492] focus-within:ring-2 focus-within:ring-[#006492]/15 dark:border-zinc-700 dark:bg-zinc-900"
    >
        <flux:textarea
            id="{{ $editorId }}"
            x-ref="textarea"
            wire:model.live.debounce.400ms="{{ $model }}"
            class="min-h-56 w-full resize-y border-0! bg-transparent! text-sm"
            placeholder="{{ $placeholder }}"
        />
    </div>

    <flux:error name="{{ $model }}" />
</flux:field>
