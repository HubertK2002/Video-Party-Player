@extends('layouts.app')

@section('title', 'Logowanie — Reverb Broadcast')

@section('content')
<div class="mx-auto flex w-full max-w-md flex-col justify-center py-10">

	<div class="mb-8 text-center">
		<h1 class="page-title">Witaj ponownie</h1>
		<p class="mt-2 text-sm text-mist-300">Zaloguj się, żeby wrócić do swoich pokoi.</p>
	</div>

	<div class="card card-body animate-in">
		<form method="POST" action="/login" class="space-y-1">
			@csrf

			<div class="field">
				<label for="email" class="label">Email</label>
				<input type="email"
				       id="email"
				       name="email"
				       value="{{ old('email') }}"
				       placeholder="ty@example.com"
				       autocomplete="email"
				       class="input @error('email') input-error @enderror"
				       required>
				@error('email')
					<p class="field-error">
						<svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
						{{ $message }}
					</p>
				@enderror
			</div>

			<div class="field">
				<label for="password" class="label">Hasło</label>
				<input type="password"
				       id="password"
				       name="password"
				       placeholder="••••••••"
				       autocomplete="current-password"
				       class="input @error('password') input-error @enderror"
				       required>
				@error('password')
					<p class="field-error">
						<svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
						{{ $message }}
					</p>
				@enderror
			</div>

			<button type="submit" class="btn btn-primary mt-2 w-full">Zaloguj się</button>
		</form>

		<div class="divider">lub</div>

		<p class="text-center text-sm text-mist-300">
			Nie masz jeszcze konta?
			<a href="/register" class="font-medium text-brand-300 hover:text-brand-200">Zarejestruj się</a>
		</p>
	</div>
</div>
@endsection
