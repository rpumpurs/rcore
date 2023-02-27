<?php

namespace RCore\Handlers\Routes;

use RCore\Controllers\AuthController;
use RCore\Controllers\ControllerBase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RoutesBase implements Routes
{
    public function getCollection(): RouteCollection
    {
        $routes = new RouteCollection();

        $routes->add('home', new Route('/', ['_controller' => [
            ControllerBase::class,
            'index'
        ]]));
        $routes->add('login', new Route('/login', ['_controller' => [
            AuthController::class,
            'login'
        ]]));
        $routes->add('login_redirect', new Route('/login_redirect/{using}', ['_controller' => [
            AuthController::class,
            'loginRedirect'
        ]]));
        $routes->add('logout', new Route('/logout', ['_controller' => [
            AuthController::class,
            'logout'
        ]]));
        $routes->add('oauth_response', new Route('/login/oauth_response', ['_controller' => [
            AuthController::class,
            'processOAuthResponse'
        ]]));

        return $routes;
    }
}