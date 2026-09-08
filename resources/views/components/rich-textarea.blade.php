@php
    $minHeight = ((int) $rows) * 24;
@endphp

<div data-rich-editor {{ $attributes->merge(['class' => 'border border-gray-300 rounded-md overflow-hidden focus-within:border-[#17458F] focus-within:ring-1 focus-within:ring-[#17458F] transition']) }}>
    <div class="flex items-center gap-0.5 px-2 py-1 border-b border-gray-200 bg-gray-50">
        <button type="button" data-cmd="bold" title="Bold" class="h-6 w-6 flex items-center justify-center rounded text-xs font-bold text-gray-600 hover:bg-gray-200">B</button>
        <button type="button" data-cmd="italic" title="Italic" class="h-6 w-6 flex items-center justify-center rounded text-xs italic text-gray-600 hover:bg-gray-200">I</button>
        <span class="w-px h-4 bg-gray-300 mx-1"></span>
        <button type="button" data-cmd="insertUnorderedList" title="Bullet list" class="h-6 w-6 flex items-center justify-center rounded text-gray-600 hover:bg-gray-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
            </svg>
        </button>
    </div>

    <div data-rich-editor-input contenteditable="true"
         class="px-3 py-2 text-sm text-gray-900 focus:outline-none [&_ul]:list-disc [&_ul]:pl-5"
         style="min-height: {{ $minHeight }}px">{!! $value !!}</div>

    <textarea name="{{ $name }}" id="{{ $id }}" class="hidden">{{ $value }}</textarea>
</div>
