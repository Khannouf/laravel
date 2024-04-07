<?php

use Illuminate\Support\Manager;
use App\Interfaces\ProviderInterface;

class CalendarManager extends Manager  
{

  public function getDefaultDriver()
    {
        // Implémentez la logique pour retourner le pilote par défaut
        return 'google'; // Par exemple, retourner le pilote Google par défaut
    }
    protected function createGoogleDriver(): ProviderInterface
    {
        $config = $this->config->get('services.google');

        return $this->buildProvider(GoogleProvider::class, $config);
    }

    protected function buildProvider($provider, $config): ProviderInterface
    {
        return new $provider(
            $this->container->make('request'),
            $config['client_id'],
            $config['client_secret'],
            $config['redirect_uri'],
            $config['scopes']
        );
    }
}