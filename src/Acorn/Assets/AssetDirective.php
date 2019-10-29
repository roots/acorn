<?php

namespace Roots\Acorn\Assets;

class AssetDirective
{
    /**
     * Invoke the @asset directive.
     *
     * @param  string $expression
     * @return string
     */
    public function __invoke($expression)
    {
        return sprintf("<?= %s(%s); ?>", '\Roots\asset', $expression);
    }
}
