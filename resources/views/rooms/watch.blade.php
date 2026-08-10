<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Watch movie</title>
	@vite(['resources/js/app.js']) {{-- Load JS --}}
</head>
<body>
	<div style="display: flex; justify-content: center; align-items: center; height: 100vh; position absolute; top: 0; left: 0; width: 100%; background-color: #f9f9f9;">
		<button onclick="this.parentElement.remove(); document.getElementById('content').style.display = 'block';">Dołącz do party</button>
	</div>
	<div id="content" style="display: none;">
	<h1 class="text-3xl font-bold mb-4">Oglądanie pokoju: {{ $room->name }}</h1>
	<p class="text-lg">Właściciel: {{ $room->owner->name }}</p>
	<p class="text-lg">Publiczny: {{ $room->is_public ? 'Tak' : 'Nie' }}</p>
	<div>
		@if($watchStatus === 'active')
			<video controls id="video-player" width="800" height="450">
				<source src="{{ Storage::disk('public')->url($room->id) }}" type="video/mp4">
				Twoja przeglądarka nie obsługuje odtwarzania wideo.
			</video>
		@else
				<p class="text-red-500 mt-4">Transmisja nie jest aktywna. Poczekaj na rozpoczęcie transmisji przez właściciela pokoju.</p>
		@endif
	</div>
	<a href="{{ route('rooms.show', $room->id) }}" class="btn btn-primary mt-4">Powrót do pokoju</a>
	</div>
	@if($isOwner)
		<script>
			document.addEventListener("DOMContentLoaded", function () {
			    const videoPlayer = document.getElementById("video-player");

			    if (!videoPlayer) {
			        return;
			    }

			    function sendVideoCommand(cmd) {
					console.log(cmd);
			        fetch("{{ route('rooms.videoControl') }}", {
			            method: "POST",
			            headers: {
			                "Content-Type": "application/json",
			                "X-CSRF-TOKEN": "{{ csrf_token() }}",
			            },
			            body: JSON.stringify({
			                cmd: cmd,
							room_id: {{ $room->id }}
			                
			            })
			        });
			    }

			    videoPlayer.addEventListener("play", () => {
			        sendVideoCommand({
			            action: "play"
			        });
			    });

			    videoPlayer.addEventListener("pause", () => {
			        sendVideoCommand({
			            action: "pause"
			        });
			    });
			});
		</script>
@endif
		<script>
			document.addEventListener("DOMContentLoaded", function () {
			    window.Echo.channel("room.{{ $room->id }}")
			        .listen(".video.control", (event) => {
			            console.log("Received video control command:", event);

			            const videoPlayer = document.getElementById("video-player");

			            if (!videoPlayer) {
			                return;
			            }

			            if (event.cmd.action === "play") {
			                videoPlayer.play();
			            }
			            else if (event.cmd.action === "pause") {
			                videoPlayer.pause();
			            }
			        });
			});
			</script>
</body>
</html>