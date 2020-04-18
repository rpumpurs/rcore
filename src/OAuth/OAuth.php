<?php

namespace RCore\OAuth;

interface OAuth
{
    public function getOAuthUrl(string $state): string;

    public function getOAuthToken(string $code): string;

    public function user(): array;
}