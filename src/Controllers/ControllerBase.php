<?php

namespace RCore\Controllers;

use RCore\Handlers\ControllerConfig;
use RCore\Handlers\Envs;
use RCore\Handlers\SessionManager;
use RCore\Handlers\Url;
use RCore\OAuth\GitLab;
use RCore\OAuth\OAuth;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ControllerBase
{
    protected $applicationName;

    protected $title = 'Index';

    protected $authRequired = true;

    /**
     * @var ControllerConfig
     */
    protected $config;

    /**
     * @var Envs
     */
    protected $envs;

    /**
     * @var OAuth
     */
    protected $OAuth;

    /**
     * @var SessionManager
     */
    protected $sessionManager;

    public function injectDependencies(ControllerConfig $config, Envs $envs, SessionManager $sessionManager)
    {
        $this->config = $config;
        $this->envs = $envs;
        $this->sessionManager = $sessionManager;
    }

    /**
     * @throws \RCore\Exceptions\ConfigNotDefined
     */
    public function runPreController()
    {
        $this->applicationName = $this->envs->param('APPLICATION_NAME');

        $this->OAuth = new GitLab(
            $this->sessionManager,
            Url::resolveCurrentBase(),
            $this->envs
        );

        /*if ($this->authRequired) {
            if (!$this->sessionManager->isAuthorized() || $this->sessionManager->isExpired()) {
                $this->sessionManager->logout();
                $this->sessionManager->setFlashErrorMessage('Session expired, please log in again');
                (new RedirectResponse('/login'))->send();
            }
        }*/

        if ($this->authRequired) {
            if (!$this->OAuth->isAuthorized() || $this->sessionManager->isExpired()) {
                $this->sessionManager->logout();
                $this->sessionManager->setFlashErrorMessage('Session expired, please log in again');
                (new RedirectResponse('/login'))->send();
            }
        }

        /*$this->OAuth = new GitLab(
            Url::resolveCurrentBase(),
            $this->envs
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
        }*/

        $this->sessionManager->setLastActivity();
    }

    public function index()
    {
        return $this->render('index.twig');
    }

    public function render($template, $vars = [])
    {
        $loader = new FilesystemLoader($this->config->templateFolder());
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