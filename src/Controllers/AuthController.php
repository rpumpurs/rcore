<?php

namespace RCore\Controllers;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthController extends ControllerBase
{
    protected $title = 'Login';

    protected $authRequired = false;

    public function login()
    {
        if ($this->sessionManager->isAuthorized()) {
            return new RedirectResponse('/');
        }

        $state = bin2hex(random_bytes(16));
        $this->sessionManager->setState($state);

        $oAuthUrl = $this->OAuth->getOAuthUrl($state);

        return $this->render('login.twig', compact('oAuthUrl'));
    }

    public function processOAuthResponse(Request $request)
    {
        /*if ($this->sessionManager->state() !== $request->get('state')) {
            $this->sessionManager->setFlashErrorMessage('Login error, please contact the admin');
            return new RedirectResponse('/');
        }

        $this->sessionManager->setAuthorizationCode($request->get('code'));
        */

        if (!$this->OAuth->processOAuthResponse($request)) {
            $this->sessionManager->setFlashErrorMessage('Login error, please contact the admin');
        }

        return new RedirectResponse('/');
    }

    public function logout()
    {
        $this->sessionManager->logout();

        return new RedirectResponse('/login');
    }
}