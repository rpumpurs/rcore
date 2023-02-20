<?php declare(strict_types=1);

namespace RTests;

use PHPUnit\Framework\TestCase;
use RCore\Handlers\Paths;
use RCore\Handlers\Routes;
use RCore\Main;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class MainTest extends TestCase
{
    public function testCanBeCreatedFromValidEmailAddress(): void
    {
        ob_start();

        $testRoutes = new RouteCollection();

        $route = new Route('/', [
            '_controller' => 'RTests\TestController::index',
        ]);
        $testRoutes->add('test', $route);

        $routes = new Routes();
        $routes->additionalRoutes($testRoutes);

        (new Main(
            new Paths('.env', ''), $routes)
        )->serve();

        $output = ob_get_clean();

        $this->assertEquals('test', $output);
    }
}