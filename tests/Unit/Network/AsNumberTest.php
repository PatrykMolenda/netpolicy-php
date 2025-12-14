<?php

namespace PatrykMolenda\NetPolicy\Tests\Unit\Network;

use PatrykMolenda\NetPolicy\Network\AsNumber;
use PHPUnit\Framework\TestCase;

class AsNumberTest extends TestCase
{
    public function testConstructorSetsValue(): void
    {
        $asn = new AsNumber(65001);
        $this->assertEquals(65001, $asn->value());
    }

    public function testEqualsReturnsTrueForSameValue(): void
    {
        $asn1 = new AsNumber(65001);
        $asn2 = new AsNumber(65001);

        $this->assertTrue($asn1->equals($asn2));
    }

    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        $asn1 = new AsNumber(65001);
        $asn2 = new AsNumber(65002);

        $this->assertFalse($asn1->equals($asn2));
    }
}

