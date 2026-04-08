<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AppConfig;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;

class OAuthController extends Controller
{
    public function store()
    {
        $allowExternal = AppConfig::get('allow_external_emails', false);
        try {
            /** @var GoogleUser $googleUser */
            $googleUser = Socialite::driver('google')->user();
            if (! $allowExternal && (! empty(config('services.google.hd')) && data_get($googleUser->user, 'hd') !== config('services.google.hd'))) {
                abort(403);
            }
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Failed to connect to fetch your data from Google, likely because you used an email not affiliated with SSIS');
        }
        $user = User::firstOrCreate(
            ['email' => $googleUser->email],
            [
                'name' => $googleUser->name,
                'google_id' => $googleUser->id,
                'profile_picture' => $googleUser->getAvatar(),
            ]
        );

        $user->update([
            'google_token' => $googleUser->token,
            'google_refresh_token' => $googleUser->refreshToken,
        ]);
        if (! $user->profile_picture) {
            $user->update(['profile_picture' => $googleUser->getAvatar()]);
        }

        Auth::login($user);

        return redirect('/');
    }
}
