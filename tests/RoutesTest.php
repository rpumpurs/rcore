<?php

namespace RTests;

use RCore\Handlers\Routes\Routes;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RoutesTest implements Routes
{
    public function getCollection(): RouteCollection
    {
        $testRoutes = new RouteCollection();

        $testRoutes->add('test', new Route('/', ['_controller' => [TestController::class, 'index']]));

        return $testRoutes;
    }
}