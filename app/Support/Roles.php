<?php

namespace App\Support;

/**
 * Rôles applicatifs. Les valeurs correspondent à celles déjà stockées dans users.role.
 */
final class Roles
{
    public const ADMIN = 'Admin';
    public const SENIOR = 'Technicien senior';
    public const TECHNICIAN = 'Technicien';

    /** Rôles attribuables par invitation : jamais Admin. @return list<string> */
    public static function invitable(): array
    {
        return [self::TECHNICIAN, self::SENIOR];
    }

    /** @return list<string> */
    public static function all(): array
    {
        return [self::ADMIN, self::SENIOR, self::TECHNICIAN];
    }
}
