<?php

namespace App\Services;

use Google\Client;
use \App\Services\GoogleOAuthService;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;

class GoogleCalendarService
{
  protected $client;
  protected $calendar;

  protected $googleOAuthService;
  public function __construct(Calendar $calendar, GoogleOAuthService $googleOAuthService)
  {
    $this->calendar = $calendar;
    $this->googleOAuthService = $googleOAuthService;
  }

  public function createEvent(array $eventData): Event
  {
    $accessTokenData = json_decode($eventData['access_token'], true);
    $client = $this->googleOAuthService->getClient();
    //dd($client);
    // Accéder au jeton d'accès
    $accessToken = $accessTokenData['access_token'];
    //dd($accessToken);
    $client->setAccessToken($accessToken);

    // Création d'une instance du service Google Calendar
    //$calendar = new Calendar($this->client);
    $calendar = $this->googleOAuthService->getCalendarService();
    // Créez un nouvel événement avec les données fournies
    $startDateTime = new \DateTime($eventData['startDateTime']);
    $startDateTime->setTimezone(new \DateTimeZone('Europe/Paris'));
    $endDateTime = new \DateTime($eventData['endDateTime']);
    $endDateTime->setTimezone(new \DateTimeZone('Europe/Paris'));

    // Créez un nouvel événement avec les données fournies
    $event = new Event([
        'summary' => $eventData['summary'],
        'description' => $eventData['description'],
        'start' => ['dateTime' => $startDateTime->format('c')],
        'end' => ['dateTime' => $endDateTime->format('c')]
        // Ajoutez d'autres détails de l'événement ici
    ]);

    // Appelez l'API Google Calendar pour insérer l'événement
    return $calendar->events->insert('primary', $event);
  }

  public function deleteEvent($eventId)
{
    // Récupérer le jeton d'accès
    $accessToken = $this->googleOAuthService->getAccessToken();

    // Créer une instance du service Google Calendar
    $calendar = $this->googleOAuthService->getCalendarService();
    // Supprimer l'événement de Google Calendar
    $calendar->events->delete('primary', $eventId);
}
}