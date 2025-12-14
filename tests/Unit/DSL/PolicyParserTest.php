<?php

namespace PatrykMolenda\NetPolicy\Tests\Unit\DSL;

use PatrykMolenda\NetPolicy\DSL\PolicyParser;
use PatrykMolenda\NetPolicy\Exception\InvalidPolicyException;
use PHPUnit\Framework\TestCase;

class PolicyParserTest extends TestCase
{
    private PolicyParser $parser;

    protected function setUp(): void
    {
        $this->parser = new PolicyParser();
    }

    public function testParseValidPolicy(): void
    {
        $data = [
            'policies' => [
                [
                    'name' => 'test-policy',
                    'priority' => 100,
                    'rules' => [
                        [
                            'match' => [
                                'prefix' => '192.168.0.0/16',
                                'asn' => 65001,
                                'protocol' => 'BGP',
                                'direction' => 'in'
                            ],
                            'action' => [
                                'type' => 'accept',
                                'attributes' => ['community' => '100:200']
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $result = $this->parser->parse($data);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('policies', $result);
        $this->assertCount(1, $result['policies']);
        $this->assertEquals('test-policy', $result['policies'][0]['name']);
    }

    public function testParseThrowsExceptionWhenPoliciesKeyMissing(): void
    {
        $this->expectException(InvalidPolicyException::class);
        $this->expectExceptionMessage('Policy data must contain a "policies" array');

        $this->parser->parse([]);
    }

    public function testParseThrowsExceptionForInvalidProtocol(): void
    {
        $data = [
            'policies' => [
                [
                    'name' => 'test',
                    'rules' => [
                        [
                            'match' => ['protocol' => 'INVALID'],
                            'action' => ['type' => 'accept']
                        ]
                    ]
                ]
            ]
        ];

        $this->expectException(InvalidPolicyException::class);
        $this->expectExceptionMessage('Invalid protocol');

        $this->parser->parse($data);
    }

    public function testParseThrowsExceptionForInvalidDirection(): void
    {
        $data = [
            'policies' => [
                [
                    'name' => 'test',
                    'rules' => [
                        [
                            'match' => ['direction' => 'sideways'],
                            'action' => ['type' => 'accept']
                        ]
                    ]
                ]
            ]
        ];

        $this->expectException(InvalidPolicyException::class);
        $this->expectExceptionMessage('Invalid direction');

        $this->parser->parse($data);
    }

    public function testParseThrowsExceptionForInvalidActionType(): void
    {
        $data = [
            'policies' => [
                [
                    'name' => 'test',
                    'rules' => [
                        [
                            'match' => [],
                            'action' => ['type' => 'delete']
                        ]
                    ]
                ]
            ]
        ];

        $this->expectException(InvalidPolicyException::class);
        $this->expectExceptionMessage('Invalid action type');

        $this->parser->parse($data);
    }

    public function testParseThrowsExceptionForMissingPolicyName(): void
    {
        $data = [
            'policies' => [
                ['rules' => []]
            ]
        ];

        $this->expectException(InvalidPolicyException::class);
        $this->expectExceptionMessage('must have a \'name\' field');

        $this->parser->parse($data);
    }

    public function testParseSetsDefaultPriority(): void
    {
        $data = [
            'policies' => [
                [
                    'name' => 'test',
                    'rules' => [
                        [
                            'match' => [],
                            'action' => ['type' => 'accept']
                        ]
                    ]
                ]
            ]
        ];

        $result = $this->parser->parse($data);

        $this->assertEquals(100, $result['policies'][0]['priority']);
    }
}

