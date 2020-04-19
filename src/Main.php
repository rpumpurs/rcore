<?php

namespace RCore;

use Exception;
use RCore\Controllers\ControllerBase;
use RCore\Exceptions\ConfigNotDefined;
use RCore\Handlers\ControllerConfig;
use RCore\Handlers\Envs;
use RCore\Handlers\Paths;
use RCore\Handlers\Routes;
use RCore\Handlers\SessionManager;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class Main
{
    private $paths;
    /**
     * @var Routes
     */
    private $routes;

    public function __construct(Paths $paths, Routes $routes)
    {
        $this->paths = $paths;
        $this->routes = $routes;
    }

    public function serve()
    {
        $dotEnv = new Dotenv();
        $dotEnv->load($this->paths->envFile());

        $request = Request::createFromGlobals();

        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->routes->resolve(), $context);

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
                );
            $controllerObject->runPreController();
            $arguments = $argumentResolver->getArguments($request, $controllerCallable);
            $response = call_user_func_array($controllerCallable, $arguments);
        } catch (ResourceNotFoundException $exception) {
            $response = new Response('Not Found', 404);
        } catch (ConfigNotDefined $e) {
            $response = new Response('Application not configured. Missing [' . $e->getMessage() . ']', 503);
        } catch (Exception $exception) {
            var_dump($exception);
            $response = new Response('An error occurred', 500);
        }

        $response->send();
    }
}