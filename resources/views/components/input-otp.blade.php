@props([
    'digits' => 6,
    'name' => 'code',
])

<flux:otp
    {{ $attributes->except(['digits']) }}
    :length="(int) $digits"
    :name="$name"
    class="mx-auto"
/>
