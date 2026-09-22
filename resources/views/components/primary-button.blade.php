<button {{ $attributes->merge(['type' => 'submit', 'class' => 'award-button border border-transparent uppercase tracking-widest']) }}>
    {{ $slot }}
</button>
