<form action="{{ route('opportunities.store') }}" method="POST">
  @csrf
  <div>
      <label for="user_id">Utilisateur :</label>
      <select name="user_id" id="user_id">
          @foreach ($users as $user)
              <option value="{{ $user->id }}">{{ $user->name }}</option>
          @endforeach
      </select>
  </div>
  <div>
      <label for="prospect_id">Prospect :</label>
      <select name="prospect_id" id="prospect_id">
          @foreach ($prospects as $prospect)
              <option value="{{ $prospect->id }}">{{ $prospect->name }}</option>
          @endforeach
      </select>
  </div>
  <div>
      <label for="step_id">Étape :</label>
      <select name="step_id" id="step_id">
          @foreach ($steps as $step)
              <option value="{{ $step->id }}">{{ $step->step }}</option>
          @endforeach
      </select>
  </div>
  <button type="submit">Créer l'opportunité</button>
</form>