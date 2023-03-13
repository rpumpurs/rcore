<?php

namespace RCore\Controllers;

use Prometheus\RenderTextFormat;
use Symfony\Component\HttpFoundation\Response;

class MetricsController extends ControllerBase
{
    protected string $title = 'Metrics';

    protected bool $authRequired = false;

    public function provide(): Response
    {
        return new Response($this->metricize->provideMetrics(),
            200,
            ['Content-type' => RenderTextFormat::MIME_TYPE]
        );
    }
}