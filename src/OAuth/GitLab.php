<?php

namespace RCore\OAuth;

use RCore\Handlers\Envs;
use RCore\Handlers\Curl;

class GitLab implements OAuth
{
    private static $ENDPOINT_OAUTH_AUTHORIZE = '/oauth/authorize';
    private static $ENDPOINT_OAUTH_TOKEN = '/oauth/token';

    private static $REDIRECT_ENDPOINT = '/login/oauth_response';

    private static $API_ENDPOINT = '/api/v4';

    private static $ENDPOINT_AUTH_USER = '/user';

    /**
     * @var string
     */
    private $gitLabURL;
    /**
     * @var string
     */
    private $gitLabCIToolApplicationID;
    /**
     * @var string
     */
    private $gitLabCIToolApplicationSecret;
    /**
     * @var string
     */
    private $currentUrlBase;
    /**
     * @var string
     */
    private $authToken;

    /**
     * GitLab constructor.
     * @param string $currentUrlBase
     * @param Envs $envs
     * @throws \RCore\Exceptions\ConfigNotDefined
     */
    public function __construct(string $currentUrlBase, Envs $envs)
    {
        $this->gitLabURL = $envs->param('GITLAB_URL');
        $this->gitLabCIToolApplicationID = $envs->param('GITLAB_CI_TOOL_APPLICATION_ID');
        $this->gitLabCIToolApplicationSecret = $envs->param('GITLAB_CI_TOOL_APPLICATION_SECRET');

        $this->currentUrlBase = $currentUrlBase;
    }

    public function getOAuthUrl(string $state): string
    {
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
            'username' => $response['username'],
            'email' => $response['email'],
            'name' => $response['name'],
            'avatar_url' => $response['avatar_url'],
        ];
    }
}