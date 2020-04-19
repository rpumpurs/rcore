<?php

namespace RCore\Handlers;

use RCore\Exceptions\ConfigNotDefined;

class Envs
{
    private $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * @param string $name
     * @return string
     * @throws ConfigNotDefined
     */
    public function param(string $name): string
    {
        if (!isset($this->params[$name])) {
            throw new ConfigNotDefined($name);
        }

        return $this->params[$name];
    }
}