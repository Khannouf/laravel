<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $fillable = ['opportunity_id', 'step_id', 'type_event', 'description', 'gcalendar_event_id', 'startDateTime', 'endDateTime'];
}
