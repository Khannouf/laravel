<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\StepController;
use App\Http\Controllers\GoogleCalendarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/blog', function (Request $request) {
  return [
    //Pour récupérer tout
    "name" => $request->all(),
    //seulement la valeur 'name' qui est 'par defaut' si elle est null
    "name" => $request->input('name', 'par défaut'),
  ];
});

Route::get('/blog/{slug}-{id}', function (string $slug,  string $id) {
  return [
    "slug" => $slug,
    "id" => $id
  ];
})-> where([
  'id' => '[0-9]+',
  'slug' => '[a-z0-9\-]+'
]);
// Route::get('/step', function (Request $request) {
//   $step = new \App\Models\Step();
//   $step->step = "RENDEZ-VOUS";
//   $step->save();
//   return $step;
// });
Route::get('/steps/create', [StepController::class, 'create'])->name('steps.create');
Route::post('/steps', [StepController::class, 'store'])->name('steps.store');
Route::get('/opportunities/create', [OpportunityController::class, 'create'])->name('opportunities.create');
Route::post('/opportunities', [OpportunityController::class, 'store'])->name('opportunities.store');

Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
Route::post('/events', [EventController::class, 'store'])->name('events.store');
Route::delete('events/delete/{event}', [EventController::class, 'destroy'])->name('events.destroy');

Route::get('/auth/google', [GoogleCalendarController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleCalendarController::class, 'handleGoogleCallback']);