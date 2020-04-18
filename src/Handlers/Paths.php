<?php

namespace RCore\Handlers;

class Paths
{
    private $envFile;
    private $templateFolder;

    public function __construct(string $envFile, string $templateFolder)
    {
        $this->envFile = $envFile;
        $this->templateFolder = $templateFolder;
    }

    public function envFile()
    {
        return $this->envFile;
    }

    public function templateFolder(): string
    {
        return $this->templateFolder;
    }
}