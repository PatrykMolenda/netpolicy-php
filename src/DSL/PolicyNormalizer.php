<?php

namespace PatrykMolenda\NetPolicy\DSL;

use PatrykMolenda\NetPolicy\Domain\Action;
use PatrykMolenda\NetPolicy\Domain\AttributeBag;
use PatrykMolenda\NetPolicy\Domain\MatchCondition;
use PatrykMolenda\NetPolicy\Domain\Policy;
use PatrykMolenda\NetPolicy\Domain\PolicySet;
use PatrykMolenda\NetPolicy\Domain\Rule;
use PatrykMolenda\NetPolicy\Network\AsNumber;
use PatrykMolenda\NetPolicy\Network\Prefix;
use PatrykMolenda\NetPolicy\Network\Protocol;
use PatrykMolenda\NetPolicy\Exception\InvalidPolicyException;

final class PolicyNormalizer
{
    /**
     * Normalize parsed policy data into domain objects
     *
     * @param array $parsed
     * @return PolicySet
     * @throws InvalidPolicyException
     */
    public function normalize(array $parsed): PolicySet
    {
        if (!isset($parsed['policies']) || !is_array($parsed['policies'])) {
            throw new InvalidPolicyException('Parsed data must contain a "policies" array');
        }

        $policies = [];

        foreach ($parsed['policies'] as $policyData) {
            $policies[] = $this->normalizePolicy($policyData);
        }

        return new PolicySet(...$policies);
    }

    /**
     * Normalize a single policy
     *
     * @param array $policyData
     * @return Policy
     * @throws InvalidPolicyException
     */
    protected function normalizePolicy(array $policyData): Policy
    {
        $rules = [];

        foreach ($policyData['rules'] as $ruleData) {
            $rules[] = $this->normalizeRule($ruleData);
        }

        return new Policy(
            $policyData['name'],
            $policyData['priority'],
            ...$rules
        );
    }

    /**
     * Normalize a single rule
     *
     * @param array $ruleData
     * @return Rule
     * @throws InvalidPolicyException
     */
    protected function normalizeRule(array $ruleData): Rule
    {
        $match = $this->normalizeMatch($ruleData['match']);
        $action = $this->normalizeAction($ruleData['action']);

        return new Rule($match, $action);
    }

    /**
     * Normalize match condition
     *
     * @param array $matchData
     * @return MatchCondition
     * @throws InvalidPolicyException
     */
    protected function normalizeMatch(array $matchData): MatchCondition
    {
        // Parse prefix
        $prefix = null;
        if ($matchData['prefix'] !== null) {
            try {
                $prefix = new Prefix($matchData['prefix']);
            } catch (\Exception $e) {
                throw new InvalidPolicyException(
                    "Invalid prefix '{$matchData['prefix']}': " . $e->getMessage()
                );
            }
        }

        // Parse ASN
        $asn = null;
        if ($matchData['asn'] !== null) {
            $asn = new AsNumber($matchData['asn']);
        }

        // Parse protocol
        $protocol = Protocol::from($matchData['protocol']);

        return new MatchCondition(
            $prefix,
            $asn,
            $protocol,
            $matchData['direction']
        );
    }

    /**
     * Normalize action
     *
     * @param array $actionData
     * @return Action
     */
    protected function normalizeAction(array $actionData): Action
    {
        $attributes = new AttributeBag($actionData['attributes']);

        return new Action(
            $actionData['type'],
            $attributes
        );
    }
}