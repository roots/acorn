<?php

namespace Roots\Acorn\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as FoundationHandler;
use Throwable;

class Handler extends FoundationHandler
{
    /**
     * Get the default context variables for logging.
     *
     * @return array
     */
    protected function context()
    {
        try {
            return array_filter([
                'userId' => get_current_user_id(),
            ]);
        } catch (Throwable $e) {
            return [];
        }
    }
}
