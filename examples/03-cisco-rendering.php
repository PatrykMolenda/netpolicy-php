<?php

/**
 * Cisco IOS-XR Rendering Example
 *
 * This example demonstrates how to render policies
 * to Cisco IOS-XR route-policy configuration.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PatrykMolenda\NetPolicy\NetPolicy;
use PatrykMolenda\NetPolicy\Render\Cisco\IosXrRenderer;
use PatrykMolenda\NetPolicy\Render\RenderContext;

echo "=== Cisco IOS-XR Rendering Example ===\n\n";

// Define a realistic BGP policy
$bgpPolicy = [
    'policies' => [
        [
            'name' => 'CUSTOMER-IN',
            'priority' => 10,
            'rules' => [
                [
                    'match' => [
                        'prefix' => '203.0.113.0/24',
                        'asn' => 65001,
                        'protocol' => 'BGP',
                        'direction' => 'in'
                    ],
                    'action' => [
                        'type' => 'accept',
                        'attributes' => [
                            'local-pref' => 200,
                            'community' => '100:200',
                            'med' => 50
                        ]
                    ]
                ]
            ]
        ],
        [
            'name' => 'REJECT-BOGONS',
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
                ],
                [
                    'match' => [
                        'prefix' => '192.168.0.0/16',
                        'protocol' => 'BGP',
                        'direction' => 'in'
                    ],
                    'action' => [
                        'type' => 'reject'
                    ]
                ],
                [
                    'match' => [
                        'prefix' => '172.16.0.0/12',
                        'protocol' => 'BGP',
                        'direction' => 'in'
                    ],
                    'action' => [
                        'type' => 'reject'
                    ]
                ]
            ]
        ],
        [
            'name' => 'PEER-IN',
            'priority' => 20,
            'rules' => [
                [
                    'match' => [
                        'prefix' => '198.51.100.0/24',
                        'protocol' => 'BGP',
                        'direction' => 'in'
                    ],
                    'action' => [
                        'type' => 'modify',
                        'attributes' => [
                            'local-pref' => 100,
                            'community' => '100:300'
                        ]
                    ]
                ]
            ]
        ]
    ]
];

echo "1. Loading and validating policy...\n";
$netpolicy = NetPolicy::fromArray($bgpPolicy)->validate();
echo "   ✓ Policy validated\n\n";

echo "2. Rendering to Cisco IOS-XR configuration...\n";
$renderer = new IosXrRenderer();
$context = new RenderContext('cisco', 'edge-router', 'ipv4');

$config = $netpolicy->render($renderer, $context);

echo "   ✓ Configuration generated\n\n";

echo "3. Generated IOS-XR Configuration:\n";
echo str_repeat("=", 70) . "\n";
echo $config;
echo str_repeat("=", 70) . "\n\n";

// Save to file
$outputPath = __DIR__ . '/output/iosxr-config.txt';
@mkdir(dirname($outputPath), 0755, true);
file_put_contents($outputPath, $config);

echo "4. Configuration saved to: $outputPath\n\n";

echo "How to use this configuration:\n";
echo "  1. Copy the generated configuration\n";
echo "  2. Paste into your IOS-XR router config mode\n";
echo "  3. Apply to BGP neighbor:\n";
echo "     router bgp 65000\n";
echo "      neighbor 203.0.113.1\n";
echo "       address-family ipv4 unicast\n";
echo "        route-policy CUSTOMER-IN in\n";
echo "       !\n";
echo "      !\n";
echo "     !\n\n";

echo "=== Example completed! ===\n";

