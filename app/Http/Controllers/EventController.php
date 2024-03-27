<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Opportunity;
use App\Models\Step;
use Illuminate\Http\Request;

class EventController extends Controller
{

  public function create()
  {
    $opportunities = Opportunity::all();

    return view('events.create', compact('opportunities'));
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
    Event::create([
        'opportunity_id' => $request->opportunity_id,
        'step_id' => $opportunity->step_id, // Utilisation du step_id de l'opportunité
        'type_event' => $request->type_event,
        'description' => $request->description,
    ]);

    return redirect()->route('events.create')->with('success', 'Événement créé avec succès!');
}
}