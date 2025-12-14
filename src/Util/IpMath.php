<?php

namespace PatrykMolenda\NetPolicy\Util;

final class IpMath
{
    /**
     * @param string $ip
     * @return int
     */
    public static function ipToInteger(string $ip): int
    {
        return ip2long($ip);
    }

    /**
     * @param int $mask
     * @return int
     */
    public static function maskToInteger(int $mask): int
    {
        return ~((1 << (32 - $mask)) - 1);
    }
}