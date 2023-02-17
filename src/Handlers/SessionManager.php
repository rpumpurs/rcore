<?php

namespace RCore\Handlers;

use RCore\Objects\User;

class SessionManager
{
    static int $SESSION_EXPIRE_TIME = 3 * 60 * 60; // 3h

    public function __construct()
    {
        session_start();
    }

    public function setState(string $state): void
    {
        $_SESSION['state'] = $state;
    }

    public function setAuthorizationCode(string $code): void
    {
        $_SESSION['authorization_code'] = $code;
    }

    public function setAuthorizationToken(string $token): void
    {
        $_SESSION['authorization_token'] = $token;
    }

    public function setUser(User $user): void
    {
        $_SESSION['user'] = $user;
    }

    public function state(): ?string
    {
        return $_SESSION['state'] ?? null;
    }

    public function authorizationCode(): ?string
    {
        return $_SESSION['authorization_code'] ?? null;
    }

    public function authorizationToken(): ?string
    {
        return $_SESSION['authorization_token'] ?? null;
    }

    public function user(): ?User
    {
        return $_SESSION['user'] ?? null;
    }

    public function logout(): void
    {
        session_destroy();
        session_start();
    }

    public function setFlashErrorMessage(string $message): void
    {
        $_SESSION['flash_error_message'] = $message;
    }

    public function getFlashErrorMessage(): ?string
    {
        $message = $_SESSION['flash_error_message'] ?? null;
        unset($_SESSION['flash_error_message']);

        return $message;
    }

    public function setFlashSuccessMessage(string $message): void
    {
        $_SESSION['flash_success_message'] = $message;
    }

    public function getFlashSuccessMessage(): ?string
    {
        $message = $_SESSION['flash_success_message'] ?? null;
        unset($_SESSION['flash_success_message']);

        return $message;
    }

    public function setLastActivity(): void
    {
        $_SESSION['last_activity'] = time();
    }

    public function isExpired(): int
    {
        return $_SESSION['last_activity'] < (time() - self::$SESSION_EXPIRE_TIME);
    }

    public function isAuthorized(): bool
    {
        return !empty($_SESSION['authorization_token']);
    }

    public function setVar(string $name, $value): void
    {
        $_SESSION[$name] = $value;
    }

    public function getVar(string $name): ?string
    {
        return $_SESSION[$name] ?? null;
    }
}