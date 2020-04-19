<?php

namespace RCore\Handlers;

class Config
{
    /**
     * @var Envs
     */
    private $envs;
    /**
     * @var Paths
     */
    private $paths;
    /**
     * @var Routes
     */
    private $routes;

    public function __construct(Envs $envs, Paths $paths, Routes $routes)
    {
        $this->envs = $envs;
        $this->paths = $paths;
        $this->routes = $routes;
    }

    public function getEnvs(): Envs
    {
        return $this->envs;
    }

    public function getPaths(): Paths
    {
        return $this->paths;
    }

    public function getRoutes(): Routes
    {
        return $this->routes;
    }
}