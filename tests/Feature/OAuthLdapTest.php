<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use LdapRecord\Connection;
use LdapRecord\Container;
use LdapRecord\Models\Entry;
use LdapRecord\Query\Builder;
use Mockery\Expectation;
use Mockery\MockInterface;

test('it fetches name and class from ldap after google login', function () {
    config(['services.google.hd' => 'example.com']);

    /** @var SocialiteUser&MockInterface $socialiteUser */
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->id = '12345';
    $socialiteUser->name = 'Google Name';
    $socialiteUser->email = 'usertag@example.com';
    $socialiteUser->token = 'token';
    $socialiteUser->refreshToken = 'refresh_token';
    $socialiteUser->user = ['hd' => 'example.com'];
    $socialiteUser->shouldReceive('getAvatar')->andReturn('http://avatar.com');

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $ldapUser = new Entry([
        'displayname' => ['LDAP Name'],
        'description' => ['TE25A'],
    ]);

    /** @var Builder&MockInterface $query */
    $query = Mockery::mock(Builder::class);
    /** @var Expectation $expectation */
    $expectation = $query->shouldReceive('where');
    $expectation->andReturnSelf();
    $query->shouldReceive('first')->andReturn($ldapUser);

    /** @var Connection&MockInterface $connection */
    $connection = Mockery::mock(Connection::class);
    $connection->shouldReceive('setDispatcher');
    $connection->shouldReceive('query')->andReturn($query);

    Container::addConnection($connection);

    $response = $this->get(route('login.oauth'));

    $response->assertRedirect('/');

    $user = User::where('email', 'usertag@example.com')->first();
    expect($user->name)->toBe('LDAP Name');
    expect($user->class)->toBe('TE25A');
});

test('it falls back to google name and unknown class if ldap fetch fails', function () {
    config(['services.google.hd' => 'example.com']);

    /** @var SocialiteUser&MockInterface $socialiteUser */
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->id = '12345';
    $socialiteUser->name = 'Google Name';
    $socialiteUser->email = 'usertag@example.com';
    $socialiteUser->token = 'token';
    $socialiteUser->refreshToken = 'refresh_token';
    $socialiteUser->user = ['hd' => 'example.com'];
    $socialiteUser->shouldReceive('getAvatar')->andReturn('http://avatar.com');

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    /** @var Builder&MockInterface $query */
    $query = Mockery::mock(Builder::class);
    /** @var Expectation $expectation */
    $expectation = $query->shouldReceive('where');
    $expectation->andReturnSelf();
    $query->shouldReceive('first')->andReturn(null);

    /** @var Connection&MockInterface $connection */
    $connection = Mockery::mock(Connection::class);
    $connection->shouldReceive('setDispatcher');
    $connection->shouldReceive('query')->andReturn($query);

    Container::addConnection($connection);

    $response = $this->get(route('login.oauth'));

    $response->assertRedirect('/');

    $user = User::where('email', 'usertag@example.com')->first();
    expect($user->name)->toBe('Google Name');
    expect($user->class)->toBe('Unknown');
});

test('it handles ldap exception gracefully', function () {
    config(['services.google.hd' => 'example.com']);

    /** @var SocialiteUser&MockInterface $socialiteUser */
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->id = '12345';
    $socialiteUser->name = 'Google Name';
    $socialiteUser->email = 'usertag@example.com';
    $socialiteUser->token = 'token';
    $socialiteUser->refreshToken = 'refresh_token';
    $socialiteUser->user = ['hd' => 'example.com'];
    $socialiteUser->shouldReceive('getAvatar')->andReturn('http://avatar.com');

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    /** @var Connection&MockInterface $connection */
    $connection = Mockery::mock(Connection::class);
    $connection->shouldReceive('setDispatcher');
    /** @var Expectation $expectation */
    $expectation = $connection->shouldReceive('query');
    $expectation->andThrow(new Exception('LDAP Connection Error'));

    Container::addConnection($connection);

    $response = $this->get(route('login.oauth'));

    $response->assertRedirect('/');

    $user = User::where('email', 'usertag@example.com')->first();
    expect($user->name)->toBe('Google Name');
    expect($user->class)->toBe('Unknown');
});
