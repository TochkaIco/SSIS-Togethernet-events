<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use Flux\Flux;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Appearance settings')]
class Appearance extends Component
{
    public string $locale;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->locale = Auth::user()->locale ?? App::getLocale();
    }

    /**
     * Set the application locale.
     */
    public function updateLocale(string $locale): void
    {
        if (! in_array($locale, ['en', 'sv'])) {
            return;
        }

        $this->locale = $locale;

        $user = Auth::user();

        if ($user) {
            $user->update([
                'locale' => $locale,
            ]);
        }

        session()->put('locale', $locale);
        App::setLocale($locale);

        $this->dispatch('locale-updated', $locale);

        Flux::toast(text: __('Locale updated, but you may need to refresh the page to see the changes.'), variant: 'success');
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.settings.appearance');
    }
}
