<?php

declare(strict_types=1);

namespace App\Services\Auth;

use GuzzleHttp\Exception\GuzzleException;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class ElevkarProvider extends AbstractProvider implements ProviderInterface
{
    protected $usesPKCE = true;

    protected $scopes = ['openid', 'profile', 'email'];

    protected $scopeSeparator = ' ';

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://elevkar-auth.ssis.nu/api/auth/oauth2/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return 'https://elevkar-auth.ssis.nu/api/auth/oauth2/token';
    }

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://elevkar-auth.ssis.nu/api/auth/oauth2/userinfo', [
            'headers' => ['Authorization' => 'Bearer '.$token],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @throws GuzzleException
     */
    public function getAccessTokenResponse($code)
    {
        $fields = $this->getTokenFields($code);

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => [
                'Accept' => 'application/json',
                'Origin' => 'https://elevkar-auth.ssis.nu',
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
            'class' => $user['class'] ?? null,
        ]);
    }
}
