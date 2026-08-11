@php
	$isOwner = auth()->check() && auth()->id() === $room->owner_id;
	$isLive = $fileExists && $watchStatus === 'active';
@endphp

@extends('layouts.app')

@section('title', $room->name . ' — Reverb Broadcast')

@section('content')
<div class="mx-auto w-full max-w-6xl">

	{{-- Nagłówek pokoju --}}
	<div class="mb-8 flex flex-wrap items-start justify-between gap-4">
		<div class="min-w-0">
			<a href="{{ route('rooms.index') }}" class="btn btn-ghost btn-sm -ml-3 mb-2">
				<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
				Wszystkie pokoje
			</a>

			<h1 class="page-title truncate">{{ $room->name }}</h1>

			<div class="mt-3 flex flex-wrap items-center gap-2">
				@if ($room->is_public)
					<span class="badge badge-public">Publiczny</span>
				@else
					<span class="badge badge-private">Prywatny</span>
				@endif

				<span class="badge badge-neutral">
					<span class="avatar h-4 w-4 text-[9px]">{{ mb_substr($room->owner->name, 0, 1) }}</span>
					{{ $room->owner->name }}
				</span>

				<span class="badge badge-neutral">{{ $users->count() }} w pokoju</span>

				@if ($isLive)
					<span class="badge border-red-500/30 bg-red-500/10 text-red-300">
						<span class="live-dot"></span> Na żywo
					</span>
				@endif
			</div>
		</div>

		@if ($isOwner)
			<a href="{{ route('rooms.invitations', $room->id) }}" class="btn btn-secondary btn-sm">
				<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
				Zaproszenia
			</a>
		@endif
	</div>

	<div class="grid gap-6 lg:grid-cols-3">

		{{-- Lewa kolumna: seans + zarządzanie --}}
		<div class="space-y-6 lg:col-span-2">

			{{-- Status seansu --}}
			<div class="card overflow-hidden">
				<div class="relative flex flex-col items-center justify-center gap-4 border-b border-white/5 bg-gradient-to-b from-brand-600/10 to-transparent px-6 py-12 text-center">
					@if ($isLive)
						<span class="flex h-14 w-14 items-center justify-center rounded-2xl border border-red-500/30 bg-red-500/10 text-red-300">
							<svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m10 8 6 4-6 4V8Z" fill="currentColor" stroke="none"/><circle cx="12" cy="12" r="10"/></svg>
						</span>
						<div>
							<h2 class="text-xl font-semibold tracking-tight">Transmisja trwa</h2>
							<p class="muted mt-1">Dołącz, żeby oglądać razem z resztą pokoju.</p>
						</div>
						<a href="{{ route('rooms.watch', $room->id) }}" class="btn btn-primary btn-lg">
							<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m10 8 6 4-6 4V8Z" fill="currentColor" stroke="none"/><rect x="2" y="4" width="20" height="16" rx="3"/></svg>
							Dołącz do seansu
						</a>
					@elseif ($fileExists)
						<span class="flex h-14 w-14 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-mist-500">
							<svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
						</span>
						<div>
							<h2 class="text-xl font-semibold tracking-tight">Film czeka na start</h2>
							<p class="muted mt-1">
								@if ($isOwner)
									Rozpocznij transmisję, gdy wszyscy będą gotowi.
								@else
									Poczekaj, aż właściciel pokoju rozpocznie transmisję.
								@endif
							</p>
						</div>
						@if ($isOwner)
							<a href="{{ route('rooms.startStream', $room->id) }}" class="btn btn-primary btn-lg">Rozpocznij transmisję</a>
						@endif
					@else
						<span class="flex h-14 w-14 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-mist-500">
							<svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M8 7l4-4 4 4"/><path d="M20 17v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2"/></svg>
						</span>
						<div>
							<h2 class="text-xl font-semibold tracking-tight">Brak filmu w pokoju</h2>
							<p class="muted mt-1">
								@if ($isOwner)
									Prześlij plik MP4, żeby rozpocząć wspólne oglądanie.
								@else
									Właściciel pokoju nie dodał jeszcze filmu.
								@endif
							</p>
						</div>
					@endif
				</div>

				@if ($isOwner)
					<div class="px-6 py-5">
						<div class="flex flex-wrap items-center justify-between gap-3">
							<div>
								<h3 class="section-title">{{ $fileExists ? 'Zmień film' : 'Prześlij film' }}</h3>
								<p class="muted mt-0.5">Obsługiwany format: MP4.</p>
							</div>
							@if ($isLive)
								<a href="{{ route('rooms.stopStream', $room->id) }}" class="btn btn-danger btn-sm">Zakończ transmisję</a>
							@endif
						</div>

						<form action="{{ route('rooms.uploadVideo', $room->id) }}" method="POST" enctype="multipart/form-data" class="mt-4 flex flex-col gap-3 sm:flex-row">
							@csrf
							<input type="file" id="video-upload" name="video" accept="video/mp4" class="input-file sm:flex-1">
							<button type="submit" class="btn btn-secondary shrink-0">Prześlij</button>
						</form>
					</div>
				@endif
			</div>

			{{-- Uczestnicy --}}
			<div class="card card-body">
				<h2 class="section-title mb-4">Użytkownicy w pokoju</h2>
				@if ($users->isEmpty())
					<p class="muted">Nikogo tu jeszcze nie ma.</p>
				@else
					<ul class="flex flex-wrap gap-2">
						@foreach ($users as $user)
							<li class="flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 py-1.5 pr-3 pl-1.5 text-sm">
								<span class="avatar h-6 w-6">{{ mb_substr($user->name, 0, 1) }}</span>
								<span class="text-mist-200">{{ $user->name }}</span>
								@if ($user->id === $room->owner_id)
									<span class="badge badge-owner px-1.5 py-0 text-[10px]">właściciel</span>
								@endif
							</li>
						@endforeach
					</ul>
				@endif
			</div>
		</div>

		{{-- Prawa kolumna: czat --}}
		<div class="lg:col-span-1">
			<div class="card flex h-[34rem] flex-col lg:sticky lg:top-24">
				<div class="flex items-center gap-2 border-b border-white/5 px-5 py-4">
					<svg class="h-4 w-4 text-brand-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/></svg>
					<h2 class="section-title">Czat w pokoju</h2>
				</div>

				<div id="chat-messages" class="chat-scroll flex-1">
					@forelse ($messages as $message)
						<div class="chat-row animate-in">
							<span class="avatar mt-0.5 h-7 w-7">{{ mb_substr($message->user->name, 0, 1) }}</span>
							<div class="min-w-0">
								<div class="chat-meta">
									<span class="chat-author">{{ $message->user->name }}</span>
									<span class="chat-time">{{ \Illuminate\Support\Carbon::parse($message->sent_at ?? $message->created_at)->format('H:i') }}</span>
								</div>
								<div class="chat-bubble">{{ $message->message }}</div>
							</div>
						</div>
					@empty
						<p id="chat-empty" class="muted m-auto text-center">Cisza w eterze — napisz pierwszą wiadomość.</p>
					@endforelse
				</div>

				@auth
					<form id="chat-form" class="flex items-center gap-2 border-t border-white/5 p-3">
						@csrf
						<input type="text"
						       id="chat-input"
						       name="message"
						       placeholder="Wpisz wiadomość…"
						       autocomplete="off"
						       maxlength="1000"
						       class="input flex-1">
						<button type="submit" class="btn btn-primary shrink-0 px-3" title="Wyślij">
							<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
							<span class="sr-only">Wyślij</span>
						</button>
					</form>
				@else
					<div class="border-t border-white/5 p-4 text-center text-sm text-mist-300">
						<a href="/login" class="font-medium text-brand-300 hover:text-brand-200">Zaloguj się</a>, żeby pisać na czacie.
					</div>
				@endauth
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script>
	document.addEventListener("DOMContentLoaded", function () {
		const chatMessages = document.getElementById("chat-messages");

		function formatTime(value) {
			const date = new Date((value || "").replace(" ", "T"));
			if (isNaN(date)) {
				return "";
			}
			return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
		}

		function appendMessage(name, text, sentAt) {
			const empty = document.getElementById("chat-empty");
			if (empty) {
				empty.remove();
			}

			const row = document.createElement("div");
			row.className = "chat-row animate-in";

			const avatar = document.createElement("span");
			avatar.className = "avatar mt-0.5 h-7 w-7";
			avatar.textContent = (name || "?").charAt(0);

			const column = document.createElement("div");
			column.className = "min-w-0";

			const meta = document.createElement("div");
			meta.className = "chat-meta";

			const author = document.createElement("span");
			author.className = "chat-author";
			author.textContent = name;

			const time = document.createElement("span");
			time.className = "chat-time";
			time.textContent = formatTime(sentAt);

			const bubble = document.createElement("div");
			bubble.className = "chat-bubble";
			bubble.textContent = text;

			meta.append(author, time);
			column.append(meta, bubble);
			row.append(avatar, column);
			chatMessages.appendChild(row);
			chatMessages.scrollTop = chatMessages.scrollHeight;
		}

		// przewiń na dół przy wejściu do pokoju
		chatMessages.scrollTop = chatMessages.scrollHeight;

		window.Echo.channel("chat-room.{{ $room->id }}")
			.listen(".message.sent", (event) => {
				appendMessage(event.message.user.name, event.message.message, event.message.sent_at);
			});

		@auth
		const chatForm = document.getElementById("chat-form");
		const messageInput = document.getElementById("chat-input");
		const sendButton = chatForm.querySelector("button[type=submit]");

		chatForm.addEventListener("submit", function (e) {
			e.preventDefault();
			const message = messageInput.value.trim();

			if (message === "") {
				return;
			}

			sendButton.disabled = true;

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
					messageInput.value = "";
					// nadawca nie dostaje własnego broadcastu (toOthers) — dorysuj lokalnie
					appendMessage(@json(auth()->user()->name ?? ''), message, new Date().toISOString());
				} else {
					alert("Błąd podczas wysyłania wiadomości: " + (data.error || ""));
				}
			})
			.catch(error => {
				console.error("Błąd:", error);
				alert("Błąd podczas wysyłania wiadomości.");
			})
			.finally(() => {
				sendButton.disabled = false;
				messageInput.focus();
			});
		});
		@endauth
	});
</script>
@endpush
