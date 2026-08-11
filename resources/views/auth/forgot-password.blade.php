@extends('layouts.app')

@section('title', 'Reset hasła — Reverb Broadcast')

@section('content')
<div class="mx-auto flex w-full max-w-md flex-col justify-center py-10">

	<div class="mb-8 text-center">
		<h1 class="page-title">Nie pamiętasz hasła?</h1>
		<p class="mt-2 text-sm text-mist-300">Podaj swój email, a wyślemy link do ustawienia nowego.</p>
	</div>

	<div class="card card-body animate-in">
		<form method="POST" action="{{ route('password.email') }}" class="space-y-1">
			@csrf

			<div class="field">
				<label for="email" class="label">Email</label>
				<input type="email"
				       id="email"
				       name="email"
				       value="{{ old('email') }}"
				       placeholder="ty@example.com"
				       autocomplete="email"
				       autofocus
				       class="input @error('email') input-error @enderror"
				       required>
				@error('email')
					<p class="field-error">
						<svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
						{{ $message }}
					</p>
				@enderror
			</div>

			<button type="submit" class="btn btn-primary mt-2 w-full">Wyślij link</button>
		</form>

		<div class="divider">lub</div>

		<p class="text-center text-sm text-mist-300">
			Przypomniało Ci się?
			<a href="/login" class="font-medium text-brand-300 hover:text-brand-200">Wróć do logowania</a>
		</p>
	</div>
</div>
@endsection
