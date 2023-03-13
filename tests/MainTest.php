<?php declare(strict_types=1);

namespace RTests;

use PHPUnit\Framework\TestCase;
use RCore\Handlers\Paths;
use RCore\Main;

final class MainTest extends TestCase
{
    public function test_contents(): void
    {
        ob_start();

        (new Main(
            new Paths('.env', ''), new RoutesTest())
        )->serve();

        $output = ob_get_clean();

        $this->assertEquals('test', $output);
    }
}