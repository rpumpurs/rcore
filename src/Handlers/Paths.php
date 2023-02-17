<?php

namespace RCore\Handlers;

class Paths
{
    private string $envFile;
    private string $templateFolder;

    public function __construct(string $envFile, string $templateFolder)
    {
        $this->envFile = $envFile;
        $this->templateFolder = $templateFolder;
    }

    public function envFile(): string
    {
        return $this->envFile;
    }

    public function templateFolder(): string
    {
        return $this->templateFolder;
    }
}