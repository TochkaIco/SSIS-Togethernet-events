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

    public bool $automatedWaitingListMove;

    public function mount(): void
    {
        $this->allowExternal = AppConfig::get('allow_external_emails', false);
        $this->automatedWaitingListMove = AppConfig::get('automated_waiting_list_move', true);
    }

    public function updatedAllowExternal($value): void
    {
        AppConfig::updateOrCreate(['key' => 'allow_external_emails'], ['value' => $value ? 'true' : 'false', 'type' => 'boolean']);

        Flux::toast(__('Setting saved.'), variant: 'success');
    }

    public function updatedAutomatedWaitingListMove($value): void
    {
        AppConfig::updateOrCreate(['key' => 'automated_waiting_list_move'], ['value' => $value ? 'true' : 'false', 'type' => 'boolean']);

        Flux::toast(__('Setting saved.'), variant: 'success');
    }

    #[Layout('layouts.app', ['title' => 'Config'])]
    public function render(): View
    {
        return view('livewire.admin.app-configuration');
    }
}
