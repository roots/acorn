<?php

namespace Roots\Acorn\Tests\Unit\TestDouble;

use Symfony\Component\Console\Output\Output;

final class OutputStub extends Output
{
    /** @var string[] */
    private $written = [];

    protected function doWrite(string $message, bool $newline)
    {
        $this->written[] = $message;
    }

    public function getWrittenMessages(): array
    {
        return $this->written;
    }
}
