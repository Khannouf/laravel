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
    //dd(Session::get('google_access_token'));

    return view('events.create', compact('opportunities', 'events'));
  }

  public function store(Request $request)
{
    $request->validate([
        'opportunity_id' => 'required|exists:opportunities,id',
        'type_event' => 'required|string|max:255',
        'description' => 'required|string',
    ]);

    // Recherche de l'opportunité associée à l'opportunity_id soumis par le formulaire
    $opportunity = Opportunity::findOrFail($request->opportunity_id);
    // Création de l'événement en utilisant le step_id de l'opportunité
    
    $eventData = [
      'access_token' => Session::get('google_access_token'),
      'summary' => $request->type_event,
      'description'=> $request->description,
      'startDateTime'=> '2024-04-07T12:00:00',
      'endDateTime'=> '2024-04-08T12:00:00',

      ];
      $event = $this->googleCalendarService->createEvent($eventData);
      //dd($event->id);
      Event::create([
        'opportunity_id' => $request->opportunity_id,
        'step_id' => $opportunity->step_id, // Utilisation du step_id de l'opportunité
        'type_event' => $request->type_event,
        'description' => $request->description,
        'gcalendar_event_id'=>$event->id
    ]);

    return redirect()->route('events.create')->with('success', 'Événement créé avec succès!');
}

public function destroy($id)
{
    $event = Event::findOrFail($id);
    //$event->delete();

    // Supprimer l'événement de Google Calendar
    $this->googleCalendarService->deleteEvent($event->gcalendar_event_id);

    return redirect()->route('events.index')->with('success', 'Événement supprimé avec succès!');
}

private function showAll(Request $request){
  $events = Event::all();
  $events->transform(function ($event) {
    $opportunity = Opportunity::findOrFail($event->opportunity_id);
    $prospect = Prospect::findOrFail($opportunity->prospect_id);
    $user = User::select('name', 'email')
      ->findOrFail($opportunity->user_id);
    $step = Step::findOrFail($opportunity->step_id);
    $event->opportunity = $opportunity;
    $event->prospect = $prospect;
    $event->user = $user;
    $event->step = $step;
    return $event;
  });
  //dd($events);
  return $events;
}
}