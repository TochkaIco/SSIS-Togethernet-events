<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AppConfig;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use LdapRecord\Connection;

class OAuthController extends Controller
{
    public function store()
    {
        $allowExternal = AppConfig::get('allow_external_emails', false);
        try {
            /** @var GoogleUser $googleUser */
            $googleUser = Socialite::driver('google')->user();
            if (! $allowExternal && (! empty(config('services.google.hd')) && data_get($googleUser->user, 'hd') !== config('services.google.hd')) && (! User::where('email', $googleUser->getEmail())->first())) {
                abort(403);
            }
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Failed to fetch your data from Google, likely because you used an email not affiliated with SSIS');
        }

        $ldapName = $googleUser->name;
        $ldapClass = 'Unknown';

        try {
            $usertag = str($googleUser->email)->before('@')->toString();

            $connection = new Connection([
                'hosts' => config('ldap.connections.default.hosts'),
                'port' => config('ldap.connections.default.port'),
                'use_ssl' => config('ldap.connections.default.use_ssl'),
                'use_tls' => config('ldap.connections.default.use_tls'),
                'base_dn' => config('ldap.connections.default.base_dn'),
            ]);

            $connection->auth()->attempt(
                config('ldap.connections.default.username'),
                config('ldap.connections.default.password'),
                stayBound: true
            );

            $ldapUser = $connection->query()
                ->in(config('ldap.connections.default.base_dn'))
                ->where('sAMAccountName', '=', $usertag)
                ->first();

            if ($ldapUser) {
                $givenName = $ldapUser['givenname'][0] ?? null;
                $sn = $ldapUser['sn'][0] ?? null;

                if ($givenName && $sn) {
                    $ldapName = "$givenName $sn";
                }
                if (str_contains($ldapUser['dn'], 'OU=Personal')) {
                    $ldapClass = 'Personal';
                } else {
                    $memberOf = array_filter($ldapUser['memberof'] ?? [], 'is_string');
                    foreach ($memberOf as $group) {
                        if (str_contains($group, 'OU=Elever')) {
                            preg_match('/^CN=([^,]+)/', $group, $matches);
                            $ldapClass = $matches[1] ?? 'Unknown';
                            break;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('LDAP error: '.$e->getMessage());
        }

        $user = User::firstOrCreate(
            ['email' => $googleUser->email],
            [
                'name' => $ldapName,
                'class' => $ldapClass,
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
