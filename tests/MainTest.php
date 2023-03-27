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

    public function test_php_headers(): void
    {
        (new Main(
            new Paths('.env', ''), new RoutesTest())
        )->serve();

        $headers = xdebug_get_headers();

        $this->assertStringContainsString('PHPSESSID', $headers[0]);
        $this->assertStringContainsString('path=/;', $headers[0]);
        $this->assertStringContainsString('secure;', $headers[0]);
        $this->assertStringContainsString('HttpOnly;', $headers[0]);
        $this->assertStringContainsString('SameSite=lax', $headers[0]);
    }
}