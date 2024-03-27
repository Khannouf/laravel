<form action="{{ route('steps.store') }}" method="POST">
  @csrf
  <div>
      <label for="step">Nom de l'étape :</label>
      <input type="text" id="step" name="step" required>
  </div>
  <button type="submit">Ajouter l'étape</button>
</form>