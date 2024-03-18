<?php

namespace Roots\Acorn\Exceptions\Solutions;

use Roots\Acorn\Exceptions\SkipProviderException;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Throwable;

class SkipProviderSolutionProvider implements HasSolutionsForThrowable
{
    /**
     * Determine if the provider can solve the given throwable.
     */
    public function canSolve(Throwable $throwable): bool
    {
        return $throwable instanceof SkipProviderException;
    }

    /**
     * Get the solutions for the given throwable.
     */
    public function getSolutions(Throwable $throwable): array
    {
        return [
            BaseSolution::create('Clear the provider cache')
                ->setSolutionDescription('Run `wp acorn optimize:clear` in your terminal and refresh the page.'),
        ];
    }
}
