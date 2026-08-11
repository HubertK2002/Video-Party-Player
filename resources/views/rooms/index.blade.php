@extends('layouts.app')

@section('title', 'Pokoje — Reverb Broadcast')

@section('content')
<div class="mx-auto w-full max-w-6xl">

	<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
		<div>
			<h1 class="page-title">Dostępne pokoje</h1>
			<p class="mt-1.5 text-sm text-mist-300">
				Na liście: {{ $rooms->count() }} — wejdź do pokoju i zacznij oglądać.
			</p>
		</div>

		<div class="flex flex-wrap items-center gap-2">
			<a href="{{ route('rooms.index') }}" class="btn btn-secondary btn-sm">
				<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/></svg>
				Odśwież
			</a>
			@auth
				<a href="{{ route('rooms.owned') }}" class="btn btn-secondary btn-sm">Moje pokoje</a>
				<a href="{{ route('rooms.create') }}" class="btn btn-primary btn-sm">
					<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
					Nowy pokój
				</a>
			@endauth
		</div>
	</div>

	@if ($rooms->isEmpty())
		<div class="empty-state">
			<span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-mist-500">
				<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="m10 8 6 4-6 4V8Z"/></svg>
			</span>
			<div>
				<h2 class="section-title">Nie ma jeszcze żadnych pokoi</h2>
				<p class="muted mt-1">Bądź pierwszy — stwórz pokój i zaproś znajomych.</p>
			</div>
			@auth
				<a href="{{ route('rooms.create') }}" class="btn btn-primary btn-sm mt-2">Stwórz pokój</a>
			@else
				<a href="/login" class="btn btn-primary btn-sm mt-2">Zaloguj się, aby stworzyć pokój</a>
			@endauth
		</div>
	@else
		<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
			@foreach ($rooms as $room)
				@include('partials.room-card', ['room' => $room])
			@endforeach
		</div>
	@endif

</div>
@endsection
