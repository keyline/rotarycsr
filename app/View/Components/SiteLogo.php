<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class SiteLogo extends Component
{
    public string $url;

    public function __construct()
    {
        $this->url = asset('images/logo.png');
    }

    public function render(): View
    {
        return view('components.site-logo');
    }
}
