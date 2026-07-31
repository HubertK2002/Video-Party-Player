<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reverb Broadcast</title>
    @vite(['resources/js/app.js']) {{-- Load JS --}}
</head>
<body>
    <h1>Strona główna</h1>
	<p>Witaj na stronie głównej aplikacji Reverb Broadcast!</p>
	
    <nav>
    @guest
	<button><a href="/login">Zaloguj się</a></button>
	<a href="/register"><button>Zarejestruj się</button></a>
    @endguest

    @auth
    <button><a href="/logout">Wyloguj się</a></button>
    <button><a href="/dashboard">Przejdź do panelu użytkownika</a></button>
    @endauth
    </nav>

    <div>
        <h2>Pokoje</h2>
        <a href="/rooms"><button>Przejdź do listy pokoi</button></a>
    </div>
</body>
</html>