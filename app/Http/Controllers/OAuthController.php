<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AppConfig;
use App\Models\User;
use App\Services\LdapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class OAuthController extends Controller
{
    public function __construct(
        private readonly LdapService $ldapService
    ) {}

    public function redirect(): RedirectResponse
    {
        $provider = AppConfig::get('active_auth_provider', 'google');

        if ($provider === 'elevkar') {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver('elevkar');

            return $driver->redirect();
        }

        /** @var AbstractProvider $driver */
        $driver = Socialite::driver('google');

        return $driver->with(['prompt' => 'select_account'])->redirect();
    }

    public function callback(): RedirectResponse
    {
        $provider = AppConfig::get('active_auth_provider', 'google');

        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver($provider);

            if ($provider === 'elevkar') {
                $driver->stateless();
            }

            /** @var \Laravel\Socialite\Two\User $user */
            $user = $driver->user();

            return $this->handleProviderCallback($user, $provider);
        } catch (\Exception $e) {
            Log::error("OAuth Callback Error ({$provider}): ".$e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'provider' => $provider,
            ]);

            return redirect('/login')->with('error', "Failed to fetch your data from {$provider}.");
        }
    }

    private function handleProviderCallback(SocialiteUserContract $oauthUser, string $provider): RedirectResponse
    {
        /** @var \Laravel\Socialite\Two\User $oauthUser */
        if ($provider === 'elevkar') {
            $name = $oauthUser->getName() ?? 'Unknown';
            $class = $oauthUser->user['user_class'] ?? $oauthUser->user['class'] ?? 'Unknown';
            $avatar = $oauthUser->getAvatar();
        } else {
            $ldapData = $this->ldapService->fetchUserData($oauthUser->getEmail());
            $name = $ldapData['name'];
            $class = $ldapData['class'];
            $avatar = $oauthUser->getAvatar();

            $this->validateGoogleHd($oauthUser);
        }

        $isNewUser = (! User::where('email', $oauthUser->getEmail())->exists());

        $user = User::updateOrCreate(
            ['email' => $oauthUser->getEmail()],
            [
                'name' => $name,
                'class' => $class,
                'profile_picture' => $avatar,
                "{$provider}_id" => $oauthUser->getId(),
                "{$provider}_token" => $oauthUser->token,
                "{$provider}_refresh_token" => $oauthUser->refreshToken,
            ]
        );

        Auth::login($user);

        if ($isNewUser) {
            return redirect(route('terms.accept'));
        } else {
            return redirect('/');
        }
    }

    private function validateGoogleHd(SocialiteUserContract $oauthUser): void
    {
        /** @var \Laravel\Socialite\Two\User $oauthUser */
        $hd = config('services.google.hd');

        if (! AppConfig::get('allow_external_emails', false) &&
            ! empty($hd) &&
            data_get($oauthUser->user, 'hd') !== $hd &&
            ! User::where('email', $oauthUser->getEmail())->exists()) {
            abort(403);
        }
    }
}
