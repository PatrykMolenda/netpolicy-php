<?php

namespace PatrykMolenda\NetPolicy\Tests\Integration;

use PatrykMolenda\NetPolicy\NetPolicy;
use PatrykMolenda\NetPolicy\Engine\EvaluationContext;
use PatrykMolenda\NetPolicy\Exception\ValidationException;
use PatrykMolenda\NetPolicy\Network\AsNumber;
use PatrykMolenda\NetPolicy\Network\Prefix;
use PatrykMolenda\NetPolicy\Network\Protocol;
use PHPUnit\Framework\TestCase;

class NetPolicyTest extends TestCase
{
    private string $testPolicyPath;

    protected function setUp(): void
    {
        $this->testPolicyPath = __DIR__ . '/../../fixtures/test_policy.json';

        // Create test policy file
        $policyData = [
            'policies' => [
                [
                    'name' => 'bgp-accept-customers',
                    'priority' => 10,
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
                                'attributes' => [
                                    'community' => '100:200',
                                    'local-pref' => 150
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'bgp-reject-bogons',
                    'priority' => 5,
                    'rules' => [
                        [
                            'match' => [
                                'prefix' => '10.0.0.0/8',
                                'protocol' => 'BGP',
                                'direction' => 'in'
                            ],
                            'action' => [
                                'type' => 'reject'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        @mkdir(dirname($this->testPolicyPath), 0777, true);
        file_put_contents($this->testPolicyPath, json_encode($policyData));
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testPolicyPath)) {
            @unlink($this->testPolicyPath);
        }
    }

    public function testFromFileLoadsAndParsesPolicy(): void
    {
        $netpolicy = NetPolicy::fromFile($this->testPolicyPath);

        $this->assertInstanceOf(NetPolicy::class, $netpolicy);
        $this->assertCount(2, $netpolicy->getPolicySet()->policies());
    }

    public function testFromArrayCreatesPolicy(): void
    {
        $data = [
            'policies' => [
                [
                    'name' => 'test-policy',
                    'priority' => 50,
                    'rules' => [
                        [
                            'match' => [
                                'protocol' => 'BGP',
                                'direction' => 'in'
                            ],
                            'action' => [
                                'type' => 'accept'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $netpolicy = NetPolicy::fromArray($data);

        $this->assertInstanceOf(NetPolicy::class, $netpolicy);
        $this->assertCount(1, $netpolicy->getPolicySet()->policies());
    }

    public function testValidateSucceedsForValidPolicy(): void
    {
        $netpolicy = NetPolicy::fromFile($this->testPolicyPath);

        $result = $netpolicy->validate();

        $this->assertSame($netpolicy, $result);
        $this->assertTrue($netpolicy->isValidated());
    }

    public function testValidateThrowsExceptionForEmptyPolicySet(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Policy set cannot be empty');

        NetPolicy::fromArray(['policies' => []])->validate();
    }

    public function testValidateDetectsConflicts(): void
    {
        $data = [
            'policies' => [
                [
                    'name' => 'policy-1',
                    'priority' => 10,
                    'rules' => [
                        [
                            'match' => [
                                'prefix' => '192.168.0.0/16',
                                'protocol' => 'BGP',
                                'direction' => 'in'
                            ],
                            'action' => ['type' => 'accept']
                        ]
                    ]
                ],
                [
                    'name' => 'policy-2',
                    'priority' => 20,
                    'rules' => [
                        [
                            'match' => [
                                'prefix' => '192.168.1.0/24',
                                'protocol' => 'BGP',
                                'direction' => 'in'
                            ],
                            'action' => ['type' => 'reject']
                        ]
                    ]
                ]
            ]
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('conflict');

        NetPolicy::fromArray($data)->validate();
    }

    public function testEvaluateReturnsDecision(): void
    {
        $netpolicy = NetPolicy::fromFile($this->testPolicyPath);

        $context = new EvaluationContext(
            new Prefix('10.1.1.0/24'),
            Protocol::BGP,
            null,
            'in'
        );

        $decision = $netpolicy->evaluate($context);

        $this->assertEquals('reject', $decision->action());
    }

    public function testEvaluateMatchesCustomerPrefix(): void
    {
        // Create a simpler test with clearer expectations
        $data = [
            'policies' => [
                [
                    'name' => 'customer-policy',
                    'priority' => 10,
                    'rules' => [
                        [
                            'match' => [
                                'prefix' => '192.168.0.0/16',
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

        $netpolicy = NetPolicy::fromArray($data);

        // Test with a prefix that should match
        $context = new EvaluationContext(
            new Prefix('192.168.1.0/24'),  // This is contained in 192.168.0.0/16
            Protocol::BGP,
            null,  // No ASN requirement in the rule
            'in'
        );

        $decision = $netpolicy->evaluate($context);

        $this->assertEquals('accept', $decision->action());
    }

    public function testRenderThrowsExceptionIfNotValidated(): void
    {
        $netpolicy = NetPolicy::fromFile($this->testPolicyPath);
        $renderer = new \PatrykMolenda\NetPolicy\Render\Cisco\IosXrRenderer();
        $context = new \PatrykMolenda\NetPolicy\Render\RenderContext('cisco', 'edge', 'ipv4');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('must be validated');

        $netpolicy->render($renderer, $context);
    }

    public function testRenderGeneratesConfiguration(): void
    {
        $netpolicy = NetPolicy::fromFile($this->testPolicyPath)->validate();
        $renderer = new \PatrykMolenda\NetPolicy\Render\Cisco\IosXrRenderer();
        $context = new \PatrykMolenda\NetPolicy\Render\RenderContext('cisco', 'edge', 'ipv4');

        $config = $netpolicy->render($renderer, $context);

        $this->assertIsString($config);
        $this->assertStringContainsString('route-policy', $config);
        $this->assertStringContainsString('prefix-set', $config);
    }
}

