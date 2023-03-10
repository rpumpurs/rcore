<?php

namespace RCore;

use Exception;
use Metricize\Metricize;
use RCore\Controllers\ControllerBase;
use RCore\Exceptions\ConfigNotDefined;
use RCore\Handlers\ControllerConfig;
use RCore\Handlers\Envs;
use RCore\Handlers\Paths;
use RCore\Handlers\Routes\Routes;
use RCore\Handlers\SessionManager;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Dotenv\Exception\PathException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class Main
{
    private Paths $paths;
    /**
     * @var Routes
     */
    private Routes $routes;

    public function __construct(Paths $paths, Routes $routes)
    {
        $this->paths = $paths;
        $this->routes = $routes;
    }

    public function serve(): void
    {
        $dotEnv = new Dotenv();
        try {
            $dotEnv->load($this->paths->envFile());
        } catch (PathException $e) {
            $response = new Response('.env file missing [' . $e->getMessage() . ']', 503);
            $response->send();
        }

        $request = Request::createFromGlobals();

        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->routes->getCollection(), $context);

        $controllerResolver = new ControllerResolver();
        $argumentResolver = new ArgumentResolver();

        try {
            $parameters = $matcher->match($request->getPathInfo());
            $request->attributes->add($parameters);
            $controllerCallable = $controllerResolver->getController($request);
            /** @var ControllerBase $controllerObject */
            $controllerObject = $controllerCallable[0];
            $controllerObject->injectDependencies(
                new ControllerConfig($this->paths),
                new Envs($_ENV),
                new SessionManager(),
                new Metricize(['host' => 'rcore_metricize_redis', 'port' => 6379, 'auto_commit' => true])
                );
            $controllerObject->runPreController();
            $arguments = $argumentResolver->getArguments($request, $controllerCallable);
            $response = call_user_func_array($controllerCallable, $arguments);
        } catch (ResourceNotFoundException $e) {
            $response = new Response('Not Found [' . $e->getMessage() . ']', 404);
        } catch (ConfigNotDefined $e) {
            $response = new Response('Application not configured. Missing [' . $e->getMessage() . ']', 503);
        } catch (Exception $e) {
            $response = new Response('An error occurred [' . $e->getMessage() . ']', 500);
        }

        $response->send();
    }
}