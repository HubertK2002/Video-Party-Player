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
	<ul class="list-disc pl-5">
		@foreach ($ownedRooms as $room)
			<li class="mb-2">
				<a class="text-blue-500 hover:underline" href="{{ route('rooms.show', $room->id) }}">{{ $room->name }}</a>
				@if ($room->is_public)
					<span class="text-green-500">Publiczny</span>
				@else
					<span class="text-red-500">Prywatny</span>
				@endif
				<span>Właściciel: {{ $room->owner->name }} (ID: {{ $room->owner->id }})</span>
			</li>
		@endforeach
	</ul>
	<a href="{{ route('rooms.create') }}" class="btn btn-primary mt-4">Utwórz nowy pokój</a><br>
</body>