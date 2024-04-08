<h1>Détails de l'événement</h1>
<form action="{{ route('events.update', $event->id) }}" method="POST">
  @csrf
  @method('PUT')
  <input type="text" name="type_event" value={{ $event->type_event }}>
  <input type="text" name="description" value={{ $event->description }}>
  <button type="submit">Modifier</button>
</form>
<form action="{{ route('events.destroy', ['event' => $event->id]) }}" method="POST">
  @csrf
  @method('DELETE')
  <button type="submit">Supprimer</button>
</form>