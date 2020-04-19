<?php

namespace RCore\Controllers;

use Exception;
use RCore\Exceptions\ConfigNotDefined;
use RCore\Handlers\ControllerConfig;
use RCore\Handlers\Envs;
use RCore\Handlers\SessionManager;
use RCore\Handlers\Url;
use RCore\OAuth\GitLab;
use RCore\OAuth\Google;
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
                throw new Exception('Unexpected auth handler');
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