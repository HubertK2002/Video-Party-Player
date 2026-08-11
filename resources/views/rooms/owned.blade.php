@extends('layouts.app')

@section('title', 'Moje pokoje — Reverb Broadcast')

@section('content')
<div class="mx-auto w-full max-w-6xl">

	<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
		<div>
			<h1 class="page-title">Moje pokoje</h1>
			<p class="mt-1.5 text-sm text-mist-300">Pokoje, których jesteś właścicielem.</p>
		</div>

		<div class="flex flex-wrap items-center gap-2">
			<a href="{{ route('rooms.index') }}" class="btn btn-secondary btn-sm">Wszystkie pokoje</a>
			<a href="{{ route('rooms.create') }}" class="btn btn-primary btn-sm">
				<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
				Nowy pokój
			</a>
		</div>
	</div>

	@if ($ownedRooms->isEmpty())
		<div class="empty-state">
			<span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-mist-500">
				<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
			</span>
			<div>
				<h2 class="section-title">Nie masz jeszcze własnego pokoju</h2>
				<p class="muted mt-1">Stwórz pierwszy i zaproś znajomych na seans.</p>
			</div>
			<a href="{{ route('rooms.create') }}" class="btn btn-primary btn-sm mt-2">Stwórz pokój</a>
		</div>
	@else
		<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
			@foreach ($ownedRooms as $room)
				@include('partials.room-card', ['room' => $room])
			@endforeach
		</div>
	@endif

</div>
@endsection
