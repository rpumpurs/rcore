<?php

namespace RCore;

use Exception;
use RCore\Exceptions\ConfigNotDefined;
use RCore\Handlers\Paths;
use RCore\Handlers\Routes;
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
        $_ENV['TEMPLATE_FOLDER'] = $this->paths->templateFolder(); // TODO figure out how to pass to ControllerBase constructor

        $request = Request::createFromGlobals();

        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->routes->resolve(), $context);

        $controllerResolver = new ControllerResolver();
        $argumentResolver = new ArgumentResolver();

        try {
            $parameters = $matcher->match($request->getPathInfo());
            $request->attributes->add($parameters);
            $controller = $controllerResolver->getController($request);
            $arguments = $argumentResolver->getArguments($request, $controller);
            $response = call_user_func_array($controller, $arguments);
        } catch (ResourceNotFoundException $exception) {
            $response = new Response('Not Found', 404);
        } catch (ConfigNotDefined $e) {
            $response = new Response('Application not configured. Missing [' . $e->getMessage() . ']', 503);
        } catch (Exception $exception) {
            $response = new Response('An error occurred', 500);
        }

        $response->send();
    }
}