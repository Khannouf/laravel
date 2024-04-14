<?php

namespace App\Services;

use Google\Client;
use \App\Services\GoogleOAuthService;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use \Exception;

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
  public function updateEvent($eventId, $eventData)
  {
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

  public function getAllEvents()
  {
    $accessToken = $this->googleOAuthService->getAccessToken();
    $client = $this->googleOAuthService->getClient();
    $client->setAccessToken($accessToken);
    $calendar = $this->googleOAuthService->getCalendarService();

    $startOfWeek = new \DateTime();
    $startOfWeek->setISODate((int) $startOfWeek->format('o'), (int) $startOfWeek->format('W'));
    $startOfWeek->setTime(0, 0, 0);

    $endOfWeek = clone $startOfWeek;
    $endOfWeek->add(new \DateInterval('P7D'));
    $endOfWeek->setTime(23, 59, 59);


    $calendarId = 'primary';

    $optParams = [
      'maxResults' => 100,
      'orderBy' => 'startTime',
      'singleEvents' => true,
      'timeMin' => $startOfWeek->format('c'),
      'timeMax' => $endOfWeek->format('c'),
    ];

    try {

      $events = $calendar->events->listEvents($calendarId, $optParams);


      $formattedEvents = [];
      foreach ($events->getItems() as $event) {
        $formattedEvents[] = [
          'id' => $event->getId(),
          'name' => $event->getSummary(),
          'description' => $event->getDescription(),
          'start' => $event->getStart()->dateTime ?? $event->getStart()->date,
          'end' => $event->getEnd()->dateTime ?? $event->getEnd()->date,
          'status' => $event->getStatus(),
          'startSearch' => $startOfWeek->format('c')
        ];
      }

      return $formattedEvents;
    } catch (Exception $e) {
      return null;
    }
  }

  public function getUpdatedEvents()
  {
    $accessToken = $this->googleOAuthService->getAccessToken();
    $client = $this->googleOAuthService->getClient();
    $client->setAccessToken($accessToken);
    $calendar = $this->googleOAuthService->getCalendarService();

    $startOfWeek = new \DateTime();
    $startOfWeek->setISODate((int) $startOfWeek->format('o'), (int) $startOfWeek->format('W'));
    $startOfWeek->setTime(0, 0, 0);
    $endOfWeek = clone $startOfWeek;
    $endOfWeek->add(new \DateInterval('P7D'));
    $endOfWeek->setTime(23, 59, 59);

    $calendarId = 'primary';

    $optParams = [
      'maxResults' => 100,
      'orderBy' => 'startTime',
      'singleEvents' => true,
      'timeMin' => $startOfWeek->format('c'),
      'timeMax' => $endOfWeek->format('c'),
    ];

    try {
      $events = $calendar->events->listEvents($calendarId, $optParams);

      $formattedEvents = [];
      foreach ($events->getItems() as $event) {
        $formattedEvents[] = [
          'id' => $event->getId(),
          'name' => $event->getSummary(),
          'description' => $event->getDescription(),
          'start' => $event->getStart()->dateTime ?? $event->getStart()->date,
          'end' => $event->getEnd()->dateTime ?? $event->getEnd()->date,
          'status' => $event->getStatus(),
          'startSearch' => $startOfWeek->format('c'),
          'lastUpdate' => $event->getUpdated(),
        ];
      }
      //dd($formattedEvents);
      return $formattedEvents;
    } catch (Exception $e) {
      return null;
    }
  }

  public function getDeletedEvents()
  {
    $accessToken = $this->googleOAuthService->getAccessToken();
    $client = $this->googleOAuthService->getClient();
    $client->setAccessToken($accessToken);
    $calendar = $this->googleOAuthService->getCalendarService();

    $startOfWeek = new \DateTime();
    $startOfWeek->setISODate((int) $startOfWeek->format('o'), (int) $startOfWeek->format('W'));
    $startOfWeek->setTime(0, 0, 0);

    $endOfWeek = clone $startOfWeek;
    $endOfWeek->add(new \DateInterval('P7D'));
    $endOfWeek->setTime(23, 59, 59);

    $calendarId = 'primary';

    $optParams = [
      'maxResults' => 100,
      'orderBy' => 'startTime',
      'singleEvents' => true,
      'timeMin' => $startOfWeek->format('c'),
      'timeMax' => $endOfWeek->format('c'),
      'showDeleted' => true,
    ];

    try {
      $events = $calendar->events->listEvents($calendarId, $optParams);

      $formattedEvents = [];
      foreach ($events->getItems() as $event) {
        if ($event->status == 'cancelled') {
          $formattedEvents[] = [
            'id' => $event->getId(),
            'Nom' => $event->getSummary(),
            'start' => $event->getStart()->dateTime ?? $event->getStart()->date,
            'end' => $event->getEnd()->dateTime ?? $event->getEnd()->date,
            'status' => $event->getStatus(),
          ];
        }
      }

      return $formattedEvents;
    } catch (Exception $e) {
      return null;
    }
  }
}