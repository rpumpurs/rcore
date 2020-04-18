<?php

namespace RCore\Handlers;

class Url
{
    public static function resolveCurrentBase()
    {
        return sprintf(
            "%s://%s",
            isset($_SERVER['HTTP_HTTPS']) && $_SERVER['HTTP_HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['HTTP_HOST']
        );
    }
}