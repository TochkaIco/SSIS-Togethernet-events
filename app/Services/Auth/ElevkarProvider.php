<?php

declare(strict_types=1);

namespace App\Services\Auth;

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
        $response = $this->getHttpClient()->get($this->getBaseUrl().'/api/auth/oauth2/userinfo', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'Accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getAccessTokenResponse($code)
    {
        $fields = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'code_verifier' => $this->request->session()->pull('code_verifier'),
            'redirect_uri' => $this->redirectUrl,
            'client_id' => $this->clientId,
        ];

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ],
            'form_params' => $fields,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['sub'] ?? null,
            'name' => $user['name'] ?? null,
            'email' => $user['email'] ?? null,
            'user_class' => $user['user_class'] ?? $user['class'] ?? null,
        ]);
    }
}
