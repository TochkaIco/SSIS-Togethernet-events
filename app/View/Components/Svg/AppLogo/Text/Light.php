<?php

declare(strict_types=1);

namespace App\View\Components\Svg\AppLogo\Text;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Light extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.svg.app-logo.text.light');
    }
}
