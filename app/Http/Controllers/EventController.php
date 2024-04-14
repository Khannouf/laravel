<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Opportunity;
use App\Models\Prospect;
use App\Models\User;
use App\Models\Step;
use Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EventController extends Controller
{
  protected $googleCalendarService;

  public function __construct(GoogleCalendarService $googleCalendarService)
  {
    $this->googleCalendarService = $googleCalendarService;
  }
  public function create(Request $request)
  {
    $opportunities = Opportunity::all();
    $events = $this->showAll($request);

    return view('events.create', compact('opportunities', 'events'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'opportunity_id' => 'required|exists:opportunities,id',
      'type_event' => 'required|string|max:255',
      'description' => 'required|string',
    ]);
    //$opportunity = Opportunity::findOrFail($request->opportunity_id);

    $eventData = [
      'access_token' => Session::get('google_access_token'),
      'summary' => $request->type_event,
      'description' => $request->description,
      'startDateTime' => $request->startDateTime,
      'endDateTime' => $request->endDateTime,

    ];
    $event = $this->googleCalendarService->createEvent($eventData);
    Event::create([
      //'opportunity_id' => $request->opportunity_id,
      //'step_id' => $opportunity->step_id, 
      'type_event' => $request->type_event,
      'description' => $request->description,
      'gcalendar_event_id' => $event->id,
      'startDateTime' => $request->startDateTime,
      'endDateTime' => $request->endDateTime,
    ]);

    return redirect()->route('events.create')->with('success', 'Événement créé avec succès!');
  }

  public function destroy($id)
  {
    $event = Event::findOrFail($id);
    $event->delete();
    $this->googleCalendarService->deleteEvent($event->gcalendar_event_id);

    return redirect()->route('events.create')->with('success', 'Événement supprimé avec succès!');
  }

  public function update(Request $request, $id)
  {
    $event = Event::findOrFail($id);
    $event->update($request->all());

    $eventData = [
      'access_token' => Session::get('google_access_token'),
      'summary' => $request->type_event,
      'description' => $request->description,
      'startDateTime' => $event->startDateTime,
      'endDateTime' => $event->endDateTime,
    ];
    $this->googleCalendarService->updateEvent($event->gcalendar_event_id, $eventData);

    return redirect()->route('events.create')->with('success', 'Événement mis à jour avec succès.');
  }

  public function show($id)
  {
    $event = Event::findOrFail($id);
    return view('events.show', compact('event'));
  }
  private function showAll(Request $request)
  {
    $events = Event::all();
    $events->transform(function ($event) {
      //$opportunity = Opportunity::findOrFail($event->opportunity_id);
      //$prospect = Prospect::findOrFail($opportunity->prospect_id);
      //$user = User::select('name', 'email')
      //->findOrFail($opportunity->user_id);
      //$step = Step::findOrFail($opportunity->step_id);
      //$event->opportunity = $opportunity;
      //$event->prospect = $prospect;
      //$event->user = $user;
      //$event->step = $step;
      return $event;
    });
    return $events;
  }
  public function showGoogleEvents()
  {
    $startOfWeek = new \DateTime();
    $startOfWeek->setISODate((int) $startOfWeek->format('o'), (int) $startOfWeek->format('W'));
    $startOfWeek->setTime(0, 0, 0);

    $googleEvents = $this->googleCalendarService->getAllEvents();
    $events = Event::where('startDateTime', '>=', $startOfWeek->format('c'))->get();

    $eventIds = $events->pluck('gcalendar_event_id')->toArray();
    $formattedEvents = [];
    foreach ($googleEvents as $googleEvent) {
      if (!in_array($googleEvent['id'], $eventIds)) {
        $dateStart = $googleEvent['start'];
        $dateEnd = $googleEvent['end'];
        $start = new Carbon($dateStart);
        $end = new Carbon($dateEnd);
        $start->setTimeZone('UTC');
        $end->setTimezone('UTC');
        $startDate = $start->format('Y-m-d H:i:s');
        $endDate = $end->format('Y-m-d H:i:s');

        $formattedEvents[] = [
          'id' => $googleEvent['id'],
          'name' => $googleEvent['name'],
          'description' => $googleEvent['description'],
          'start' => $startDate, 
          'end' => $endDate,
          'status' => $googleEvent['status'],
        ];
        Event::create([
          'type_event' => $googleEvent['name'],
          'description' => $googleEvent['description'],
          'gcalendar_event_id' => $googleEvent['id'],
          'startDateTime' => $startDate,
          'endDateTime' => $endDate,
        ]);
      }
    }
    return $formattedEvents;
  }

  public function showUpdatedGoogleEvents()
  {
    $startOfWeek = new \DateTime();
    $startOfWeek->setISODate((int) $startOfWeek->format('o'), (int) $startOfWeek->format('W'));
    $startOfWeek->setTime(0, 0, 0);

    $googleEvents = $this->googleCalendarService->getUpdatedEvents();
    //dd($googleEvents); 
    $events = Event::where('startDateTime', '>=', $startOfWeek->format('c'))->get();
    $eventIds = $events->pluck('gcalendar_event_id')->toArray();
    $formattedEvents = [];
    foreach ($googleEvents as $googleEvent) {
      $googleEventLastUpdate = new Carbon($googleEvent['lastUpdate']);
      $foundUpdated = false;
      $dateStart = $googleEvent['start'];
      $dateEnd = $googleEvent['end'];
      $start = new Carbon($dateStart);
      $end = new Carbon($dateEnd);
      $start->setTimeZone('UTC');
      $end->setTimezone('UTC');
      $startDate = $start->format('Y-m-d H:i:s');
      $endDate = $end->format('Y-m-d H:i:s');
      foreach ($events as $event) {
        //dd($event);
        $localUpdatedAt = new Carbon($event->updated_at);
        $formattedLocalUpdatedAt = $localUpdatedAt->toIso8601ZuluString();
        if ($formattedLocalUpdatedAt < $googleEventLastUpdate) {
          $foundUpdated = true;
        }
      }
      if ($foundUpdated) {
        $formattedEvents[] = [
          'id' => $googleEvent['id'],
          'name' => $googleEvent['name'],
          'description'=> $googleEvent['description'],
          'start' => $googleEvent['start'],
          'end' => $googleEvent['end'],
          'status' => $googleEvent['status'],
          'update' => $googleEvent['lastUpdate'],
          'nameEvent' => $event->updated_at,
        ];

        try {
          $event = Event::where('gcalendar_event_id', $googleEvent['id'])->firstOrFail();
          $event->update([
            'type_event' => $googleEvent['name'],
            'description' => $googleEvent['description'],
            'gcalendar_event_id' => $googleEvent['id'],
            'startDateTime' => $startDate,
            'endDateTime' => $endDate,
          ]);
          
        } catch (ModelNotFoundException $e) {
          return response()->json(['error' => 'Evenement non trouvé : '.$e], 404);
        }
      }
    }
    ;
    dd($formattedEvents);
    return $googleEvents;
  }
  public function showDeletedGoogleEvents()
  {
    $startOfWeek = new \DateTime();
    $startOfWeek->setISODate((int) $startOfWeek->format('o'), (int) $startOfWeek->format('W'));
    $startOfWeek->setTime(0, 0, 0);

    $googleEvents = $this->googleCalendarService->getDeletedEvents();
    $events = Event::where('startDateTime', '>=', $startOfWeek->format('c'))->get();
    $formattedEvents = [];
    foreach ($googleEvents as $googleEvent) {
      foreach ($events as $event) {
        if ($event->gcalendar_event_id == $googleEvent['id']) {
          $formattedEvents[] = [
            'id' => $googleEvent['id'],
            'Nom' => $googleEvent['Nom'],
            'start' => $googleEvent['start'],
            'end' => $googleEvent['end'],
            'status' => $googleEvent['status'],
          ];
          try {
            $event = Event::where('gcalendar_event_id', $googleEvent['id'])->firstOrFail();
            $event->delete();
            
          } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Evenement non trouvé : '.$e], 404);
          }
        }
      }
    }
    return response()->json(['type'=> 'success']);
  }
}