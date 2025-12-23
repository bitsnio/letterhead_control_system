<?php

namespace App\CustomClasses;

use Illuminate\Support\Facades\Log;

class LdapService
{
    public static function AuthenticateUser(string $username, string $password): bool
    {

        if (empty($username) || empty($password)) {
            return false;
        }

        $ldapHost = 'ldap://172.26.14.162:389';
        $ldapPort = 389;
        $domain   = 'cmpak\\';

        $connection = @ldap_connect($ldapHost);


        if (! $connection) {

            return false;
        }

        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);


        return @ldap_bind(
            $connection,
            $domain . $username,
            $password
        );
    }
}
