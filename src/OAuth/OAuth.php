<?php

namespace RCore\OAuth;

use Symfony\Component\HttpFoundation\Request;

interface OAuth
{
    public function processOAuthResponse(Request $request): bool;

    public function getOAuthUrl(): string;

    public function getOAuthToken(string $code): string;

    public function user(): array;

    public function isAuthorized(): bool;
}