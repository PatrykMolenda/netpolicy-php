<?php

namespace PatrykMolenda\NetPolicy\Tests\Integration;

use PatrykMolenda\NetPolicy\DSL\PolicyLoader;
use PatrykMolenda\NetPolicy\DSL\PolicyNormalizer;
use PatrykMolenda\NetPolicy\DSL\PolicyParser;
use PHPUnit\Framework\TestCase;

class PolicyPipelineTest extends TestCase
{
    private string $testJsonPath;

    protected function setUp(): void
    {
        $this->testJsonPath = __DIR__ . '/../../fixtures/pipeline_test.json';

        $policyData = [
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

        @mkdir(dirname($this->testJsonPath), 0777, true);
        file_put_contents($this->testJsonPath, json_encode($policyData));
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testJsonPath)) {
            @unlink($this->testJsonPath);
        }
    }

    public function testFullPipelineFromFileToNormalizedPolicySet(): void
    {
        // Load
        $loader = new PolicyLoader();
        $rawData = $loader->loadFile($this->testJsonPath);

        $this->assertIsArray($rawData);
        $this->assertArrayHasKey('policies', $rawData);

        // Parse
        $parser = new PolicyParser();
        $parsed = $parser->parse($rawData);

        $this->assertIsArray($parsed);
        $this->assertCount(1, $parsed['policies']);

        // Normalize
        $normalizer = new PolicyNormalizer();
        $policySet = $normalizer->normalize($parsed);

        $this->assertInstanceOf(\PatrykMolenda\NetPolicy\Domain\PolicySet::class, $policySet);
        $this->assertCount(1, $policySet->policies());

        $policy = $policySet->policies()[0];
        $this->assertEquals('test-policy', $policy->name());
        $this->assertEquals(100, $policy->priority());
        $this->assertCount(1, $policy->rules());
    }

    public function testPipelineHandlesMultiplePolicies(): void
    {
        $data = [
            'policies' => [
                [
                    'name' => 'policy-1',
                    'priority' => 10,
                    'rules' => [
                        [
                            'match' => ['protocol' => 'BGP'],
                            'action' => ['type' => 'accept']
                        ]
                    ]
                ],
                [
                    'name' => 'policy-2',
                    'priority' => 20,
                    'rules' => [
                        [
                            'match' => ['protocol' => 'OSPF'],
                            'action' => ['type' => 'reject']
                        ]
                    ]
                ]
            ]
        ];

        $parser = new PolicyParser();
        $normalizer = new PolicyNormalizer();

        $parsed = $parser->parse($data);
        $policySet = $normalizer->normalize($parsed);

        $this->assertCount(2, $policySet->policies());

        // Check sorting by priority
        $sorted = $policySet->sorted();
        $policies = $sorted->policies();

        $this->assertEquals('policy-1', $policies[0]->name());
        $this->assertEquals('policy-2', $policies[1]->name());
    }
}

