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
    $accessToken = $this->googleOAuthService->getAccessToken();
    $client = $this->googleOAuthService->getClient();
    $client->setAccessToken($accessToken);
    $calendar = $this->googleOAuthService->getCalendarService();
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
    ]);
    return $calendar->events->insert('primary', $event);
  }

  public function deleteEvent($eventId)
{

    $accessToken = $this->googleOAuthService->getAccessToken();
    $client = $this->googleOAuthService->getClient();
    $client->setAccessToken($accessToken);
    $calendar = $this->googleOAuthService->getCalendarService();
    $calendar = $this->googleOAuthService->getCalendarService();
    $calendar->events->delete('primary', $eventId);
}
public function updateEvent($eventId, $eventData){
    $accessToken = $this->googleOAuthService->getAccessToken();
    $client = $this->googleOAuthService->getClient();
    $client->setAccessToken($accessToken);
    $calendar = $this->googleOAuthService->getCalendarService();
    
    $startDateTime = new \DateTime($eventData['startDateTime']);
    $startDateTime->setTimezone(new \DateTimeZone('Europe/Paris'));
    $endDateTime = new \DateTime($eventData['endDateTime']);
    $endDateTime->setTimezone(new \DateTimeZone('Europe/Paris'));

    $event = $calendar->events->get('primary', $eventId);

    $event->setSummary($eventData['summary']);
    $event->setDescription($eventData['description']);
    $event->getStart()->setDateTime($startDateTime->format('c'));
    $event->getEnd()->setDateTime($endDateTime->format('c'));

    $calendar->events->update('primary', $event->getId(), $event);

}
}