<?php

namespace PatrykMolenda\NetPolicy\Render;

use PatrykMolenda\NetPolicy\Domain\PolicySet;

interface RendererInterface
{
    public function render(
        PolicySet $set,
        RenderContext $context
    ): string;
}