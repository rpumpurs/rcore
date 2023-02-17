<?php

namespace RCore\OAuth;

use Google_Client;
use Google_Service_Oauth2;
use RCore\Exceptions\ConfigNotDefined;
use RCore\Handlers\Curl;
use RCore\Handlers\Envs;
use RCore\Handlers\SessionManager;
use RCore\Objects\User;
use Symfony\Component\HttpFoundation\Request;

class Google implements OAuth
{
    private static string $ENDPOINT_OAUTH_AUTHORIZE = '/oauth/authorize';
    private static string $ENDPOINT_OAUTH_TOKEN = '/oauth/token';

    private static string $REDIRECT_ENDPOINT = '/login/oauth_response';

    private static string $API_ENDPOINT = '/api/v4';

    private static string $ENDPOINT_AUTH_USER = '/user';

    private string $gitLabURL;

    private string $clientId;

    private string $apiKey;

    private string $clientSecret;

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

        $this->clientId = $envs->param('GOOGLE_CLIENT_ID');
        $this->clientSecret = $envs->param('GOOGLE_CLIENT_SECRET');
        $this->apiKey = $envs->param('GOOGLE_API_KEY');
    }

    private function initClient(): Google_Client
    {
        $client = new Google_Client();
        $client->setApplicationName("RCore");
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setRedirectUri($this->currentUrlBase . self::$REDIRECT_ENDPOINT);
        $client->setDeveloperKey($this->apiKey);
        $client->addScope("https://www.googleapis.com/auth/userinfo.email");
        $client->addScope("https://www.googleapis.com/auth/userinfo.profile");
        if ($this->sessionManager->authorizationToken()) {
            $client->setAccessToken($this->sessionManager->authorizationToken());
        }

        return $client;
    }

    public function getOAuthUrl(): string
    {
        $client = $this->initClient();

        return $client->createAuthUrl();
    }

    public function processOAuthResponse(Request $request): bool
    {
        $client = $this->initClient();

        if ($code = $request->get('code')) {
            $accessToken = $client->fetchAccessTokenWithAuthCode($code);
            $this->sessionManager->setAuthorizationToken($accessToken['access_token']);

            $objOAuthService = new Google_Service_Oauth2($client);
            $userData = $objOAuthService->userinfo->get();

            $this->sessionManager->setUser(new User(
                (int)$userData['id'], (string)$userData['name'], (string)$userData['email'], (string)$userData['picture']
            ));

            return true;
        }

        return false;
    }

    public function getOAuthToken(string $code): string
    {
        list($response, $_) = Curl::call($this->gitLabURL . self::$ENDPOINT_OAUTH_TOKEN . '?' . http_build_query([
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
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

    public function isAuthorized(): bool
    {
        return !empty($this->sessionManager->getVar('authorization_token'));
    }
}