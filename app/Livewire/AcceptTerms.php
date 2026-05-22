<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\GlobalLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AcceptTerms extends Component
{
    public string $terms = '';

    /**
     * Mount the component.
     */
    public function mount(): mixed
    {
        if (Auth::user()?->tos_accepted_at) {
            return redirect()->intended('/');
        }

        $path = resource_path('views/terms.md');
        if (File::exists($path)) {
            $this->terms = Str::markdown(File::get($path));
        } else {
            $this->terms = 'Terms of Service not found.';
        }

        return null;
    }

    public function accept(): void
    {
        Auth::user()->update([
            'tos_accepted_at' => now(),
        ]);

        GlobalLog::log('User accepted TOS', 'user', ['user_id' => Auth::id()]);

        $this->redirectIntended('/');
    }

    public function decline(): void
    {
        $user = Auth::user();

        Auth::logout();

        $user->anonymize();

        GlobalLog::log('User declined TOS and had been anonymized', 'user', ['user_id' => Auth::id()]);

        $this->redirect('/');
    }

    public function render()
    {
        return view('livewire.accept-terms')
            ->title(__('Accept Terms of Service'));
    }
}
