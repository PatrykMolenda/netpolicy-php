<?php

namespace PatrykMolenda\NetPolicy\Validation\Schema;

use PatrykMolenda\NetPolicy\Exception\ValidationException;

final class V1Schema implements SchemaInterface
{
    /**
     * Validate data against V1 schema
     *
     * @param array $data
     * @return bool
     * @throws ValidationException
     */
    public function validate(array $data): bool
    {
        // Root must have 'policies' key
        if (!isset($data['policies']) || !is_array($data['policies'])) {
            throw new ValidationException('Root must contain "policies" array');
        }

        // Validate each policy
        foreach ($data['policies'] as $index => $policy) {
            $this->validatePolicy($policy, $index);
        }

        return true;
    }

    /**
     * Validate single policy
     *
     * @param mixed $policy
     * @param int $index
     * @return void
     * @throws ValidationException
     */
    protected function validatePolicy(mixed $policy, int $index): void
    {
        if (!is_array($policy)) {
            throw new ValidationException("Policy at index $index must be an array");
        }

        // Required fields
        if (!isset($policy['name'])) {
            throw new ValidationException("Policy at index $index must have 'name' field");
        }

        if (!isset($policy['rules']) || !is_array($policy['rules'])) {
            throw new ValidationException("Policy at index $index must have 'rules' array");
        }

        // Optional: priority
        if (isset($policy['priority']) && !is_int($policy['priority'])) {
            throw new ValidationException("Policy at index $index has invalid 'priority' (must be integer)");
        }

        // Validate rules
        foreach ($policy['rules'] as $ruleIndex => $rule) {
            $this->validateRule($rule, $policy['name'], $ruleIndex);
        }
    }

    /**
     * Validate single rule
     *
     * @param mixed $rule
     * @param string $policyName
     * @param int $index
     * @return void
     * @throws ValidationException
     */
    protected function validateRule(mixed $rule, string $policyName, int $index): void
    {
        if (!is_array($rule)) {
            throw new ValidationException("Rule at index $index in policy '$policyName' must be an array");
        }

        // Required: match
        if (!isset($rule['match']) || !is_array($rule['match'])) {
            throw new ValidationException("Rule at index $index in policy '$policyName' must have 'match' object");
        }

        // Required: action
        if (!isset($rule['action']) || !is_array($rule['action'])) {
            throw new ValidationException("Rule at index $index in policy '$policyName' must have 'action' object");
        }

        // Validate action has type
        if (!isset($rule['action']['type'])) {
            throw new ValidationException("Action in rule $index of policy '$policyName' must have 'type' field");
        }
    }

    /**
     * Get schema version
     *
     * @return string
     */
    public function version(): string
    {
        return '1.0';
    }

    /**
     * Export schema as array (JSON Schema compatible)
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            '$schema' => 'http://json-schema.org/draft-07/schema#',
            'title' => 'NetPolicy V1 Schema',
            'version' => $this->version(),
            'type' => 'object',
            'required' => ['policies'],
            'properties' => [
                'policies' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'required' => ['name', 'rules'],
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'priority' => ['type' => 'integer'],
                            'rules' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'required' => ['match', 'action'],
                                    'properties' => [
                                        'match' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'prefix' => ['type' => 'string'],
                                                'asn' => ['type' => 'integer'],
                                                'protocol' => [
                                                    'type' => 'string',
                                                    'enum' => ['BGP', 'OSPF', 'STATIC']
                                                ],
                                                'direction' => [
                                                    'type' => 'string',
                                                    'enum' => ['in', 'out', 'any']
                                                ]
                                            ]
                                        ],
                                        'action' => [
                                            'type' => 'object',
                                            'required' => ['type'],
                                            'properties' => [
                                                'type' => [
                                                    'type' => 'string',
                                                    'enum' => ['accept', 'reject', 'modify']
                                                ],
                                                'attributes' => ['type' => 'object']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}