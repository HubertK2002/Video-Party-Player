@extends('layouts.app')

@section('title', 'Nowy pokój — Reverb Broadcast')

@section('content')
<div class="mx-auto w-full max-w-xl">

	<div class="mb-8">
		<a href="{{ route('rooms.index') }}" class="btn btn-ghost btn-sm -ml-3 mb-3">
			<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
			Powrót do listy pokoi
		</a>
		<h1 class="page-title">Utwórz nowy pokój</h1>
		<p class="mt-1.5 text-sm text-mist-300">Nazwij pokój i wybierz, kto może do niego wejść.</p>
	</div>

	@if ($errors->any())
		<div class="alert alert-error">
			<svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
			<ul class="space-y-0.5">
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<div class="card card-body animate-in">
		<form action="{{ route('rooms.store') }}" method="POST">
			@csrf

			<div class="field">
				<label for="name" class="label">Nazwa pokoju</label>
				<input type="text"
				       name="name"
				       id="name"
				       value="{{ old('name') }}"
				       placeholder="np. Piątkowy seans"
				       maxlength="255"
				       class="input @error('name') input-error @enderror"
				       required>
			</div>

			<fieldset class="field">
				<legend class="label">Widoczność</legend>

				<div class="grid gap-3 sm:grid-cols-2">
					<label class="cursor-pointer">
						<input type="radio" name="is_public" value="1" class="peer sr-only" @checked(old('is_public') == '1')>
						<div class="h-full rounded-xl border border-white/10 bg-ink-900/60 p-4 transition
						            hover:border-white/20 peer-checked:border-brand-500/70 peer-checked:bg-brand-500/10
						            peer-focus-visible:ring-2 peer-focus-visible:ring-brand-400">
							<span class="badge badge-public mb-2">
								<svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20Z"/></svg>
								Publiczny
							</span>
							<p class="text-xs leading-relaxed text-mist-300">Widoczny na liście — każdy może wejść.</p>
						</div>
					</label>

					<label class="cursor-pointer">
						<input type="radio" name="is_public" value="0" class="peer sr-only" @checked(old('is_public', '0') == '0')>
						<div class="h-full rounded-xl border border-white/10 bg-ink-900/60 p-4 transition
						            hover:border-white/20 peer-checked:border-brand-500/70 peer-checked:bg-brand-500/10
						            peer-focus-visible:ring-2 peer-focus-visible:ring-brand-400">
							<span class="badge badge-private mb-2">
								<svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
								Prywatny
							</span>
							<p class="text-xs leading-relaxed text-mist-300">Wejście tylko z linkiem zapraszającym.</p>
						</div>
					</label>
				</div>
			</fieldset>

			<div class="mt-6 flex items-center gap-3">
				<button type="submit" class="btn btn-primary">
					<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
					Utwórz pokój
				</button>
				<a href="{{ route('rooms.index') }}" class="btn btn-ghost">Anuluj</a>
			</div>
		</form>
	</div>

</div>
@endsection
