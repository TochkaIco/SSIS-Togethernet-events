<?php

declare(strict_types=1);

namespace App\Services\Auth;

use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class ElevkarProvider extends AbstractProvider implements ProviderInterface
{
    public $config;

    protected $usesPKCE = true;

    protected $scopes = ['openid', 'profile', 'email'];

    protected $scopeSeparator = ' ';

    protected function getBaseUrl(): string
    {
        return rtrim($this->config['base_url'] ?? 'https://elevkar-auth.ssis.nu', '/');
    }

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase($this->getBaseUrl().'/api/auth/oauth2/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return $this->getBaseUrl().'/api/auth/oauth2/token';
    }

    protected function getUserByToken($token)
    {
        $response = Http::withToken($token)
            ->get($this->getBaseUrl().'/api/auth/oauth2/userinfo');

        if ($response->failed()) {
            throw new \RuntimeException("Failed to retrieve user info: {$response->body()} (Status: {$response->status()})");
        }

        return $response->json();
    }

    public function getAccessTokenResponse($code)
    {
        $verifier = $this->request->session()->get('code_verifier');

        if (! $verifier) {
            throw new \RuntimeException('PKCE code_verifier missing from session. Ensure session is working and not cleared.');
        }

        $fields = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'code_verifier' => $verifier,
            'redirect_uri' => $this->redirectUrl,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];

        $response = Http::asForm()
            ->withHeaders([
                'Origin' => $this->getBaseUrl(),
            ])
            ->post($this->getTokenUrl(), $fields);

        if ($response->failed()) {
            throw new \RuntimeException("Failed to retrieve access token: {$response->body()} (Status: {$response->status()})");
        }

        return $response->json();
    }

    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['sub'] ?? $user['id'] ?? null,
            'name' => $user['name'] ?? null,
            'email' => $user['email'] ?? null,
            'user_class' => $user['user_class'] ?? $user['class'] ?? null,
            'avatar' => $user['picture'] ?? $user['avatar'] ?? null,
        ]);
    }
}
