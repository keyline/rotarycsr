<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class RichTextarea extends Component
{
    public function __construct(
        public string $name,
        public ?string $id = null,
        public ?string $value = null,
        public string $rows = '4',
    ) {
        $this->id ??= $name;
    }

    public function render(): View
    {
        return view('components.rich-textarea');
    }
}
