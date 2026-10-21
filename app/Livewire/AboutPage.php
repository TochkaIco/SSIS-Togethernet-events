<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class AboutPage extends Component
{
    public function render(): View
    {
        return view('livewire.about-page')->layout('layouts.app', ['title' => __('About Us')]);
    }
}
