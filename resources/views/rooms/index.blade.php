<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Reverb Broadcast - Rooms</title>
	@vite(['resources/js/app.js']) {{-- Load JS --}}
	</head>
<body>
	<h1 class="text-3xl font-bold mb-4">Dostępne pokoje</h1>
		@foreach ($rooms as $room)
			<table>
				<tr>
					<td><a class="text-blue-500 hover:underline" href="{{ route('rooms.show', $room->id) }}">{{ $room->name }}</a></td>
					<td>
						@if ($room->is_public)
							<span class="text-green-500">Publiczny</span>
						@else
							<span class="text-red-500">Prywatny</span>
						@endif
					</td>
					<td> {{ $room->owner->id }} || {{ $room->owner->name }} </td>
				</tr>
			</table>
		@endforeach
	@auth
		<a href="{{ route('rooms.create') }}" class="btn btn-primary mt-4">Utwórz nowy pokój</a><br>
		<a href="{{ route('rooms.owned') }}" class="btn btn-primary mt-4">Pokoje, które posiadasz</a><br>
	@endauth
	<a href="{{ route('rooms.index') }}" class="btn btn-primary mt-4">Odśwież listę pokoi</a><br>
	<a href="{{ route('home') }}" class="btn btn-primary mt-4">Powrót do strony głównej</a>
</body>
</html>