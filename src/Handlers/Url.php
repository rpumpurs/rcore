<?php

namespace RCore\Handlers;

class Url
{
    public static function resolveCurrentBase()
    {
        return sprintf(
            "%s://%s",
            'https',
            $_SERVER['HTTP_HOST']
        );
    }
}