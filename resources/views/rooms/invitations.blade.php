@extends('layouts.app')

@section('title', 'Zaproszenia — ' . $room->name)

@section('content')
<div class="mx-auto w-full max-w-4xl">

	<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
		<div class="min-w-0">
			<a href="{{ route('rooms.show', $room->id) }}" class="btn btn-ghost btn-sm -ml-3 mb-2">
				<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
				Powrót do pokoju
			</a>
			<h1 class="page-title truncate">Zaproszenia</h1>
			<p class="mt-1.5 text-sm text-mist-300">
				Pokój <span class="font-medium text-mist-100">{{ $room->name }}</span> —
				{{ $room->is_public ? 'publiczny' : 'prywatny' }}
			</p>
		</div>

		<button id="generate-invitation" class="btn btn-primary">
			<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
			Wygeneruj zaproszenie
		</button>
	</div>

	@if ($invitations->isEmpty())
		<div class="empty-state">
			<span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-mist-500">
				<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z"/><path d="m4 7 8 6 8-6"/></svg>
			</span>
			<div>
				<h2 class="section-title">Brak zaproszeń</h2>
				<p class="muted mt-1">Wygeneruj link i wyślij go znajomym, żeby dołączyli do pokoju.</p>
			</div>
		</div>
	@else
		<div class="space-y-3">
			@foreach ($invitations as $invitation)
				@php
					$joinUrl = route('rooms.join', ['room' => $room->id, 'token' => $invitation->invitation_code]);
				@endphp

				<div class="card card-body">
					<div class="flex flex-wrap items-start justify-between gap-3">
						<div class="min-w-0 flex-1">
							<div class="mb-3 flex flex-wrap items-center gap-2 text-xs text-mist-500">
								<span class="badge badge-neutral">Utworzono: {{ $invitation->created_at?->format('d.m.Y H:i') ?? '—' }}</span>
								<span class="badge badge-neutral">
									Wygasa: {{ $invitation->expires_at ? \Illuminate\Support\Carbon::parse($invitation->expires_at)->format('d.m.Y H:i') : 'bezterminowo' }}
								</span>
							</div>

							<label class="label">Link zapraszający</label>
							<div class="flex flex-wrap items-center gap-2">
								<input type="text" value="{{ $joinUrl }}" readonly
								       class="input min-w-0 flex-1 font-mono text-xs"
								       onclick="this.select()">
								<button type="button" class="btn btn-secondary btn-sm shrink-0" data-copy="{{ $joinUrl }}">
									<svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
									Kopiuj link
								</button>
							</div>

							<div class="mt-3 flex flex-wrap items-center gap-2">
								<span class="muted">Token:</span>
								<code class="rounded-lg border border-white/10 bg-ink-900/70 px-2 py-1 font-mono text-xs text-mist-300">{{ $invitation->invitation_code }}</code>
								<button type="button" class="btn btn-ghost btn-sm" data-copy="{{ $invitation->invitation_code }}">Kopiuj</button>
							</div>
						</div>

						<form action="{{ route('rooms.deleteInvitation', ['room' => $room->id, 'invitation' => $invitation->id]) }}"
						      method="POST"
						      onsubmit="return confirm('Czy na pewno chcesz usunąć to zaproszenie?');">
							@csrf
							@method('DELETE')
							<button type="submit" class="btn btn-danger btn-sm">
								<svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
								Usuń
							</button>
						</form>
					</div>
				</div>
			@endforeach
		</div>
	@endif

	<div id="toast" class="pointer-events-none fixed bottom-6 left-1/2 z-50 -translate-x-1/2 rounded-xl border border-white/10 bg-ink-800 px-4 py-2.5 text-sm text-mist-100 opacity-0 shadow-2xl shadow-black/50 transition-opacity duration-200"></div>
</div>
@endsection

@push('scripts')
<script>
	const toast = document.getElementById("toast");
	let toastTimer;

	function showToast(text) {
		toast.textContent = text;
		toast.classList.remove("opacity-0");
		clearTimeout(toastTimer);
		toastTimer = setTimeout(() => toast.classList.add("opacity-0"), 2000);
	}

	document.querySelectorAll("[data-copy]").forEach((button) => {
		button.addEventListener("click", () => {
			navigator.clipboard.writeText(button.dataset.copy)
				.then(() => showToast("Skopiowano do schowka"))
				.catch(() => showToast("Nie udało się skopiować"));
		});
	});

	document.getElementById("generate-invitation").addEventListener("click", function () {
		const button = this;
		button.disabled = true;

		fetch("{{ route('rooms.generateInvitation', $room->id) }}", {
			method: "POST",
			headers: {
				"X-CSRF-TOKEN": "{{ csrf_token() }}",
				"Content-Type": "application/json"
			},
			body: JSON.stringify({})
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				showToast("Zaproszenie wygenerowane");
				setTimeout(() => location.reload(), 400);
			} else {
				showToast("Błąd: " + (data.error || "nie udało się wygenerować"));
				button.disabled = false;
			}
		})
		.catch(error => {
			console.error("Błąd:", error);
			showToast("Błąd podczas generowania zaproszenia");
			button.disabled = false;
		});
	});
</script>
@endpush
