<?php

namespace Roots\Acorn\Exceptions\Solutions;

use Illuminate\Support\Str;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Spatie\Ignition\Contracts\Solution;
use Throwable;

class ManifestSolutionProvider implements HasSolutionsForThrowable
{
    /**
     * The documentation links.
     */
    protected array $links = [
        'Sage Documentation' => 'https://roots.io/sage/docs/installation/',
        'Bud Documentation' => 'https://bud.js.org/learn/getting-started',
    ];

    /**
     * Determine if the provider can solve the given throwable.
     */
    public function canSolve(Throwable $throwable): bool
    {
        return Str::startsWith($throwable->getMessage(), 'The asset manifest');
    }

    /**
     * Get the solutions for the given throwable.
     */
    public function getSolutions(Throwable $throwable): array
    {
        return [$this->getSolution()];
    }

    /**
     * Get the solutions for the given throwable.
     */
    public function getSolution(): Solution
    {
        $baseCommand = collect([
            'pnpm-lock.yaml' => 'pnpm',
            'yarn.lock' => 'yarn',
        ])->first(fn ($_, $lockfile) => file_exists(base_path($lockfile)), 'npm run');

        return app()->environment('development', 'testing', 'local')
            ? $this->getLocalSolution($baseCommand)
            : $this->getProductionSolution($baseCommand);
    }

    /**
     * Get the local solution.
     */
    protected function getLocalSolution(string $baseCommand): Solution
    {
        return BaseSolution::create('Start the development server')
            ->setSolutionDescription("Run `{$baseCommand} dev` in your terminal and refresh the page.")
            ->setDocumentationLinks($this->links);
    }

    /**
     * Get the production solution.
     */
    protected function getProductionSolution(string $baseCommand): Solution
    {
        return BaseSolution::create('Build the production assets')
            ->setSolutionDescription("Run `{$baseCommand} build` in your deployment script.")
            ->setDocumentationLinks($this->links);
    }
}
