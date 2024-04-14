<form action="{{ route('events.store') }}" method="POST">
  @csrf
  <div>
      <label for="opportunity_id">Opportunité :</label>
      <select name="opportunity_id" id="opportunity_id">
          @foreach ($opportunities as $opportunity)
              <option value="{{ $opportunity->id }}">{{ $opportunity->id }}</option>
          @endforeach
      </select>
  </div>
  <div>
      <label for="type_event">Type d'événement :</label>
      <input type="text" id="type_event" name="type_event" required>
  </div>
  <div>
      <label for="description">Description :</label>
      <textarea id="description" name="description" required></textarea>
  </div>
  <div>
    <label for="startDateTime">Date et heure de début :</label>
    <input type="datetime-local" id="startDateTime" name="startDateTime" required>
</div>
<div>
    <label for="endDateTime">Date et heure de fin :</label>
    <input type="datetime-local" id="endDateTime" name="endDateTime" required>
</div>
  <button type="submit">Créer l'événement</button>
</form>


<table>
  <caption> Events déja existants </caption> 
  <thead>
    <tr>
      <th scope="col">Events description</th>
      <th scope="col">Opportunitie Date</th>
      <th scope="col">Created By</th>
      <th scope="col">Prospect name</th>
      <th scope="col">Step of the event</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($events as $event)
    <tr>
      <td><a href="{{ route('events.show', $event->id) }}">{{ $event->description }}</a></td>
      @if ($event->opportunity)
      <td>{{ $event->opportunity->created_at }}</td>
      @endif
      @if ($event->user)
      <td>{{ $event->user->name }}</td>
      @endif
      @if ($event->prospect)
      <td>{{ $event->prospect->name }}</td>
      @endif
      @if ($event->step)
      <td>{{ $event->step->step }}</td>
      @endif
    </tr>
    @endforeach 
  </tbody>
  @foreach ($events as $event)
  <option value="{{ $opportunity->id }}">{{ $opportunity->id }}</option>

@endforeach 
</table>

<form action="{{ route('events.destroy', ['event' => 1]) }}" method="POST">
  @csrf
  @method('DELETE')
  <button type="submit">Supprimer</button>
</form>