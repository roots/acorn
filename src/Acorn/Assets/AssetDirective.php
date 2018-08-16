<?php

namespace Roots\Acorn\Assets;

class AssetDirective
{
    public function __invoke($expression)
    {
        return sprintf("<?= %s(%s); ?>", '\Roots\asset', $expression);
    }
}
