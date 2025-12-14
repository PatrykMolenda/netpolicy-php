<?php

namespace PatrykMolenda\NetPolicy\Network;

final class AsNumber
{
    /**
     * @param int $asn
     */
    public function __construct(protected int $asn)
    {
    }

    /**
     * @return int
     */
    public function value(): int
    {
        return $this->asn;
    }

    /**
     * @param AsNumber $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->asn === $other->asn;
    }
}