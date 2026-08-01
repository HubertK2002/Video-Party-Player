<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Reverb Broadcast - Rooms</title>
	@vite(['resources/js/app.js']) {{-- Load JS --}}
	</head>
	<style>
		#chat-messages {
			border: 3px solid #ccc;
			border-style: outset;
			padding: 10px;
			height: 300px;
			overflow-y: scroll;
			background-color: #f9f9f9;
			Width: 400px;
			margin-bottom: 10px;
		}
	</style>
<body>
	@if (Auth::user() == $room->owner)
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
	<a href="{{ route('rooms.invitations', $room->id) }}" class="btn btn-primary mt-4">Zaproszenia do pokoju</a>
	@endif
	<div>
		<h2 class="text-2xl font-bold mt-6 mb-4">Czat w pokoju</h2>
		<div id="chat-messages" class="border p-4 h-64 overflow-y-scroll">
			@foreach ($messages as $message)
				<p><strong>{{ $message->user->name }}:</strong> {{ $message->message }} <em>({{ $message->sent_at }})</em></p>
			@endforeach
		</div>
		<form id="chat-form" class="mt-4">
			@csrf
			<input type="text" id="chat-input" name="message" placeholder="Wpisz wiadomość..." class="border p-2 w-full">
			<button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-2">Wyślij</button>
		</form>
	<a href="{{ route('rooms.index') }}" class="btn btn-primary mt-4">Powrót do listy pokoi</a>
	<script>
		document.addEventListener("DOMContentLoaded", function () {
			window.Echo.channel("chat-room.{{ $room->id }}")
				.listen(".message.sent", (event) => {
					console.log("Received:", event);
					const chatMessages = document.getElementById("chat-messages");
					const newMessage = document.createElement("p");
					newMessage.innerHTML = `<strong>${event.message.user.name}:</strong> ${event.message.message} <em>(${event.message.sent_at})</em>`;
					chatMessages.appendChild(newMessage);
					chatMessages.scrollTop = chatMessages.scrollHeight; // Scroll to the bottom
				});
		});


		document.getElementById("chat-form").addEventListener("submit", function (e) {
			e.preventDefault();
			const messageInput = document.getElementById("chat-input");
			const message = messageInput.value

			if (message.trim() === "") return;
			fetch("{{ route('rooms.sendMessage', $room->id) }}", {
				method: "POST",
				headers: {
					"Content-Type": "application/json",
					"X-CSRF-TOKEN": "{{ csrf_token() }}"
				},
				body: JSON.stringify({ message: message, room_id: {{ $room->id }} })
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					messageInput.value = ""; // Clear input field
				} else {
					alert("Błąd podczas wysyłania wiadomości: " + data.error);
				}
			})
			.catch(error => {
				console.error("Błąd:", error);
				alert("Błąd podczas wysyłania wiadomości.");
	});
});
	</script>
</body>