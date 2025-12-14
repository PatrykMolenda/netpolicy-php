<?php

/**
 * Conflict Detection Example
 *
 * This example shows how NetPolicy detects conflicts between rules
 * with overlapping prefixes and different actions.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PatrykMolenda\NetPolicy\NetPolicy;
use PatrykMolenda\NetPolicy\Exception\ValidationException;

echo "=== Conflict Detection Example ===\n\n";

// Example 1: Conflicting policies
echo "Example 1: Policies with conflicts\n";
echo str_repeat("-", 50) . "\n";

$conflictingPolicies = [
    'policies' => [
        [
            'name' => 'accept-private-networks',
            'priority' => 10,
            'rules' => [
                [
                    'match' => [
                        'prefix' => '192.168.0.0/16',  // Large prefix
                        'protocol' => 'BGP',
                        'direction' => 'in'
                    ],
                    'action' => [
                        'type' => 'accept'
                    ]
                ]
            ]
        ],
        [
            'name' => 'reject-specific-subnet',
            'priority' => 20,
            'rules' => [
                [
                    'match' => [
                        'prefix' => '192.168.1.0/24',  // Overlaps with above!
                        'protocol' => 'BGP',
                        'direction' => 'in'
                    ],
                    'action' => [
                        'type' => 'reject'  // Different action = CONFLICT
                    ]
                ]
            ]
        ]
    ]
];

try {
    $netpolicy = NetPolicy::fromArray($conflictingPolicies);
    $netpolicy->validate();
    echo "✗ No conflict detected (unexpected!)\n";
} catch (ValidationException $e) {
    echo "✓ Conflict detected correctly!\n";
    echo "  Message: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 2: Non-conflicting policies (same action)
echo "Example 2: Overlapping prefixes but same action (no conflict)\n";
echo str_repeat("-", 50) . "\n";

$nonConflictingSame = [
    'policies' => [
        [
            'name' => 'accept-large-network',
            'priority' => 10,
            'rules' => [
                [
                    'match' => [
                        'prefix' => '10.0.0.0/8',
                        'protocol' => 'BGP',
                        'direction' => 'in'
                    ],
                    'action' => ['type' => 'accept']
                ]
            ]
        ],
        [
            'name' => 'accept-subnet',
            'priority' => 20,
            'rules' => [
                [
                    'match' => [
                        'prefix' => '10.1.0.0/16',
                        'protocol' => 'BGP',
                        'direction' => 'in'
                    ],
                    'action' => ['type' => 'accept']  // Same action = OK
                ]
            ]
        ]
    ]
];

try {
    $netpolicy = NetPolicy::fromArray($nonConflictingSame);
    $netpolicy->validate();
    echo "✓ No conflict (same actions)\n";
} catch (ValidationException $e) {
    echo "✗ Unexpected conflict: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 3: Non-overlapping prefixes (no conflict)
echo "Example 3: Different prefixes, different actions (no conflict)\n";
echo str_repeat("-", 50) . "\n";

$nonConflictingDifferent = [
    'policies' => [
        [
            'name' => 'policy-a',
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
            'name' => 'policy-b',
            'priority' => 20,
            'rules' => [
                [
                    'match' => [
                        'prefix' => '10.0.0.0/8',  // No overlap
                        'protocol' => 'BGP',
                        'direction' => 'in'
                    ],
                    'action' => ['type' => 'reject']  // Different action but no overlap = OK
                ]
            ]
        ]
    ]
];

try {
    $netpolicy = NetPolicy::fromArray($nonConflictingDifferent);
    $netpolicy->validate();
    echo "✓ No conflict (prefixes don't overlap)\n";
} catch (ValidationException $e) {
    echo "✗ Unexpected conflict: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 4: Different protocols (no conflict)
echo "Example 4: Same prefix but different protocols (no conflict)\n";
echo str_repeat("-", 50) . "\n";

$nonConflictingProtocol = [
    'policies' => [
        [
            'name' => 'bgp-policy',
            'priority' => 10,
            'rules' => [
                [
                    'match' => [
                        'prefix' => '172.16.0.0/12',
                        'protocol' => 'BGP',
                        'direction' => 'in'
                    ],
                    'action' => ['type' => 'accept']
                ]
            ]
        ],
        [
            'name' => 'ospf-policy',
            'priority' => 20,
            'rules' => [
                [
                    'match' => [
                        'prefix' => '172.16.0.0/12',  // Same prefix
                        'protocol' => 'OSPF',  // Different protocol = OK
                        'direction' => 'in'
                    ],
                    'action' => ['type' => 'reject']
                ]
            ]
        ]
    ]
];

try {
    $netpolicy = NetPolicy::fromArray($nonConflictingProtocol);
    $netpolicy->validate();
    echo "✓ No conflict (different protocols)\n";
} catch (ValidationException $e) {
    echo "✗ Unexpected conflict: " . $e->getMessage() . "\n";
}

echo "\n=== Conflict detection examples completed! ===\n";
echo "\nKey takeaways:\n";
echo "  • Conflicts occur when rules have overlapping match conditions\n";
echo "  • AND different actions (accept vs reject)\n";
echo "  • Same prefix + same protocol + same direction + different action = CONFLICT\n";
echo "  • Validation catches these conflicts before deployment\n";

