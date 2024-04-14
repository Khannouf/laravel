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
    $startOfWeek->setISODate((int)$startOfWeek->format('o'), (int)$startOfWeek->format('W'));
    $startOfWeek->setTime(0, 0, 0); // Début de la semaine à minuit

    $endOfWeek = clone $startOfWeek;
    $endOfWeek->add(new \DateInterval('P7D')); // Ajouter 6 jours pour arriver à la fin de la semaine
    $endOfWeek->setTime(23, 59, 59); // Fin de la semaine juste avant minuit

    // ID du calendrier principal de l'utilisateur
    $calendarId = 'primary';

    // Paramètres optionnels pour la requête
    $optParams = [
      'maxResults' => 100, // Limite le nombre de résultats retournés
      'orderBy' => 'startTime', // Ordonne les événements par date de début
      'singleEvents' => true, // Traite les événements récurrents comme des événements uniques
      'timeMin' => $startOfWeek->format('c'), // Récupère les événements à partir de maintenant
      'timeMax' => $endOfWeek->format('c'),
    ];

    try {
      // Utilisation des paramètres optionnels dans l'appel
      $events = $calendar->events->listEvents($calendarId, $optParams);

      // Transformer les données des événements si nécessaire, par exemple :
      $formattedEvents = [];
      foreach ($events->getItems() as $event) {
        $formattedEvents[] = [
          'id' => $event->getId(),
          'name' => $event->getSummary(),
          'description'=> $event->getDescription(),
          'start' => $event->getStart()->dateTime ?? $event->getStart()->date, // Gère les événements toute la journée
          'end' => $event->getEnd()->dateTime ?? $event->getEnd()->date,
          'status' => $event->getStatus(),
          'startSearch'=> $startOfWeek->format('c')
          // Ajoutez d'autres champs selon les besoins
        ];
      }

      return $formattedEvents;
    } catch (Exception $e) {
      // Gérer l'exception comme vous le souhaitez, par exemple en retournant null ou un message d'erreur
      // Retourne null ou un tableau vide pour indiquer qu'aucun événement n'a été trouvé ou qu'une erreur s'est produite
      return null; // Ou retourner un message d'erreur personnalisé
    }
  }

  public function getUpdatedEvents()
  {
    $accessToken = $this->googleOAuthService->getAccessToken();
    $client = $this->googleOAuthService->getClient();
    $client->setAccessToken($accessToken);
    $calendar = $this->googleOAuthService->getCalendarService();

    $startOfWeek = new \DateTime();
    $startOfWeek->setISODate((int)$startOfWeek->format('o'), (int)$startOfWeek->format('W'));
    $startOfWeek->setTime(0, 0, 0); // Début de la semaine à minuit

    $endOfWeek = clone $startOfWeek;
    $endOfWeek->add(new \DateInterval('P7D')); // Ajouter 6 jours pour arriver à la fin de la semaine
    $endOfWeek->setTime(23, 59, 59); // Fin de la semaine juste avant minuit

    // ID du calendrier principal de l'utilisateur
    $calendarId = 'primary';

    // Paramètres optionnels pour la requête
    $optParams = [
      'maxResults' => 100, // Limite le nombre de résultats retournés
      'orderBy' => 'startTime', // Ordonne les événements par date de début
      'singleEvents' => true, // Traite les événements récurrents comme des événements uniques
      'timeMin' => $startOfWeek->format('c'), // Récupère les événements à partir de maintenant
      'timeMax' => $endOfWeek->format('c'),
    ];

    try {
      // Utilisation des paramètres optionnels dans l'appel
      $events = $calendar->events->listEvents($calendarId, $optParams);

      // Transformer les données des événements si nécessaire, par exemple :
      $formattedEvents = [];
      foreach ($events->getItems() as $event) {
        $formattedEvents[] = [
          'id' => $event->getId(),
          'name' => $event->getSummary(),
          'description'=> $event->getDescription(),
          'start' => $event->getStart()->dateTime ?? $event->getStart()->date, // Gère les événements toute la journée
          'end' => $event->getEnd()->dateTime ?? $event->getEnd()->date,
          'status' => $event->getStatus(),
          'startSearch'=> $startOfWeek->format('c'),
          'lastUpdate'=> $event->getUpdated(),
          // Ajoutez d'autres champs selon les besoins
        ];
      }
      //dd($formattedEvents);
      return $formattedEvents;
    } catch (Exception $e) {
      // Gérer l'exception comme vous le souhaitez, par exemple en retournant null ou un message d'erreur
      // Retourne null ou un tableau vide pour indiquer qu'aucun événement n'a été trouvé ou qu'une erreur s'est produite
      return null; // Ou retourner un message d'erreur personnalisé
    }
  }

  public function getDeletedEvents()
  {
    $accessToken = $this->googleOAuthService->getAccessToken();
    $client = $this->googleOAuthService->getClient();
    $client->setAccessToken($accessToken);
    $calendar = $this->googleOAuthService->getCalendarService();

    $startOfWeek = new \DateTime();
    $startOfWeek->setISODate((int)$startOfWeek->format('o'), (int)$startOfWeek->format('W'));
    $startOfWeek->setTime(0, 0, 0); // Début de la semaine à minuit

    $endOfWeek = clone $startOfWeek;
    $endOfWeek->add(new \DateInterval('P7D')); // Ajouter 6 jours pour arriver à la fin de la semaine
    $endOfWeek->setTime(23, 59, 59); // Fin de la semaine juste avant minuit

    // ID du calendrier principal de l'utilisateur
    $calendarId = 'primary';

    // Paramètres optionnels pour la requête
    $optParams = [
      'maxResults' => 100, // Limite le nombre de résultats retournés
      'orderBy' => 'startTime', // Ordonne les événements par date de début
      'singleEvents' => true, // Traite les événements récurrents comme des événements uniques
      'timeMin' => $startOfWeek->format('c'), // Récupère les événements à partir de maintenant
      'timeMax' => $endOfWeek->format('c'),
      'showDeleted'=>true,
    ];

    try {
      // Utilisation des paramètres optionnels dans l'appel
      $events = $calendar->events->listEvents($calendarId, $optParams);

      // Transformer les données des événements si nécessaire, par exemple :
      $formattedEvents = [];
      foreach ($events->getItems() as $event) {
        if($event->status == 'cancelled'){
          $formattedEvents[] = [
            'id' => $event->getId(),
            'Nom' => $event->getSummary(),
            'start' => $event->getStart()->dateTime ?? $event->getStart()->date, // Gère les événements toute la journée
            'end' => $event->getEnd()->dateTime ?? $event->getEnd()->date,
            'status' => $event->getStatus(),
            // Ajoutez d'autres champs selon les besoins
          ];
        }
      }

      return $formattedEvents;
    } catch (Exception $e) {
      // Gérer l'exception comme vous le souhaitez, par exemple en retournant null ou un message d'erreur
      // Retourne null ou un tableau vide pour indiquer qu'aucun événement n'a été trouvé ou qu'une erreur s'est produite
      return null; // Ou retourner un message d'erreur personnalisé
    }
  }
}