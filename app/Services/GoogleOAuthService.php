<?php

namespace App\Services;

use Google\Client;
use Illuminate\Support\Facades\Session;
use Google\Service\Calendar as GoogleCalendar;
use GuzzleHttp\Client as GuzzleClient;

class GoogleOAuthService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();

        // config client Google
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $this->client->setAccessType('offline');
        $this->client->setIncludeGrantedScopes(true);
        $this->client->addScope(GoogleCalendar::CALENDAR);

        // client Guzzle + la désactivation de la vérification SSL
        $guzzleClient = new GuzzleClient([
            'curl' => [
                CURLOPT_SSL_VERIFYPEER => false,
            ],
        ]);
        $this->client->setHttpClient($guzzleClient);
    }

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function setAccessToken($code)
    {
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($code);
        $this->client->setAccessToken($accessToken);
        $accessTokenJson = json_encode($accessToken);
        Session::put('google_access_token', $accessTokenJson);

    }

    public function getAccessToken()
    {

      $accessToken = Session::get('google_access_token');
      $accessTokenData = json_decode($accessToken, true);
      $accessToken = $accessTokenData['access_token'];

        return $accessToken;
    }

    public function getClient()
    {
        $this->getAuthUrl();
        return $this->client;
    }

    public function getCalendarService()
    {
      $this->getClient();
        return new GoogleCalendar($this->client);
    }
}