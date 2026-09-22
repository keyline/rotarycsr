@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-bold text-rotary-navy']) }}>
    {{ $value ?? $slot }}
</label>
