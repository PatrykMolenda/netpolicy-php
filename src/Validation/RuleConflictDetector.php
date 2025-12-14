<?php

namespace PatrykMolenda\NetPolicy\Validation;

use PatrykMolenda\NetPolicy\Domain\PolicySet;
use PatrykMolenda\NetPolicy\Domain\Rule;

final class RuleConflictDetector
{
    /**
     * @param PolicySet $set
     * @return array<int, array{policyA: string, ruleA: Rule, policyB: string, ruleB: Rule}>
     */
    public function detect(PolicySet $set): array
    {
        // Returns list of conflicts found in the given PolicySet (structural, not strings)
        $policies = $set->policies();
        $conflicts = [];
        for ($i = 0; $i < count($policies); $i++) {
            $policyA = $policies[$i];
            foreach ($policyA->rules() as $ruleA) {
                for ($j = $i + 1; $j < count($policies); $j++) {
                    $policyB = $policies[$j];
                    foreach ($policyB->rules() as $ruleB) {
                        if ($this->rulesConflict($ruleA, $ruleB)) {
                            $conflicts[] = [
                                'policyA' => $policyA->name(),
                                'ruleA' => $ruleA,
                                'policyB' => $policyB->name(),
                                'ruleB' => $ruleB,
                            ];
                        }
                    }
                }
            }
        }
        return $conflicts;
    }

    /**
     * @param Rule $ruleA
     * @param Rule $ruleB
     * @return bool
     */
    protected function rulesConflict(Rule $ruleA, Rule $ruleB): bool
    {
        // Two rules conflict if:
        // 1. Their match conditions can overlap (match the same traffic)
        // 2. Their actions differ (conflicting outcomes)

        if (!$this->matchConditionsOverlap($ruleA->match(), $ruleB->match())) {
            return false;
        }

        return $this->actionsConflict($ruleA->action(), $ruleB->action());
    }

    /**
     * @param $matchA
     * @param $matchB
     * @return bool
     */
    protected function matchConditionsOverlap($matchA, $matchB): bool
    {
        // Match conditions overlap if they could potentially match the same traffic

        // Check prefix overlap
        $prefixA = $matchA->prefix();
        $prefixB = $matchB->prefix();

        if ($prefixA !== null && $prefixB !== null) {
            // Both have prefixes - they must overlap
            if (!$prefixA->overlaps($prefixB)) {
                return false;
            }
        }
        // If either is null, it matches any prefix, so we continue

        // Check ASN compatibility
        $asnA = $matchA->asn();
        $asnB = $matchB->asn();

        if ($asnA !== null && $asnB !== null) {
            // Both have ASNs - they must be equal
            if (!$asnA->equals($asnB)) {
                return false;
            }
        }
        // If either is null, it matches any ASN, so we continue

        // Check protocol compatibility
        if ($matchA->protocol() !== $matchB->protocol()) {
            return false;
        }

        // Check direction compatibility
        $dirA = $matchA->direction();
        $dirB = $matchB->direction();

        if ($dirA !== 'any' && $dirB !== 'any' && $dirA !== $dirB) {
            return false;
        }

        // All conditions can overlap
        return true;
    }

    /**
     * @param $actionA
     * @param $actionB
     * @return bool
     */
    protected function actionsConflict($actionA, $actionB): bool
    {
        // Actions conflict if they have different types
        return $actionA->type() !== $actionB->type();
    }
}