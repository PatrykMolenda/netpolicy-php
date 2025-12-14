<?php

namespace PatrykMolenda\NetPolicy\Tests\Unit\Engine;

use PatrykMolenda\NetPolicy\Engine\EvaluationContext;
use PatrykMolenda\NetPolicy\Network\AsNumber;
use PatrykMolenda\NetPolicy\Network\Prefix;
use PatrykMolenda\NetPolicy\Network\Protocol;
use PHPUnit\Framework\TestCase;

class EvaluationContextTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $prefix = new Prefix('192.168.1.0/24');
        $protocol = Protocol::BGP;
        $asn = new AsNumber(65001);
        $direction = 'in';

        $context = new EvaluationContext($prefix, $protocol, $asn, $direction);

        $this->assertSame($prefix, $context->ipAddress());
        $this->assertSame($protocol, $context->protocol());
        $this->assertSame($asn, $context->asNumber());
        $this->assertEquals($direction, $context->direction());
    }

    public function testConstructorAllowsNullAsn(): void
    {
        $prefix = new Prefix('192.168.1.0/24');
        $protocol = Protocol::BGP;
        $direction = 'in';

        $context = new EvaluationContext($prefix, $protocol, null, $direction);

        $this->assertNull($context->asNumber());
    }
}

