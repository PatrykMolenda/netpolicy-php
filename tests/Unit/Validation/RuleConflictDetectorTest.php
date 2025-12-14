<?php

namespace PatrykMolenda\NetPolicy\Tests\Unit\Validation;

use PatrykMolenda\NetPolicy\Domain\Action;
use PatrykMolenda\NetPolicy\Domain\AttributeBag;
use PatrykMolenda\NetPolicy\Domain\MatchCondition;
use PatrykMolenda\NetPolicy\Domain\Policy;
use PatrykMolenda\NetPolicy\Domain\PolicySet;
use PatrykMolenda\NetPolicy\Domain\Rule;
use PatrykMolenda\NetPolicy\Network\AsNumber;
use PatrykMolenda\NetPolicy\Network\Prefix;
use PatrykMolenda\NetPolicy\Network\Protocol;
use PatrykMolenda\NetPolicy\Validation\RuleConflictDetector;
use PHPUnit\Framework\TestCase;

class RuleConflictDetectorTest extends TestCase
{
    private RuleConflictDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new RuleConflictDetector();
    }

    public function testDetectReturnsEmptyArrayWhenNoConflicts(): void
    {
        $policy = new Policy(
            'test',
            100,
            $this->createRule('192.168.0.0/16', Action::ACCEPT),
            $this->createRule('10.0.0.0/8', Action::ACCEPT)
        );

        $set = new PolicySet($policy);
        $conflicts = $this->detector->detect($set);

        $this->assertEmpty($conflicts);
    }

    public function testDetectFindsConflictWithOverlappingPrefixesAndDifferentActions(): void
    {
        $policy1 = new Policy(
            'policy1',
            10,
            $this->createRule('192.168.0.0/16', Action::ACCEPT)
        );

        $policy2 = new Policy(
            'policy2',
            20,
            $this->createRule('192.168.1.0/24', Action::REJECT)
        );

        $set = new PolicySet($policy1, $policy2);
        $conflicts = $this->detector->detect($set);

        $this->assertCount(1, $conflicts);
        $this->assertEquals('policy1', $conflicts[0]['policyA']);
        $this->assertEquals('policy2', $conflicts[0]['policyB']);
    }

    public function testDetectDoesNotFindConflictWhenPrefixesDoNotOverlap(): void
    {
        $policy1 = new Policy(
            'policy1',
            10,
            $this->createRule('192.168.0.0/16', Action::ACCEPT)
        );

        $policy2 = new Policy(
            'policy2',
            20,
            $this->createRule('10.0.0.0/8', Action::REJECT)
        );

        $set = new PolicySet($policy1, $policy2);
        $conflicts = $this->detector->detect($set);

        $this->assertEmpty($conflicts);
    }

    public function testDetectDoesNotFindConflictWhenActionsAreSame(): void
    {
        $policy1 = new Policy(
            'policy1',
            10,
            $this->createRule('192.168.0.0/16', Action::ACCEPT)
        );

        $policy2 = new Policy(
            'policy2',
            20,
            $this->createRule('192.168.1.0/24', Action::ACCEPT)
        );

        $set = new PolicySet($policy1, $policy2);
        $conflicts = $this->detector->detect($set);

        $this->assertEmpty($conflicts);
    }

    private function createRule(string $prefix, string $actionType): Rule
    {
        $match = new MatchCondition(
            new Prefix($prefix),
            null,
            Protocol::BGP,
            'in'
        );

        $action = new Action($actionType, new AttributeBag());

        return new Rule($match, $action);
    }
}

