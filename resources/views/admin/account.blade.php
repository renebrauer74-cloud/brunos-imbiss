<x-layouts.admin title="Konto" active="account">
    <div class="page-head">
        <div>
            <h1>Konto</h1>
            <p class="muted">Angemeldet als <strong>{{ auth()->user()->username }}</strong></p>
        </div>
    </div>

    <form method="post" action="{{ route('admin.account.update') }}" class="form card narrow">
        @csrf
        @method('PUT')
        <h2>Passwort ändern</h2>
        <input type="text" name="username" value="{{ auth()->user()->username }}" autocomplete="username" hidden>
        <label class="field">
            <span>Aktuelles Passwort</span>
            <input type="password" name="current_password" required autocomplete="current-password">
        </label>
        <label class="field">
            <span>Neues Passwort <small>(mind. 8 Zeichen)</small></span>
            <input type="password" name="password" required minlength="8" autocomplete="new-password">
        </label>
        <label class="field">
            <span>Neues Passwort wiederholen</span>
            <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
        </label>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Passwort ändern</button>
        </div>
    </form>
</x-layouts.admin>
