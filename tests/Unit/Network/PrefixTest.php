<?php

namespace PatrykMolenda\NetPolicy\Tests\Unit\Network;

use PatrykMolenda\NetPolicy\Network\Prefix;
use PHPUnit\Framework\TestCase;

class PrefixTest extends TestCase
{
    public function testConstructorWithValidIpv4(): void
    {
        $prefix = new Prefix('192.168.0.0/16');
        $this->assertEquals('192.168.0.0/16', $prefix->cidr());
    }

    public function testConstructorWithValidIpv6(): void
    {
        $prefix = new Prefix('2001:db8::/32');
        $this->assertEquals('2001:db8::/32', $prefix->cidr());
    }

    public function testConstructorThrowsExceptionForInvalidCidr(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid CIDR notation');
        new Prefix('not-a-valid-cidr');
    }

    public function testContainsReturnsTrueWhenPrefixContainsAnother(): void
    {
        $prefix1 = new Prefix('192.168.0.0/16');
        $prefix2 = new Prefix('192.168.1.0/24');

        $this->assertTrue($prefix1->contains($prefix2));
    }

    public function testContainsReturnsFalseWhenPrefixDoesNotContainAnother(): void
    {
        $prefix1 = new Prefix('192.168.0.0/16');
        $prefix2 = new Prefix('10.0.0.0/8');

        $this->assertFalse($prefix1->contains($prefix2));
    }

    public function testContainsReturnsFalseForDifferentFamilies(): void
    {
        $ipv4 = new Prefix('192.168.0.0/16');
        $ipv6 = new Prefix('2001:db8::/32');

        $this->assertFalse($ipv4->contains($ipv6));
    }

    public function testOverlapsReturnsTrueWhenPrefixesOverlap(): void
    {
        $prefix1 = new Prefix('192.168.0.0/16');
        $prefix2 = new Prefix('192.168.1.0/24');

        $this->assertTrue($prefix1->overlaps($prefix2));
        $this->assertTrue($prefix2->overlaps($prefix1));
    }

    public function testOverlapsReturnsFalseWhenPrefixesDoNotOverlap(): void
    {
        $prefix1 = new Prefix('192.168.0.0/16');
        $prefix2 = new Prefix('10.0.0.0/8');

        $this->assertFalse($prefix1->overlaps($prefix2));
    }

    public function testOverlapsReturnsTrueForIdenticalPrefixes(): void
    {
        $prefix1 = new Prefix('172.16.0.0/12');
        $prefix2 = new Prefix('172.16.0.0/12');

        $this->assertTrue($prefix1->overlaps($prefix2));
    }

    public function testIsInPrefixDelegatesToContains(): void
    {
        $prefix1 = new Prefix('192.168.0.0/16');
        $prefix2 = new Prefix('192.168.1.0/24');

        $this->assertTrue($prefix1->isInPrefix($prefix2));
    }

    public function testIpv6Operations(): void
    {
        $prefix1 = new Prefix('2001:db8::/32');
        $prefix2 = new Prefix('2001:db8:1::/48');

        $this->assertTrue($prefix1->contains($prefix2));
        $this->assertTrue($prefix1->overlaps($prefix2));
    }
}

