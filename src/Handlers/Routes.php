<?php

namespace RCore\Handlers;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Routes
{
    private ?RouteCollection $additionalRoutes = null;

    public function resolve(): RouteCollection
    {
        if ($this->additionalRoutes) {
            return $this->additionalRoutes;
        }

        $routes = new RouteCollection();

        $route = new Route('/', [
            '_controller' => '\RCore\Controllers\ControllerBase::index',
        ]);
        $routes->add('home', $route);

        $route = new Route('/login', [
            '_controller' => '\RCore\Controllers\AuthController::login',
        ]);
        $routes->add('login', $route);

        $route = new Route('/login_redirect/{using}', [
            '_controller' => '\RCore\Controllers\AuthController::loginRedirect',
        ]);
        $routes->add('login_redirect', $route);

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

    public function additionalRoutes(RouteCollection $routes): void
    {
        $this->additionalRoutes = $routes;
    }
}