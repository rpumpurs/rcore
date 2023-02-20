<?php

namespace RTests;

use RCore\Controllers\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class TestController extends ControllerBase
{
    protected $title = 'Test';

    protected $authRequired = false;

    public function index(): Response
    {
        return new Response('test');
    }
}