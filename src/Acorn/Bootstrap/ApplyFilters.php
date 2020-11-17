<?php

namespace Roots\Acorn\Bootstrap;

use InvalidArgumentException;
use Roots\Acorn\Application;
use Roots\Acorn\Support\Contracts\Filter;

class ApplyFilters
{
    /**
     * Application instance.
     *
     * @var \Roots\Acorn\Application
     */
    protected $app;

    /**
     * Bootstrap the given application.
     *
     * @param \Roots\Acorn\Application $app
     *
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $this->app = $app;

        foreach ($this->buildFilters() as $filter) {
            $filter->apply();
        }
    }

    /**
     * Build filters based on provided user configuration.
     *
     * @return \Roots\Acorn\Support\Contracts\Filter[]
     */
    protected function buildFilters(): array
    {
        $filters = [];

        foreach ($this->getFilters() as $filter) {
            $filters[] = $this->makeFilter($filter);
        }

        return $filters;
    }

    /**
     * Get user filters configuration.
     *
     * @return string[]
     */
    protected function getFilters(): array
    {
        $filters = $this->app->config->get('filters', []);

        if (!is_array($filters)) {
            throw new InvalidArgumentException(
                sprintf('Filters configuration must be an array, [%s] given.', gettype($filters))
            );
        }

        return $filters;
    }

    /**
     * Make filter using container.
     *
     * @param string $filter
     *
     * @return \Roots\Acorn\Support\Contracts\Filter
     */
    protected function makeFilter(string $filter): Filter
    {
        return $this->app->make($filter);
    }
}
