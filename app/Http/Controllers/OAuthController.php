<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;

class OAuthController extends Controller
{
    //
    public function store()
    {
        try {
            /** @var GoogleUser $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'failed to connect to fetch your data from Google');
        }
        $user = User::updateOrCreate([
            'email' => $googleUser->email,
        ], [
            'name' => $googleUser->name,
            'email' => $googleUser->email,
        ]);

        Auth::login($user);

        return redirect('/');
    }
}
