<?php

namespace App\View\Components;

use App\Models\Setting;
use Illuminate\View\Component;
use Illuminate\View\View;

class SiteLogo extends Component
{
    public string $url;

    public function __construct()
    {
        $path = Setting::get('site_logo_path');

        $this->url = $path ? asset('storage/'.$path) : asset('images/rotary-logo.svg');
    }

    public function render(): View
    {
        return view('components.site-logo');
    }
}
