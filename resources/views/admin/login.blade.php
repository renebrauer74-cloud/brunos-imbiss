<x-layouts.admin title="Anmelden" :guest="true">
    <div class="login-box">
        <img class="login-logo" src="{{ asset('assets/img/logo.jpg') }}" alt="Bruno’s Imbiss" width="1314" height="1009">

        <h1>Anmelden</h1>

        <form method="post" action="{{ route('admin.login.store') }}" class="form" autocomplete="on">
            @csrf
            <label class="field">
                <span>Benutzername</span>
                <input type="text" name="username" value="{{ old('username') }}" required autocomplete="username" autocapitalize="none" autofocus>
            </label>
            <label class="field">
                <span>Passwort</span>
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <label class="check-inline">
                <input type="checkbox" name="remember" value="1"> Angemeldet bleiben
            </label>
            <button class="btn btn-primary btn-block" type="submit">Anmelden</button>
        </form>
        <p class="login-back"><a href="{{ route('home') }}">← Zur Website</a></p>
    </div>
</x-layouts.admin>
