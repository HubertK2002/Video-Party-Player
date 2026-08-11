@extends('layouts.app')

@section('title', 'Seans: ' . $room->name)
@section('bare', true)

@section('content')
<div class="flex min-h-screen flex-col bg-ink-950">

	{{-- Ekran wejścia (wymagany, żeby przeglądarka pozwoliła na odtwarzanie) --}}
	<div id="join-overlay" class="fixed inset-0 z-50 flex flex-col items-center justify-center gap-6 bg-ink-950/95 px-6 text-center backdrop-blur-xl">
		<span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-flare-500 shadow-2xl shadow-brand-600/40">
			<svg class="h-8 w-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m10 8 6 4-6 4V8Z" fill="currentColor" stroke="none"/><rect x="2" y="4" width="20" height="16" rx="3"/></svg>
		</span>

		<div>
			<h1 class="text-3xl font-bold tracking-tight">{{ $room->name }}</h1>
			<p class="mt-2 text-sm text-mist-300">Seans prowadzi {{ $room->owner->name }}. Wejdź, gdy będziesz gotowy.</p>
		</div>

		<button id="join-button" class="btn btn-primary btn-lg">
			<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
			Dołącz do party
		</button>

		<a href="{{ route('rooms.show', $room->id) }}" class="text-sm text-mist-500 transition hover:text-mist-300">Wróć do pokoju</a>
	</div>

	{{-- Kino --}}
	<div id="content" class="hidden flex-1 flex-col">
		<header class="flex items-center gap-3 border-b border-white/5 px-4 py-3 sm:px-6">
			<a href="{{ route('rooms.show', $room->id) }}" class="btn btn-ghost btn-sm -ml-2">
				<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
				Pokój
			</a>

			<div class="min-w-0">
				<h1 class="truncate text-sm font-semibold tracking-tight">{{ $room->name }}</h1>
				<p class="truncate text-xs text-mist-500">{{ $room->owner->name }}</p>
			</div>

			<div class="ml-auto flex items-center gap-2">
				@if ($watchStatus === 'active')
					<span class="badge border-red-500/30 bg-red-500/10 text-red-300">
						<span class="live-dot"></span> Na żywo
					</span>
				@endif
				@if ($isOwner)
					<span class="badge badge-owner hidden sm:inline-flex">Sterujesz odtwarzaniem</span>
				@else
					<span class="badge badge-neutral hidden sm:inline-flex">Odtwarzanie synchronizowane</span>
				@endif
			</div>
		</header>

		<div class="flex flex-1 items-center justify-center p-4 sm:p-8">
			@if ($watchStatus === 'active')
				<div class="w-full max-w-5xl">
					<div class="overflow-hidden rounded-2xl border border-white/10 bg-black shadow-2xl shadow-black/70">
						<video controls id="video-player" class="block aspect-video w-full bg-black" playsinline>
							<source src="{{ Storage::disk('public')->url($room->id) }}" type="video/mp4">
							Twoja przeglądarka nie obsługuje odtwarzania wideo.
						</video>
					</div>

					<p class="mt-3 text-center text-xs text-mist-500">
						@if ($isOwner)
							Twoje play i pauza sterują odtwarzaniem u wszystkich widzów.
						@else
							Play i pauza są sterowane przez właściciela pokoju.
						@endif
					</p>
				</div>
			@else
				<div class="empty-state max-w-md">
					<span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-mist-500">
						<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
					</span>
					<div>
						<h2 class="section-title">Transmisja nie jest aktywna</h2>
						<p class="muted mt-1">Poczekaj, aż właściciel pokoju rozpocznie seans.</p>
					</div>
					<a href="{{ route('rooms.show', $room->id) }}" class="btn btn-secondary btn-sm mt-2">Wróć do pokoju</a>
				</div>
			@endif
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script>
	document.getElementById("join-button").addEventListener("click", function () {
		document.getElementById("join-overlay").remove();
		const content = document.getElementById("content");
		content.classList.remove("hidden");
		content.classList.add("flex");
	});
</script>

@if($isOwner)
	<script>
		document.addEventListener("DOMContentLoaded", function () {
		    const videoPlayer = document.getElementById("video-player");

		    if (!videoPlayer) {
		        return;
		    }

		    function sendVideoCommand(cmd) {
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
@endpush
