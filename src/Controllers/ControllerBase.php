<?php

namespace RCore\Controllers;

use RCore\Handlers\Config;
use RCore\Handlers\SessionManager;
use RCore\Handlers\Url;
use RCore\OAuth\GitLab;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ControllerBase
{
    protected $applicationName;

    protected $title = 'Index';

    protected $authRequired = true;

    protected $OAuth;

    protected $sessionManager;

    protected $config;

    /**
     * ControllerBase constructor.
     * @throws \RCore\Exceptions\ConfigNotDefined
     */
    public function __construct()
    {
        $this->config = new Config($_ENV);
        $this->applicationName = $this->config->param('APPLICATION_NAME');

        $this->sessionManager = new SessionManager();

        $this->OAuth = new GitLab(
            Url::resolveCurrentBase(),
            $this->config
        );

        if ($this->authRequired) {
            if ($this->sessionManager->authorizationCode()) {
                if ($this->sessionManager->isExpired()) {
                    $this->sessionManager->logout();
                    $this->sessionManager->setFlashErrorMessage('Session expired, please log in again');
                    (new RedirectResponse('/login'))->send();
                    die();
                }

                if (!$this->sessionManager->authorizationToken()) {
                    $this->sessionManager->setAuthorizationToken($this->OAuth->getOAuthToken($this->sessionManager->authorizationCode()));
                }
            } else {
                (new RedirectResponse('/login'))->send();
                die();
            }

            if (!$this->sessionManager->user()) {
                $this->sessionManager->setUser($this->OAuth->user());
            }
        }

        $this->sessionManager->setLastActivity();
    }

    public function index()
    {
        return $this->render('index.twig');
    }

    public function render($template, $vars = [])
    {
        $loader = new FilesystemLoader($_ENV['TEMPLATE_FOLDER']);
        $twig = new Environment($loader);
        $vars = array_merge($vars, [
                'applicationName' => $this->applicationName,
                'title' => $this->title,
                'user' => $this->sessionManager->user(),
                'flashErrorMessage' => $this->sessionManager->getFlashErrorMessage(),
                'flashSuccessMessage' => $this->sessionManager->getFlashSuccessMessage(),
            ]
        );

        return new Response($twig->render($template, $vars));
    }
}