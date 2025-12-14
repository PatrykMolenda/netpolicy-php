<?php

namespace PatrykMolenda\NetPolicy\Network;

final class Prefix
{
    protected string $network;
    protected string $broadcast;
    protected int $family;

    /**
     * @param string $cidr
     */
    public function __construct(protected string $cidr) {
        @[$ip, $mask] = @explode('/', $cidr);

        $bin = inet_pton($ip);
        if($bin === false) {
            throw new \InvalidArgumentException("Invalid CIDR notation: $cidr");
        }

        $this->family = strlen($bin);
        $mask = (int)$mask;

        $this->network = self::applyMask($bin, $mask, $this->family);
        $this->broadcast = self::applyHostMask($this->network, $mask, $this->family);
    }

    /**
     * @param string $bin
     * @param int $mask
     * @param int $bytes
     * @return string
     */
    protected static function applyMask(string $bin, int $mask, int $bytes): string
    {
        $bits = $bytes * 8;
        $maskBits = str_repeat("1", $mask) . str_repeat("0", $bits - $mask);

        return $bin & self::bitsToBinary($maskBits);
    }

    /**
     * @param string $bin
     * @param int $mask
     * @param int $bytes
     * @return string
     */
    protected static function applyHostMask(string $bin, int $mask, int $bytes): string
    {
        $bits = $bytes * 8;
        $hostBits = str_repeat("0", $mask) . str_repeat("1", $bits - $mask);

        return $bin | self::bitsToBinary($hostBits);
    }

    /**
     * @return string
     */
    public function cidr(): string
    {
        return $this->cidr;
    }

    /**
     * Check if an IP address (as Prefix) is within this prefix
     *
     * @param Prefix $ip
     * @return bool
     */
    public function isInPrefix(self $ip): bool
    {
        return $this->contains($ip);
    }

    /**
     * @param Prefix $other
     * @return bool
     */
    public function contains(self $other): bool
    {
        // Check if families match
        if ($this->family !== $other->family) {
            return false;
        }

        // This prefix contains another if:
        // - The other's network address is >= this network address
        // - The other's broadcast address is <= this broadcast address
        return $this->network <= $other->network && $this->broadcast >= $other->broadcast;
    }

    /**
     * @param Prefix $other
     * @return bool
     */
    public function overlaps(self $other): bool
    {
        // Check if families match
        if ($this->family !== $other->family) {
            return false;
        }

        // Two prefixes overlap if:
        // - This network is <= other's broadcast AND
        // - This broadcast is >= other's network
        return $this->network <= $other->broadcast && $this->broadcast >= $other->network;
    }

    /**
     * Convert binary string representation to packed binary format
     *
     * @param string $bits
     * @return string
     */
    protected static function bitsToBinary(string $bits): string
    {
        $bytes = str_split($bits, 8);
        $result = '';
        foreach ($bytes as $byte) {
            $result .= chr(bindec($byte));
        }
        return $result;
    }
}