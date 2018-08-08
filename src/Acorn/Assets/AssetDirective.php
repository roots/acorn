<?php

namespace Roots\Acorn\Assets;

class AssetDirective
{
    public function __invoke($asset)
    {
        return sprintf("<?= %s('%s'); ?>", '\Roots\asset', $asset);
    }
}
