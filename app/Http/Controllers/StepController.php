<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Step;

class StepController extends Controller
{
    public function create()
    {
        return view('steps.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'step' => 'required|string|max:255', // Modifier le nom du champ
        ]);

        // Step::create([
        //     'step' => $request->step, // Modifier le nom du champ
        // ]);
        $step = new Step();
        $step->step = $request->step;
        $step->save();

        return redirect()->route('steps.create')->with('success', 'Étape ajoutée avec succès!');
    }
}