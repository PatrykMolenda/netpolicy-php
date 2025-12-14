<?php

namespace PatrykMolenda\NetPolicy\Tests\Unit\Network;

use PatrykMolenda\NetPolicy\Network\Protocol;
use PHPUnit\Framework\TestCase;

class ProtocolTest extends TestCase
{
    public function testEnumCases(): void
    {
        $this->assertEquals('BGP', Protocol::BGP->value);
        $this->assertEquals('OSPF', Protocol::OSPF->value);
        $this->assertEquals('STATIC', Protocol::STATIC->value);
    }

    public function testEqualsReturnsTrueForSameProtocol(): void
    {
        $this->assertTrue(Protocol::BGP->equals(Protocol::BGP));
    }

    public function testEqualsReturnsFalseForDifferentProtocol(): void
    {
        $this->assertFalse(Protocol::BGP->equals(Protocol::OSPF));
    }

    public function testFromString(): void
    {
        $protocol = Protocol::from('BGP');
        $this->assertSame(Protocol::BGP, $protocol);
    }
}

