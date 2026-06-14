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
        $baseUrl = $this->getBaseUrl();

        $response = Http::withToken($token)
            ->withHeaders([
                'Origin' => $baseUrl,
                'Referer' => $baseUrl.'/',
                'Accept' => '*/*',
            ])
            ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36')
            ->get($baseUrl.'/api/auth/oauth2/userinfo');

        if ($response->failed()) {
            throw new \RuntimeException("Failed to retrieve user info: {$response->body()} (Status: {$response->status()})");
        }

        return $response->json();
    }

    public function getAccessTokenResponse($code)
    {
        if (! $this->request->hasSession()) {
            throw new \RuntimeException('Session store not set on request. Ensure the callback route is in the "web" middleware group.');
        }

        $verifier = $this->request->session()->get('code_verifier');

        if (! $verifier) {
            throw new \RuntimeException('PKCE code_verifier missing from session. If this only happens in production, check your SESSION_DOMAIN, SESSION_SECURE_COOKIE, and TRUSTED_PROXIES settings.');
        }

        $fields = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'code_verifier' => $verifier,
            'redirect_uri' => $this->redirectUrl,
            'client_id' => config('services.elevkar.client_id'),
            'client_secret' => config('services.elevkar.client_secret'),
        ];

        $url = $this->getTokenUrl();
        $baseUrl = $this->getBaseUrl();

        $response = Http::asForm()
            ->withHeaders([
                'Origin' => $baseUrl,
                'Referer' => $baseUrl.'/',
                'Accept' => '*/*',
            ])
            ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36')
            ->post($url, $fields);

        if ($response->failed()) {
            throw new \RuntimeException("Failed to retrieve access token from {$url}: {$response->body()} (Status: {$response->status()})");
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
