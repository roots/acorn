<?php

namespace Roots\Acorn;

use Illuminate\Foundation\AliasLoader as FoundationAliasLoader;

class AliasLoader extends FoundationAliasLoader
{
    /**
     * Register the loader on the auto-loader stack.
     *
     * @return void
     */
    public function register()
    {
        if ($this->registered) {
            return;
        }

        $this->registerFunctionAliases();

        $this->prependToLoaderStack();

        $this->registered = true;
    }

    /**
     * Registers global function aliases.
     *
     * @return void
     */
    protected function registerFunctionAliases()
    {
        require_once __DIR__ . '/../../globals.php';
    }
}
