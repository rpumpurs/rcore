<?php

namespace RCore\Handlers\Routes;

use Symfony\Component\Routing\RouteCollection;

interface Routes
{
    public function getCollection(): RouteCollection;
}