<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Reverb Broadcast - Invitations</title>
	@vite(['resources/js/app.js']) {{-- Load JS --}}
</head>
<body>
	<h1 class="text-3xl font-bold mb-4">Zaproszenia do pokoju: {{ $room->name }}</h1>
	<p class="text-lg">Właściciel: {{ $room->owner->name }}</p>
	<p class="text-lg">Publiczny: {{ $room->is_public ? 'Tak' : 'Nie' }}</p>
	<h2 class="text-2xl font-bold mt-6 mb-4">Lista zaproszeń</h2>
	<ul>
		@foreach ($invitations as $invitation)
			<li>
				<p>Token: {{ $invitation->invitation_code }} <button onclick="copyToClipboard('{{ $invitation->invitation_code }}')">Kopiuj</button></p>
				<p>Utworzono: {{ $invitation->created_at }}</p>
				<p>Wygasa: {{ $invitation->expires_at }}</p>
				<p> Link do pokoju: <a href="{{ route('rooms.join', ['room' => $room->id, 'token' => $invitation->invitation_code]) }}" target="_blank">{{ route('rooms.join', ['room' => $room->id, 'token' => $invitation->invitation_code]) }}</a> <button onclick="copyToClipboard('{{ route('rooms.join', ['room' => $room->id, 'token' => $invitation->invitation_code]) }}')">Kopiuj</button></p>
				<form action="{{ route('rooms.deleteInvitation', ['room' => $room->id, 'invitation' => $invitation->id]) }}" method="POST" onsubmit="return confirm('Czy na pewno chcesz usunąć to zaproszenie?');">
					@csrf
					@method('DELETE')
					<button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Usuń zaproszenie</button>
				</form>
			</li>
		@endforeach
	</ul>
	<button id="generate-invitation" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Wygeneruj nowe zaproszenie</button>
	<script>
		function copyToClipboard(text) {
			navigator.clipboard.writeText(text).then(() => {
				alert('Skopiowano do schowka!');
			});
		}

		document.getElementById('generate-invitation').addEventListener('click', function() {
			fetch('{{ route('rooms.generateInvitation', $room->id) }}', {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': '{{ csrf_token() }}',
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({})
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					alert('Nowe zaproszenie wygenerowane: ' + data.token);
					location.reload(); // Odśwież stronę, aby zobaczyć nowe zaproszenie
				} else {
					alert('Błąd podczas generowania zaproszenia. ' + data.error);
				}
			})
			.catch(error => {
				console.error('Błąd:', error);
				alert('Błąd podczas generowania zaproszenia.' + error);
			});
		});
	</script><br>
	<a href="{{ route('rooms.index') }}" class="btn btn-primary mt-4">Powrót do listy pokoi</a><br>
	<a href="{{ route('rooms.show', $room->id) }}" class="btn btn-primary mt-4">Powrót do pokoju</a>
</body>