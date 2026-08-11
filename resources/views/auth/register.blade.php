@extends('layouts.app')

@section('title', 'Rejestracja — Reverb Broadcast')

@section('content')
<div class="mx-auto flex w-full max-w-md flex-col justify-center py-10">

	<div class="mb-8 text-center">
		<h1 class="page-title">Stwórz konto</h1>
		<p class="mt-2 text-sm text-mist-300">Kilka sekund i możesz zapraszać znajomych na seans.</p>
	</div>

	<div class="card card-body animate-in">
		<form method="POST" action="/register" class="space-y-1">
			@csrf

			<div class="field">
				<label for="name" class="label">Nazwa</label>
				<input type="text"
				       id="name"
				       name="name"
				       value="{{ old('name') }}"
				       placeholder="Jan Kowalski"
				       autocomplete="name"
				       class="input @error('name') input-error @enderror"
				       required>
				@error('name')
					<p class="field-error">
						<svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
						{{ $message }}
					</p>
				@enderror
			</div>

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
				       autocomplete="new-password"
				       class="input @error('password') input-error @enderror"
				       required>
				@error('password')
					<p class="field-error">
						<svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
						{{ $message }}
					</p>
				@else
					<p class="muted mt-1.5">Minimum 8 znaków.</p>
				@enderror
			</div>

			<div class="field">
				<label for="password_confirmation" class="label">Potwierdź hasło</label>
				<input type="password"
				       id="password_confirmation"
				       name="password_confirmation"
				       placeholder="••••••••"
				       autocomplete="new-password"
				       class="input"
				       required>
			</div>

			<button type="submit" class="btn btn-primary mt-2 w-full">Zarejestruj się</button>
		</form>

		<div class="divider">lub</div>

		<p class="text-center text-sm text-mist-300">
			Masz już konto?
			<a href="/login" class="font-medium text-brand-300 hover:text-brand-200">Zaloguj się</a>
		</p>
	</div>
</div>
@endsection
