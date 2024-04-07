<?php

use App\Interfaces\ProviderInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
abstract class AbstractProvider implements ProviderInterface
{
    protected $providerName;
    protected $request;
    protected $httpClient;
    protected $clientId;
    protected $clientSecret;
    protected $redirectUrl;
    protected $scopes = [];
    protected $scopeSeparator = ' ';
    protected $user;

    /**
     * Create a new provider instance.
     */
    public function __construct(Request $request, string $clientId, string $clientSecret, string $redirectUrl, array $scopes = [])
    {
        $this->request = $request;
        $this->clientId = $clientId;
        $this->redirectUrl = $redirectUrl;
        $this->clientSecret = $clientSecret;
        $this->scopes = $scopes;
    }

    /**
     * @return RedirectResponse
     * @throws \Exception
     */
    public function redirect(): RedirectResponse
    {
        $this->request->query->add(['state' => $this->getState()]);

        if ($user = $this->request->user()) {
            $this->request->query->add(['user_id' => $user->getKey()]);
        }

        return new RedirectResponse($this->createAuthUrl());
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        if (isset($this->user)) {
            return $this->user;
        }

        try {
            $credentials = $this->fetchAccessTokenWithAuthCode(
                $this->request->get('code', '')
            );

            $this->user = $this->toUser($this->getBasicProfile($credentials));
        } catch (\Exception $exception) {
            report($exception);
            throw new \InvalidArgumentException($exception->getMessage());
        }

        $state = $this->request->get('state', '');

        if (isset($state)) {
            $state = Crypt::decrypt($state);
        }

        return $this->user
            ->setRedirectCallback($state['redirect_callback'])
            ->setToken($credentials['access_token'])
            ->setRefreshToken($credentials['refresh_token'])
            ->setExpiresAt(
                Carbon::now()->addSeconds($credentials['expires_in'])
            )
            ->setScopes(
                explode($this->getScopeSeparator(), $credentials['scope'])
            );
    }

    abstract protected function createAuthUrl();
    abstract protected function fetchAccessTokenWithAuthCode(string $code);
    abstract protected function getBasicProfile($credentials);
    abstract protected function toUser($userProfile);
}