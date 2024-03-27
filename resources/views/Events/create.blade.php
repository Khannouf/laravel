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
  <button type="submit">Créer l'événement</button>
</form>