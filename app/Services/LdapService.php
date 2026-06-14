<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GlobalLog;
use Illuminate\Support\Facades\Log;
use LdapRecord\Connection;

class LdapService
{
    public function fetchUserData(string $email): array
    {
        $data = ['name' => 'Unknown', 'class' => 'Unknown'];

        try {
            $usertag = str($email)->before('@')->toString();
            $connection = new Connection(config('ldap.connections.default'));
            $connection->auth()->attempt(
                config('ldap.connections.default.username'),
                config('ldap.connections.default.password'),
                true
            );

            $ldapUser = $connection->query()
                ->in(config('ldap.connections.default.base_dn'))
                ->where('sAMAccountName', '=', $usertag)
                ->first();

            if ($ldapUser) {
                $data['name'] = ($ldapUser['givenname'][0] ?? '').' '.($ldapUser['sn'][0] ?? '');
                $data['class'] = str_contains($ldapUser['dn'], 'OU=Personal')
                    ? 'Personal'
                    : $this->parseClassFromGroups($ldapUser['memberof'] ?? []);
            }
        } catch (\Exception $e) {
            Log::error('LDAP error: '.$e->getMessage());
            GlobalLog::log('LDAP error encountered', 'system', ['error_message' => $e->getMessage()]);
        }

        return $data;
    }

    private function parseClassFromGroups(array $memberOf): string
    {
        foreach (array_filter($memberOf, 'is_string') as $group) {
            if (str_contains($group, 'OU=Klass')) {
                preg_match('/^CN=([^,]+)/', $group, $matches);

                return $matches[1] ?? 'Unknown';
            }
        }

        return 'Unknown';
    }
}
