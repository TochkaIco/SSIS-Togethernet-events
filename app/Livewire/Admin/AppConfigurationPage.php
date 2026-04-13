<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\AppConfig;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class AppConfigurationPage extends Component
{
    public bool $allowExternal;

    public function mount(): void
    {
        $this->allowExternal = AppConfig::get('allow_external_emails', false);
    }

    public function updatedAllowExternal($value): void
    {
        AppConfig::where('key', 'allow_external_emails')
            ->update(['value' => $value ? 'true' : 'false']);

        Flux::toast('Setting saved.');
    }

    #[Layout('layouts.app', ['title' => 'Config'])]
    public function render(): View
    {
        return view('livewire.admin.app-configuration');
    }
}
