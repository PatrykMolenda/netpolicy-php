<?php

declare(strict_types=1);

namespace PatrykMolenda\NetPolicy;

use PatrykMolenda\NetPolicy\Domain\PolicySet;
use PatrykMolenda\NetPolicy\DSL\PolicyLoader;
use PatrykMolenda\NetPolicy\DSL\PolicyNormalizer;
use PatrykMolenda\NetPolicy\DSL\PolicyParser;
use PatrykMolenda\NetPolicy\Engine\Decision;
use PatrykMolenda\NetPolicy\Engine\EvaluationContext;
use PatrykMolenda\NetPolicy\Engine\PolicyEngine;
use PatrykMolenda\NetPolicy\Exception\ValidationException;
use PatrykMolenda\NetPolicy\Render\RenderContext;
use PatrykMolenda\NetPolicy\Render\RendererInterface;
use PatrykMolenda\NetPolicy\Validation\PolicyValidator;
use PatrykMolenda\NetPolicy\Validation\RuleConflictDetector;

final class NetPolicy
{
    private bool $validated = false;

    private function __construct(
        private PolicySet $policySet
    ) {}

    /**
     * Load policy from file (JSON/YAML/XML)
     *
     * @param string $path
     * @return self
     * @throws ValidationException
     */
    public static function fromFile(string $path): self
    {
        $loader = new PolicyLoader();
        $parser = new PolicyParser();
        $normalizer = new PolicyNormalizer();

        $raw = $loader->loadFile($path);
        $parsed = $parser->parse($raw);
        $policySet = $normalizer->normalize($parsed);

        return new self($policySet);
    }

    /**
     * Load policy from array
     *
     * @param array $data
     * @return self
     * @throws ValidationException
     */
    public static function fromArray(array $data): self
    {
        $parser = new PolicyParser();
        $normalizer = new PolicyNormalizer();

        $parsed = $parser->parse($data);
        $policySet = $normalizer->normalize($parsed);

        return new self($policySet);
    }

    /**
     * Validate the policy set
     * Checks for conflicts and validates rules
     *
     * @return self
     * @throws ValidationException
     */
    public function validate(): self
    {
        // Validate using PolicyValidator
        $validator = new PolicyValidator();
        $validator->validate($this->policySet);

        // Check for conflicts
        $conflictDetector = new RuleConflictDetector();
        $conflicts = $conflictDetector->detect($this->policySet);

        if (!empty($conflicts)) {
            $messages = [];
            foreach ($conflicts as $conflict) {
                $messages[] = sprintf(
                    "Conflict between policy '%s' and '%s'",
                    $conflict['policyA'],
                    $conflict['policyB']
                );
            }
            throw new ValidationException(
                "Policy validation failed with " . count($conflicts) . " conflict(s):\n" .
                implode("\n", $messages)
            );
        }

        $this->validated = true;
        return $this;
    }

    /**
     * Evaluate a policy against a context
     *
     * @param EvaluationContext $context
     * @return Decision
     */
    public function evaluate(EvaluationContext $context): Decision
    {
        $engine = new PolicyEngine();
        return $engine->evaluate($this->policySet, $context);
    }

    /**
     * Render policy to vendor-specific configuration
     *
     * @param RendererInterface $renderer
     * @param RenderContext $context
     * @return string
     */
    public function render(RendererInterface $renderer, RenderContext $context): string
    {
        if (!$this->validated) {
            throw new \RuntimeException('Policy must be validated before rendering. Call validate() first.');
        }

        return $renderer->render($this->policySet, $context);
    }

    /**
     * Get the underlying PolicySet
     *
     * @return PolicySet
     */
    public function getPolicySet(): PolicySet
    {
        return $this->policySet;
    }

    /**
     * Check if policy has been validated
     *
     * @return bool
     */
    public function isValidated(): bool
    {
        return $this->validated;
    }
}