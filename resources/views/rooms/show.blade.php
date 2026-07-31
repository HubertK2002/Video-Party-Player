<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Reverb Broadcast - Rooms</title>
	@vite(['resources/js/app.js']) {{-- Load JS --}}
	</head>
<body>
	<p>Zalogowany użytkownik: {{ Auth::user()->name }}, ID: {{ Auth::user()->id }}</p>
	<h1 class="text-3xl font-bold mb-4">Pokój: {{ $room->name }}</h1>
	<p class="text-lg">Właściciel: {{ $room->owner->name }}</p>
	<p class="text-lg">Publiczny: {{ $room->is_public ? 'Tak' : 'Nie' }}</p>
	<h2 class="text-2xl font-bold mt-6 mb-4">Użytkownicy w pokoju</h2>
	<ul>
		@foreach ($users as $user)
			<li>{{ $user->name }}, {{$user->id}}</li>
		@endforeach
	</ul>
	<a href="{{ route('rooms.index') }}" class="btn btn-primary mt-4">Powrót do listy pokoi</a>
</body>