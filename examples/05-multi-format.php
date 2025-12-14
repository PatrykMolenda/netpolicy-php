<?php

/**
 * Multi-Format Loading Example
 *
 * This example shows how to load policies from different formats:
 * JSON, YAML, XML, and PHP arrays.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PatrykMolenda\NetPolicy\NetPolicy;
use PatrykMolenda\NetPolicy\DSL\PolicyLoader;

echo "=== Multi-Format Loading Example ===\n\n";

// Prepare example policies in different formats
$policiesDir = __DIR__ . '/policies';
@mkdir($policiesDir, 0755, true);

// 1. JSON format
echo "1. Creating JSON policy...\n";
$jsonPolicy = [
    'policies' => [
        [
            'name' => 'json-policy',
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
                        'attributes' => ['source' => 'json']
                    ]
                ]
            ]
        ]
    ]
];

$jsonPath = $policiesDir . '/policy.json';
file_put_contents($jsonPath, json_encode($jsonPolicy, JSON_PRETTY_PRINT));
echo "   ✓ Created: $jsonPath\n\n";

// 2. YAML format (if yaml extension is available)
echo "2. YAML policy...\n";
if (function_exists('yaml_parse')) {
    $yamlContent = <<<YAML
policies:
  - name: yaml-policy
    priority: 20
    rules:
      - match:
          prefix: "10.0.0.0/8"
          protocol: BGP
          direction: in
        action:
          type: reject
          attributes:
            source: yaml
YAML;

    $yamlPath = $policiesDir . '/policy.yaml';
    file_put_contents($yamlPath, $yamlContent);
    echo "   ✓ Created: $yamlPath\n\n";
} else {
    echo "   ⚠ YAML extension not available, skipping\n\n";
}

// 3. XML format
echo "3. Creating XML policy...\n";
$xmlContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <policies>
        <item>
            <name>xml-policy</name>
            <priority>30</priority>
            <rules>
                <item>
                    <match>
                        <prefix>172.16.0.0/12</prefix>
                        <protocol>BGP</protocol>
                        <direction>in</direction>
                    </match>
                    <action>
                        <type>modify</type>
                        <attributes>
                            <source>xml</source>
                        </attributes>
                    </action>
                </item>
            </rules>
        </item>
    </policies>
</root>
XML;

$xmlPath = $policiesDir . '/policy.xml';
file_put_contents($xmlPath, $xmlContent);
echo "   ✓ Created: $xmlPath\n\n";

// Load and test each format
echo "4. Loading policies from different formats:\n";
echo str_repeat("-", 50) . "\n\n";

// JSON
echo "   a) Loading JSON policy:\n";
try {
    $loader = new PolicyLoader();
    $data = $loader->loadFile($jsonPath);
    echo "      ✓ Loaded successfully\n";
    echo "      Format detected: JSON\n";
    echo "      Policy name: " . $data['policies'][0]['name'] . "\n";
} catch (\Exception $e) {
    echo "      ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// YAML
if (function_exists('yaml_parse') && file_exists($yamlPath)) {
    echo "   b) Loading YAML policy:\n";
    try {
        $loader = new PolicyLoader();
        $data = $loader->loadFile($yamlPath);
        echo "      ✓ Loaded successfully\n";
        echo "      Format detected: YAML\n";
        echo "      Policy name: " . $data['policies'][0]['name'] . "\n";
    } catch (\Exception $e) {
        echo "      ✗ Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// XML
echo "   c) Loading XML policy:\n";
try {
    $loader = new PolicyLoader();
    $data = $loader->loadFile($xmlPath);
    echo "      ✓ Loaded successfully\n";
    echo "      Format detected: XML\n";
    echo "      Policy name: " . $data['policies']['item']['name'] . "\n";
} catch (\Exception $e) {
    echo "      ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// PHP Array (direct)
echo "   d) Loading from PHP array:\n";
try {
    $arrayPolicy = [
        'policies' => [
            [
                'name' => 'php-array-policy',
                'priority' => 40,
                'rules' => [
                    [
                        'match' => [
                            'protocol' => 'BGP',
                            'direction' => 'any'
                        ],
                        'action' => [
                            'type' => 'accept',
                            'attributes' => ['source' => 'php-array']
                        ]
                    ]
                ]
            ]
        ]
    ];

    $netpolicy = NetPolicy::fromArray($arrayPolicy);
    echo "      ✓ Loaded successfully\n";
    echo "      Format: PHP Array (in-memory)\n";
    echo "      Policy name: php-array-policy\n";
} catch (\Exception $e) {
    echo "      ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

echo "5. Using NetPolicy::fromFile() with auto-detection:\n";
echo str_repeat("-", 50) . "\n\n";

echo "   Loading JSON with fromFile():\n";
$netpolicy = NetPolicy::fromFile($jsonPath)->validate();
echo "   ✓ Loaded and validated\n";
echo "   Policies: " . count($netpolicy->getPolicySet()->policies()) . "\n\n";

echo "=== Example completed! ===\n\n";

echo "Summary:\n";
echo "  • NetPolicy supports multiple input formats\n";
echo "  • Format is auto-detected based on file content\n";
echo "  • JSON: Native PHP support (recommended)\n";
echo "  • YAML: Requires yaml PHP extension\n";
echo "  • XML: Native PHP support\n";
echo "  • Array: Direct in-memory loading\n\n";

echo "Best practices:\n";
echo "  • Use JSON for maximum compatibility\n";
echo "  • Use YAML for human-friendly configuration\n";
echo "  • Use PHP arrays for programmatic generation\n";
echo "  • Always validate() after loading\n";

