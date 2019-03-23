<?php

namespace Roots\Acorn\Tests;

use org\bovigo\vfs\vfsStream;

/**
 * @codeCoverageIgnore
 */
trait VirtualFileSystem
{
    protected $filesystem;

    public function setUp() : void
    {
        $this->filesystem = new class ($this->fixtures()) {
            /** @var \org\bovigo\vfs\vfsStreamDirectory */
            protected $stream;

            public function __construct($fixtures = [])
            {
                $this->stream = vfsStream::setup('__fixtures__', null, $fixtures);
            }

            public function __call($name, $arguments)
            {
                return $this->stream->{$name}(...$arguments);
            }

            public function __get($name)
            {
                return $this->stream->{$name};
            }

            public function __toString()
            {
                return $this->stream->url();
            }
        };
    }

    protected function write($file, $contents)
    {
        $file = str_replace('\\', '/', $file);
        $file = ltrim($file, '/');

        $file = "{$this->filesystem}/{$file}";

        file_put_contents($file, $contents);

        return $file;
    }

    protected function writeDump($file, $data)
    {
        $contents = var_export($data, true);
        return $this->write($file, $contents);
    }

    protected function fixtures()
    {
        $filesystem = [];

        foreach ($this->fixtures ?? [] as $file => $content) {
            $limbs = array_reverse(array_filter(explode('/', $file)));
            $filesystem = array_merge_recursive(array_reduce($limbs, function ($leaf, $limb) {
                return [$limb => $leaf];
            }, $content), $filesystem);
        }

        return $filesystem;
    }
}
