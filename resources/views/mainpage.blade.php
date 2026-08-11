@extends('layouts.app')

@section('title', 'Reverb Broadcast — oglądaj razem')

@section('content')
<div class="mx-auto w-full max-w-6xl">

	{{-- Hero --}}
	<section class="relative overflow-hidden rounded-3xl border border-white/10 bg-ink-900/60 px-6 py-16 text-center shadow-2xl shadow-black/50 sm:px-12 sm:py-24">
		<div class="pointer-events-none absolute inset-x-0 -top-40 h-80 bg-gradient-to-b from-brand-600/25 to-transparent blur-3xl"></div>

		<div class="relative">
			<span class="badge badge-neutral mb-6">
				<span class="live-dot"></span>
				Synchronizacja na żywo przez Laravel Reverb
			</span>

			<h1 class="mx-auto max-w-3xl text-4xl font-bold tracking-tight text-balance sm:text-6xl">
				Oglądaj filmy razem, <span class="gradient-text">nawet gdy jesteście osobno</span>
			</h1>

			<p class="mx-auto mt-5 max-w-xl text-base text-mist-300 sm:text-lg">
				Stwórz pokój, zaproś znajomych i puść film. Odtwarzanie jest zsynchronizowane u wszystkich,
				a czat na żywo działa obok obrazu.
			</p>

			<div class="mt-9 flex flex-wrap items-center justify-center gap-3">
				@auth
					<a href="{{ route('rooms.create') }}" class="btn btn-primary btn-lg">
						<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
						Stwórz pokój
					</a>
					<a href="{{ route('rooms.index') }}" class="btn btn-secondary btn-lg">Przeglądaj pokoje</a>
				@endauth

				@guest
					<a href="/register" class="btn btn-primary btn-lg">Załóż darmowe konto</a>
					<a href="{{ route('rooms.index') }}" class="btn btn-secondary btn-lg">Zobacz pokoje</a>
				@endguest
			</div>

			@guest
				<p class="mt-4 text-sm text-mist-500">
					Masz już konto? <a href="/login" class="font-medium text-brand-300 hover:text-brand-200">Zaloguj się</a>
				</p>
			@endguest
		</div>
	</section>

	{{-- Jak to działa --}}
	<section class="mt-12 grid gap-4 sm:grid-cols-3">
		@php
			$steps = [
				['1', 'Stwórz pokój', 'Nadaj nazwę i zdecyduj, czy ma być publiczny, czy tylko na zaproszenia.'],
				['2', 'Zaproś znajomych', 'Wygeneruj link zapraszający i wyślij go komu chcesz.'],
				['3', 'Włącz seans', 'Prześlij film i rozpocznij transmisję — pauza i play działają u wszystkich naraz.'],
			];
		@endphp

		@foreach ($steps as [$number, $heading, $description])
			<div class="card card-body card-hover">
				<span class="mb-4 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500/25 to-flare-500/25 text-sm font-bold text-brand-300">
					{{ $number }}
				</span>
				<h2 class="section-title">{{ $heading }}</h2>
				<p class="mt-2 text-sm leading-relaxed text-mist-300">{{ $description }}</p>
			</div>
		@endforeach
	</section>

	{{-- Funkcje --}}
	<section class="mt-4 grid gap-4 sm:grid-cols-2">
		<div class="card card-body flex flex-row items-start gap-4">
			<span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-brand-300">
				<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/></svg>
			</span>
			<div>
				<h2 class="section-title">Czat na żywo</h2>
				<p class="mt-1.5 text-sm leading-relaxed text-mist-300">
					Wiadomości pojawiają się natychmiast u wszystkich w pokoju — bez odświeżania strony.
				</p>
			</div>
		</div>

		<div class="card card-body flex flex-row items-start gap-4">
			<span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-flare-400">
				<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4M12 18v4M4.9 4.9l2.9 2.9M16.2 16.2l2.9 2.9M2 12h4M18 12h4M4.9 19.1l2.9-2.9M16.2 7.8l2.9-2.9"/></svg>
			</span>
			<div>
				<h2 class="section-title">Wspólne sterowanie</h2>
				<p class="mt-1.5 text-sm leading-relaxed text-mist-300">
					Właściciel pokoju steruje odtwarzaniem, a play i pauza rozchodzą się do wszystkich widzów.
				</p>
			</div>
		</div>
	</section>

</div>
@endsection
