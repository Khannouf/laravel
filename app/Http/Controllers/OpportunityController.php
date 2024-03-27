<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Prospect;
use App\Models\Step;
use App\Models\Opportunity;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
  public function create()
  {
    $users = User::all();
    $prospects = Prospect::all();
    $steps = Step::all();

    return view('opportunities.create', compact('users', 'prospects', 'steps'));
  }
  public function store(Request $request)
{
    // Validez les données soumises si nécessaire
  $validatedData = $request->except('_token');
    Opportunity::create($validatedData);


    return redirect()->route('opportunities.create')->with('success', 'Opportunité créée avec succès!');
}
}