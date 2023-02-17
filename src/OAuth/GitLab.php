<?php

namespace RCore\OAuth;

use Exception;
use RCore\Exceptions\ConfigNotDefined;
use RCore\Handlers\Curl;
use RCore\Handlers\Envs;
use RCore\Handlers\SessionManager;
use RCore\Objects\User;
use Symfony\Component\HttpFoundation\Request;

class GitLab implements OAuth
{
    private static string $ENDPOINT_OAUTH_AUTHORIZE = '/oauth/authorize';
    private static string $ENDPOINT_OAUTH_TOKEN = '/oauth/token';

    private static string $REDIRECT_ENDPOINT = '/login/oauth_response';

    private static string $API_ENDPOINT = '/api/v4';

    private static string $ENDPOINT_AUTH_USER = '/user';

    private string $gitLabURL;

    private string$gitLabCIToolApplicationID;

    private string$gitLabCIToolApplicationSecret;

    private string $currentUrlBase;

    private string $authToken;

    private SessionManager $sessionManager;

    /**
     * @throws ConfigNotDefined
     */
    public function __construct(SessionManager $sessionManager, string $currentUrlBase, Envs $envs)
    {
        $this->sessionManager = $sessionManager;

        $this->currentUrlBase = $currentUrlBase;

        $this->gitLabURL = $envs->param('GITLAB_URL');
        $this->gitLabCIToolApplicationID = $envs->param('GITLAB_CI_TOOL_APPLICATION_ID');
        $this->gitLabCIToolApplicationSecret = $envs->param('GITLAB_CI_TOOL_APPLICATION_SECRET');
    }

    public function processOAuthResponse(Request $request): bool
    {
        if ($this->sessionManager->state() !== $request->get('state')) {
            return false;
        }

        if ($code = $request->get('code')) {
            $this->sessionManager->setAuthorizationCode($code);

            $this->sessionManager->setAuthorizationToken($this->getOAuthToken($this->sessionManager->authorizationCode()));

            $userData = $this->user();

            $this->sessionManager->setUser(new User(
                (int)$userData['id'], (string)$userData['name'], (string)$userData['email'], (string)$userData['avatar_url']
            ));

            return true;
        }

        return false;
    }

    /**
     * @throws Exception
     */
    public function getOAuthUrl(): string
    {
        $state = bin2hex(random_bytes(16));
        $this->sessionManager->setState($state);

        return $this->gitLabURL . self::$ENDPOINT_OAUTH_AUTHORIZE . '?' . http_build_query([
                'client_id' => $this->gitLabCIToolApplicationID,
                'redirect_uri' => $this->currentUrlBase . self::$REDIRECT_ENDPOINT,
                'response_type' => 'code',
                'state' => $state,
            ]);
    }

    public function getOAuthToken(string $code): string
    {
        list($response, $_) = Curl::call($this->gitLabURL . self::$ENDPOINT_OAUTH_TOKEN . '?' . http_build_query([
                'client_id' => $this->gitLabCIToolApplicationID,
                'client_secret' => $this->gitLabCIToolApplicationSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->currentUrlBase . self::$REDIRECT_ENDPOINT,
            ]), [], true);

        return $this->authToken = $response['access_token'];
    }

    public function user(): array
    {
        list($response, $_) = Curl::call($this->gitLabURL . self::$API_ENDPOINT . self::$ENDPOINT_AUTH_USER, [
            'Authorization: Bearer ' . $this->authToken,
        ]);

        return [
            'id' => $response['id'],
            'username' => $response['username'],
            'email' => $response['email'],
            'name' => $response['name'],
            'avatar_url' => $response['avatar_url'],
        ];
    }

    public function isAuthorized(): bool
    {
        if ($this->sessionManager->authorizationCode()) {
            if (!$this->sessionManager->authorizationToken()) {
                $this->sessionManager->setAuthorizationToken($this->getOAuthToken($this->sessionManager->authorizationCode()));
            }
        } else {
            return false;
        }

        return !empty($this->sessionManager->getVar('authorization_token'));
    }
}