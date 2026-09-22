@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border-slate-300 bg-white/90 shadow-sm focus:border-rotary-gold focus:ring-rotary-gold']) }}>
