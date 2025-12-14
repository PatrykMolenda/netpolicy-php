<?php

namespace PatrykMolenda\NetPolicy\DSL;

use PatrykMolenda\NetPolicy\Exception\InvalidPolicyException;

final class PolicyParser
{
    /**
     * Parse raw policy data into structured format
     *
     * Expected input format:
     * [
     *   'policies' => [
     *     [
     *       'name' => 'policy_name',
     *       'priority' => 100,
     *       'rules' => [
     *         [
     *           'match' => [
     *             'prefix' => '192.168.0.0/16',
     *             'asn' => 65001,
     *             'protocol' => 'BGP',
     *             'direction' => 'in'
     *           ],
     *           'action' => [
     *             'type' => 'accept',
     *             'attributes' => ['community' => '100:200']
     *           ]
     *         ]
     *       ]
     *     ]
     *   ]
     * ]
     *
     * @param array $raw
     * @return array
     * @throws InvalidPolicyException
     */
    public function parse(array $raw): array
    {
        if (!isset($raw['policies']) || !is_array($raw['policies'])) {
            throw new InvalidPolicyException('Policy data must contain a "policies" array');
        }

        $parsed = [
            'policies' => []
        ];

        foreach ($raw['policies'] as $index => $policyData) {
            $parsed['policies'][] = $this->parsePolicy($policyData, $index);
        }

        return $parsed;
    }

    /**
     * Parse a single policy
     *
     * @param array $policyData
     * @param int $index
     * @return array
     * @throws InvalidPolicyException
     */
    protected function parsePolicy(array $policyData, int $index): array
    {
        if (!isset($policyData['name'])) {
            throw new InvalidPolicyException("Policy at index $index must have a 'name' field");
        }

        if (!isset($policyData['rules']) || !is_array($policyData['rules'])) {
            throw new InvalidPolicyException("Policy '{$policyData['name']}' must have a 'rules' array");
        }

        $policy = [
            'name' => (string)$policyData['name'],
            'priority' => isset($policyData['priority']) ? (int)$policyData['priority'] : 100,
            'rules' => []
        ];

        foreach ($policyData['rules'] as $ruleIndex => $ruleData) {
            $policy['rules'][] = $this->parseRule($ruleData, $policyData['name'], $ruleIndex);
        }

        return $policy;
    }

    /**
     * Parse a single rule
     *
     * @param array $ruleData
     * @param string $policyName
     * @param int $index
     * @return array
     * @throws InvalidPolicyException
     */
    protected function parseRule(array $ruleData, string $policyName, int $index): array
    {
        if (!isset($ruleData['match']) || !is_array($ruleData['match'])) {
            throw new InvalidPolicyException("Rule at index $index in policy '$policyName' must have a 'match' section");
        }

        if (!isset($ruleData['action']) || !is_array($ruleData['action'])) {
            throw new InvalidPolicyException("Rule at index $index in policy '$policyName' must have an 'action' section");
        }

        return [
            'match' => $this->parseMatch($ruleData['match'], $policyName, $index),
            'action' => $this->parseAction($ruleData['action'], $policyName, $index)
        ];
    }

    /**
     * Parse match conditions
     *
     * @param array $matchData
     * @param string $policyName
     * @param int $ruleIndex
     * @return array
     * @throws InvalidPolicyException
     */
    protected function parseMatch(array $matchData, string $policyName, int $ruleIndex): array
    {
        $match = [
            'prefix' => $matchData['prefix'] ?? null,
            'asn' => isset($matchData['asn']) ? (int)$matchData['asn'] : null,
            'protocol' => $matchData['protocol'] ?? 'BGP',
            'direction' => $matchData['direction'] ?? 'any'
        ];

        // Validate protocol
        $validProtocols = ['BGP', 'OSPF', 'STATIC'];
        if (!in_array($match['protocol'], $validProtocols)) {
            throw new InvalidPolicyException(
                "Invalid protocol '{$match['protocol']}' in rule $ruleIndex of policy '$policyName'. " .
                "Valid protocols: " . implode(', ', $validProtocols)
            );
        }

        // Validate direction
        $validDirections = ['in', 'out', 'any'];
        if (!in_array($match['direction'], $validDirections)) {
            throw new InvalidPolicyException(
                "Invalid direction '{$match['direction']}' in rule $ruleIndex of policy '$policyName'. " .
                "Valid directions: " . implode(', ', $validDirections)
            );
        }

        return $match;
    }

    /**
     * Parse action
     *
     * @param array $actionData
     * @param string $policyName
     * @param int $ruleIndex
     * @return array
     * @throws InvalidPolicyException
     */
    protected function parseAction(array $actionData, string $policyName, int $ruleIndex): array
    {
        if (!isset($actionData['type'])) {
            throw new InvalidPolicyException(
                "Action in rule $ruleIndex of policy '$policyName' must have a 'type' field"
            );
        }

        $validTypes = ['accept', 'reject', 'modify'];
        if (!in_array($actionData['type'], $validTypes)) {
            throw new InvalidPolicyException(
                "Invalid action type '{$actionData['type']}' in rule $ruleIndex of policy '$policyName'. " .
                "Valid types: " . implode(', ', $validTypes)
            );
        }

        return [
            'type' => $actionData['type'],
            'attributes' => $actionData['attributes'] ?? []
        ];
    }
}