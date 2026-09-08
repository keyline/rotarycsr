<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#17458F] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#123669] focus:bg-[#123669] active:bg-[#0d2a52] focus:outline-none focus:ring-2 focus:ring-[#F7A81B] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
