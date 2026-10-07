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

    /** @return list<string> */
    public static function all(): array
    {
        return [self::ADMIN, self::SENIOR, self::TECHNICIAN];
    }
}
