<?php

namespace PatrykMolenda\NetPolicy\Network;

enum Protocol: string
{
    case BGP = 'BGP';
    case OSPF = 'OSPF';
    case STATIC = 'STATIC';

    /**
     * Check if this protocol equals another
     *
     * @param Protocol $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this === $other;
    }
}