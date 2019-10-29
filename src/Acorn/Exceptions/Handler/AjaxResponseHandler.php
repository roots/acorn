<?php

namespace Roots\Acorn\Exceptions\Handler;

use Whoops\Exception\Formatter;
use Whoops\Handler\Handler;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Util\Misc;

class AjaxResponseHandler extends JsonResponseHandler
{
    /**
     * Return Frames
     *
     * @var boolean
     */
    protected $returnFrames = true;

    /**
     * Handle the request.
     *
     * @return void
     */
    public function handle()
    {
        if (Misc::canSendHeaders()) {
            status_header(500);
            header('Content-Type: application/json; charset=utf-8');
        }

        echo wp_json_encode([
            'success' => false,
            'data' => Formatter::formatExceptionAsDataArray($this->getInspector(), $this->addTraceToOutput()),
        ], JSON_PRETTY_PRINT);

        return Handler::QUIT;
    }
}
