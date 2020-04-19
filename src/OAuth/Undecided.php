<?php

namespace RCore\OAuth;

use Exception;
use Symfony\Component\HttpFoundation\Request;

class Undecided implements OAuth
{
    /**
     * @param Request $request
     * @return bool
     * @throws Exception
     */
    public function processOAuthResponse(Request $request): bool
    {
        throw new Exception('Should not called');
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getOAuthUrl(): string
    {
        throw new Exception('Should not called');
    }

    /**
     * @param string $code
     * @return string
     * @throws Exception
     */
    public function getOAuthToken(string $code): string
    {
        throw new Exception('Should not called');
    }

    public function user(): array
    {
        return [];
    }

    public function isAuthorized(): bool
    {
        return false;
    }
}