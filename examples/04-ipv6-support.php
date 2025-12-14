<?php

/**
 * IPv6 Support Example
 *
 * This example demonstrates IPv6 prefix handling,
 * including validation, evaluation, and rendering.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PatrykMolenda\NetPolicy\NetPolicy;
use PatrykMolenda\NetPolicy\Engine\EvaluationContext;
use PatrykMolenda\NetPolicy\Network\Prefix;
use PatrykMolenda\NetPolicy\Network\Protocol;
use PatrykMolenda\NetPolicy\Network\AsNumber;

echo "=== IPv6 Support Example ===\n\n";

// Define IPv6 policies
$ipv6Policy = [
    'policies' => [
        [
            'name' => 'ipv6-customer-prefixes',
            'priority' => 10,
            'rules' => [
                [
                    'match' => [
                        'prefix' => '2001:db8::/32',
                        'asn' => 65001,
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
            'name' => 'ipv6-bogon-filter',
            'priority' => 5,
            'rules' => [
                [
                    'match' => [
                        'prefix' => 'fc00::/7',  // ULA
                        'protocol' => 'BGP',
                        'direction' => 'in'
                    ],
                    'action' => [
                        'type' => 'reject'
                    ]
                ],
                [
                    'match' => [
                        'prefix' => 'fe80::/10',  // Link-local
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

echo "1. Testing IPv6 Prefix Operations\n";
echo str_repeat("-", 50) . "\n";

// Test IPv6 prefix contains
$prefix1 = new Prefix('2001:db8::/32');
$prefix2 = new Prefix('2001:db8:1::/48');
$prefix3 = new Prefix('2001:db9::/32');

echo "Prefix 1: {$prefix1->cidr()}\n";
echo "Prefix 2: {$prefix2->cidr()}\n";
echo "Prefix 3: {$prefix3->cidr()}\n\n";

echo "Tests:\n";
echo "  P1 contains P2: " . ($prefix1->contains($prefix2) ? "YES ✓" : "NO ✗") . "\n";
echo "  P1 overlaps P2: " . ($prefix1->overlaps($prefix2) ? "YES ✓" : "NO ✗") . "\n";
echo "  P1 overlaps P3: " . ($prefix1->overlaps($prefix3) ? "YES ✓" : "NO ✗") . "\n";
echo "  (Expected: YES, YES, NO)\n\n";

// Test policy
echo "2. Loading and validating IPv6 policy...\n";
$netpolicy = NetPolicy::fromArray($ipv6Policy)->validate();
echo "   ✓ IPv6 policy validated\n\n";

echo "3. Evaluating IPv6 traffic scenarios:\n\n";

// Scenario 1: Customer prefix (should accept)
echo "   Scenario 1: Customer prefix (2001:db8:1::/48)\n";
$context1 = new EvaluationContext(
    new Prefix('2001:db8:1::/48'),
    Protocol::BGP,
    new AsNumber(65001),
    'in'
);

$decision1 = $netpolicy->evaluate($context1);
echo "   Decision: " . strtoupper($decision1->action()) . "\n";
if ($decision1->action() === 'accept') {
    echo "   Attributes: " . json_encode($decision1->attributes()->all()) . "\n";
}
echo "\n";

// Scenario 2: ULA prefix (should reject)
echo "   Scenario 2: ULA prefix (fc00::/48)\n";
$context2 = new EvaluationContext(
    new Prefix('fc00::/48'),
    Protocol::BGP,
    null,
    'in'
);

$decision2 = $netpolicy->evaluate($context2);
echo "   Decision: " . strtoupper($decision2->action()) . "\n";
echo "   (Bogon filter applied)\n\n";

// Scenario 3: Link-local prefix (should reject)
echo "   Scenario 3: Link-local prefix (fe80::/64)\n";
$context3 = new EvaluationContext(
    new Prefix('fe80::/64'),
    Protocol::BGP,
    null,
    'in'
);

$decision3 = $netpolicy->evaluate($context3);
echo "   Decision: " . strtoupper($decision3->action()) . "\n";
echo "   (Bogon filter applied)\n\n";

// Scenario 4: Non-matching prefix (should default deny)
echo "   Scenario 4: Non-matching prefix (2001:db9::/48)\n";
$context4 = new EvaluationContext(
    new Prefix('2001:db9::/48'),
    Protocol::BGP,
    null,
    'in'
);

$decision4 = $netpolicy->evaluate($context4);
echo "   Decision: " . strtoupper($decision4->action()) . "\n";
echo "   (Default deny applied)\n\n";

echo "4. Common IPv6 Prefix Types:\n";
echo str_repeat("-", 50) . "\n";
echo "  • Documentation: 2001:db8::/32\n";
echo "  • Unique Local (ULA): fc00::/7\n";
echo "  • Link-local: fe80::/10\n";
echo "  • Multicast: ff00::/8\n";
echo "  • Global Unicast: 2000::/3\n";
echo "  • Loopback: ::1/128\n";
echo "  • Unspecified: ::/128\n\n";

echo "=== IPv6 example completed! ===\n";
echo "\nKey features demonstrated:\n";
echo "  ✓ IPv6 CIDR notation support\n";
echo "  ✓ IPv6 prefix contains/overlaps operations\n";
echo "  ✓ IPv6 policy evaluation\n";
echo "  ✓ IPv6 bogon filtering\n";

