<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\AppConfig;
use App\Models\GlobalLog;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class AppConfigurationPage extends Component
{
    public bool $useElevkarAuth;

    public bool $allowExternal;

    public bool $automatedWaitingListMove;

    public function mount(): void
    {
        $this->useElevkarAuth = AppConfig::get('active_auth_provider', 'google') === 'elevkar';
        $this->allowExternal = AppConfig::get('allow_external_emails', false);
        $this->automatedWaitingListMove = AppConfig::get('automated_waiting_list_move', true);
    }

    public function updatedUseElevkarAuth($value): void
    {
        AppConfig::updateOrCreate(['key' => 'active_auth_provider'], ['value' => $value ? 'elevkar' : 'google', 'type' => 'string']);

        GlobalLog::log('App Configuration updated', 'config', ['key' => 'active_auth_provider', 'value' => $value ? 'elevkar' : 'google']);

        Flux::toast(__('Setting saved.'), variant: 'success');
    }

    public function updatedAllowExternal($value): void
    {
        AppConfig::updateOrCreate(['key' => 'allow_external_emails'], ['value' => $value ? 'true' : 'false', 'type' => 'boolean']);

        GlobalLog::log('App Configuration Updated', 'config', ['key' => 'allow_external_emails', 'value' => $value ? 'true' : 'false']);

        Flux::toast(__('Setting saved.'), variant: 'success');
    }

    public function updatedAutomatedWaitingListMove($value): void
    {
        AppConfig::updateOrCreate(['key' => 'automated_waiting_list_move'], ['value' => $value ? 'true' : 'false', 'type' => 'boolean']);

        GlobalLog::log('App Configuration Updated', 'config', ['key' => 'automated_waiting_list_move', 'value' => $value ? 'true' : 'false']);

        Flux::toast(__('Setting saved.'), variant: 'success');
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.admin.app-configuration')->title(__('App Configuration'));
    }
}
