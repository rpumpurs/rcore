<?php

namespace RCore\Handlers;

class ControllerConfig
{
    private string $templateFolder;

    public function __construct(Paths $paths)
    {
        $this->templateFolder = $paths->templateFolder();
    }

    public function templateFolder(): string
    {
        return $this->templateFolder;
    }
}