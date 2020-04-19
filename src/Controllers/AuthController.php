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

        return $this->render('login.twig');
    }

    /**
     * @param $using
     * @return RedirectResponse
     * @throws \RCore\Exceptions\ConfigNotDefined
     */
    public function loginRedirect($using)
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
     * @throws \RCore\Exceptions\ConfigNotDefined
     */
    public function processOAuthResponse(Request $request)
    {
        if (!$this->resolveAuth()->processOAuthResponse($request)) {
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