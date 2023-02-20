<?php

namespace RCore\Controllers;

use RCore\Exceptions\ConfigNotDefined;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class AuthController extends ControllerBase
{
    protected $title = 'Login';

    protected $authRequired = false;

    public function login(): Response
    {
        if ($this->sessionManager->isAuthorized()) {
            return new RedirectResponse('/');
        }

        return $this->render('login.twig');
    }

    /**
     * @param $using
     * @return RedirectResponse
     * @throws ConfigNotDefined
     */
    public function loginRedirect($using): RedirectResponse
    {
        if ($this->sessionManager->isAuthorized()) {
            return new RedirectResponse('/');
        }

        $redirectUrl = $this->resolveAuth($using)->getOAuthUrl();

        return new RedirectResponse($redirectUrl);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ConfigNotDefined
     */
    public function processOAuthResponse(Request $request): RedirectResponse
    {
        if (!$this->resolveAuth()->processOAuthResponse($request)) {
            $this->sessionManager->setFlashErrorMessage('Login error, please contact the admin');
        }

        return new RedirectResponse('/');
    }

    public function logout(): RedirectResponse
    {
        $this->sessionManager->logout();

        return new RedirectResponse('/login');
    }
}