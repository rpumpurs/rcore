<?php

namespace RTests;

use RCore\Controllers\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class TestController extends ControllerBase
{
    protected string $title = 'Test';

    protected bool $authRequired = false;

    public function index(): Response
    {
        return new Response('test');
    }
}