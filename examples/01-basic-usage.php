<?php

/**
 * Basic Usage Example
 *
 * This example demonstrates the basic workflow:
 * 1. Load policy from file
 * 2. Validate policy
 * 3. Evaluate against traffic
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PatrykMolenda\NetPolicy\NetPolicy;
use PatrykMolenda\NetPolicy\Engine\EvaluationContext;
use PatrykMolenda\NetPolicy\Network\Prefix;
use PatrykMolenda\NetPolicy\Network\Protocol;
use PatrykMolenda\NetPolicy\Network\AsNumber;

echo "=== NetPolicy PHP - Basic Usage Example ===\n\n";

// Step 1: Load policy from JSON file
echo "1. Loading policy from file...\n";
$policyPath = __DIR__ . '/policies/basic-policy.json';

if (!file_exists($policyPath)) {
    echo "   Creating example policy file...\n";

    $examplePolicy = [
        'policies' => [
            [
                'name' => 'customer-traffic',
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
                            'attributes' => [
                                'local-pref' => 150,
                                'community' => '100:200'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'name' => 'ospf-traffic',
                'priority' => 20,
                'rules' => [
                    [
                        'match' => [
                            'protocol' => 'OSPF',
                            'direction' => 'any'
                        ],
                        'action' => [
                            'type' => 'accept'
                        ]
                    ]
                ]
            ]
        ]
    ];

    @mkdir(dirname($policyPath), 0755, true);
    file_put_contents($policyPath, json_encode($examplePolicy, JSON_PRETTY_PRINT));
}

$netpolicy = NetPolicy::fromFile($policyPath);
echo "   ✓ Policy loaded\n\n";

// Step 2: Validate policy
echo "2. Validating policy...\n";
try {
    $netpolicy->validate();
    echo "   ✓ Policy is valid (no conflicts detected)\n\n";
} catch (\Exception $e) {
    echo "   ✗ Validation failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 3: Evaluate traffic
echo "3. Evaluating traffic scenarios:\n\n";

// Scenario 1: Customer traffic (should accept)
echo "   Scenario 1: Customer traffic (192.168.1.0/24)\n";
$context1 = new EvaluationContext(
    new Prefix('192.168.1.0/24'),
    Protocol::BGP,
    null,
    'in'
);

$decision1 = $netpolicy->evaluate($context1);
echo "   Decision: " . strtoupper($decision1->action()) . "\n";
if ($decision1->action() === 'accept') {
    $attrs = $decision1->attributes()->all();
    echo "   Attributes:\n";
    foreach ($attrs as $key => $value) {
        echo "     - $key: $value\n";
    }
}
echo "\n";

// Scenario 2: Non-customer traffic (should reject)
echo "   Scenario 2: Non-customer traffic (10.0.1.0/24)\n";
$context2 = new EvaluationContext(
    new Prefix('10.0.1.0/24'),
    Protocol::BGP,
    null,
    'in'
);

$decision2 = $netpolicy->evaluate($context2);
echo "   Decision: " . strtoupper($decision2->action()) . "\n\n";

// Scenario 3: OSPF traffic (should accept)
echo "   Scenario 3: OSPF traffic\n";
$context3 = new EvaluationContext(
    new Prefix('172.16.0.0/12'),
    Protocol::OSPF,
    null,
    'any'
);

$decision3 = $netpolicy->evaluate($context3);
echo "   Decision: " . strtoupper($decision3->action()) . "\n";
echo "   (OSPF traffic accepted)\n\n";

echo "=== Example completed successfully! ===\n";

