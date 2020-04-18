<?php

namespace RCore\Handlers;

use RCore\Controllers\ControllerBase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Routes
{
    public function resolve(): RouteCollection
    {
        $routes = new RouteCollection();

        $route = new Route('/', [
            '_controller' => '\RCore\Controllers\ControllerBase::index',
        ]);
        $routes->add('home', $route);

        $route = new Route('/login', [
            '_controller' => '\RCore\Controllers\AuthController::login',
        ]);
        $routes->add('login', $route);

        $route = new Route('/logout', [
            '_controller' => '\RCore\Controllers\AuthController::logout',
        ]);
        $routes->add('logout', $route);

        $route = new Route('/login/oauth_response', [
            '_controller' => '\RCore\Controllers\AuthController::processOAuthResponse',
        ]);
        $routes->add('oauth_response', $route);

        return $routes;
    }

    protected function additionalRoutes(RouteCollection $routes)
    {
        return $routes;
    }
}