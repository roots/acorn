<?php

namespace Roots\Acorn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Roots\Acorn\Tests\VirtualFileSystem;

class ConfigTest extends TestCase
{
    use VirtualFileSystem;

    protected $fixtures = [
        'core/config/test.php' => "<?php return ['foo' => 'bar', 'biz' => 'baz'];",
        'parent/config/test.php' => "<?php return ['foo' => 'PARENT', 'biz' => 'baz'];",
        'child/config/test.php' => "<?php return ['foo' => 'CHILD',  'biz' => 'baz'];",
    ];

    /** @test */
    public function it_should_load_config_from_given_path()
    {
        $config = new \Roots\Acorn\Config();

        $config->load("{$this->filesystem}/core/config/test.php");

        $this->assertEquals($config['test.foo'], 'bar');
        $this->assertEquals($config['test.biz'], 'baz');
    }

    /** @test */
    public function it_should_prefer_to_load_config_from_optional_paths()
    {
        $parent = "{$this->filesystem}/parent/config";
        $child = "{$this->filesystem}/child/config";

        $config = new \Roots\Acorn\Config();
        $config->paths = [$parent];
        $config->load("{$this->filesystem}/core/config/test.php");
        $this->assertEquals($config['test.foo'], 'PARENT');

        $config = new \Roots\Acorn\Config();
        $config->paths = [$child, $parent];
        $config->load("{$this->filesystem}/core/config/test.php");
        $this->assertEquals($config['test.foo'], 'CHILD');
    }

    /** @test */
    public function it_should_override_previous_config_values()
    {
        $config = new \Roots\Acorn\Config();
        $parent = "{$this->filesystem}/parent/config";
        $child = "{$this->filesystem}/child/config";

        $config->load("{$this->filesystem}/core/config/test.php");
        $this->assertEquals($config['test.foo'], 'bar');

        $config->paths = [$parent];
        $config->load("{$this->filesystem}/core/config/test.php");
        $this->assertEquals($config['test.foo'], 'PARENT');

        $config->paths = [$child, $parent];
        $config->load("{$this->filesystem}/core/config/test.php");
        $this->assertEquals($config['test.foo'], 'CHILD');
    }
}
