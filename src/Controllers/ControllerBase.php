<?php

namespace RCore\Controllers;

use Exception;
use Metricize\Metricize;
use RCore\Exceptions\ConfigNotDefined;
use RCore\Handlers\ControllerConfig;
use RCore\Handlers\Envs;
use RCore\Handlers\SessionManager;
use RCore\Handlers\Url;
use RCore\OAuth\GitLab;
use RCore\OAuth\Google;
use RCore\OAuth\OAuth;
use RCore\OAuth\Undecided;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ControllerBase
{
    protected string $applicationName;

    protected string $title = 'Index';

    protected bool $authRequired = true;

    protected ControllerConfig $config;
    protected Envs $envs;
    protected OAuth $OAuth;
    protected SessionManager $sessionManager;
    protected Metricize $metricize;

    public function injectDependencies(ControllerConfig $config, Envs $envs, SessionManager $sessionManager, Metricize $metricize): void
    {
        $this->config = $config;
        $this->envs = $envs;
        $this->sessionManager = $sessionManager;
        $this->metricize = $metricize;
    }

    /**
     * @throws ConfigNotDefined
     */
    public function runPreController()
    {
        $this->applicationName = $this->envs->param('APPLICATION_NAME');

        if ($this->authRequired) {
            if (!$this->resolveAuth()->isAuthorized() || $this->sessionManager->isExpired()) {
                $this->sessionManager->logout();
                $this->sessionManager->setFlashErrorMessage('Session expired, please log in again');
                (new RedirectResponse('/login'))->send();
            }
        }

        $this->sessionManager->setLastActivity();
    }

    /**
     * @param string|null $using
     * @return OAuth
     * @throws ConfigNotDefined
     * @throws Exception
     */
    protected function resolveAuth(string $using = null): OAuth
    {
        if ($using) {
            $this->sessionManager->setVar('oauthHandler', $using);
        }

        switch ($this->sessionManager->getVar('oauthHandler')) {
            case 'gitlab':
                $auth = new GitLab(
                    $this->sessionManager,
                    Url::resolveCurrentBase(),
                    $this->envs
                );
                break;

            case 'google':
                $auth = new Google(
                    $this->sessionManager,
                    Url::resolveCurrentBase(),
                    $this->envs
                );
                break;
            default:
                $auth = new Undecided();
        }

        return $auth;
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