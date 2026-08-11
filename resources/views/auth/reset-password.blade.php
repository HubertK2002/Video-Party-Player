@extends('layouts.app')

@section('title', 'Nowe hasło — Reverb Broadcast')

@section('content')
<div class="mx-auto flex w-full max-w-md flex-col justify-center py-10">

	<div class="mb-8 text-center">
		<h1 class="page-title">Ustaw nowe hasło</h1>
		<p class="mt-2 text-sm text-mist-300">Wpisz nowe hasło do swojego konta.</p>
	</div>

	<div class="card card-body animate-in">
		<form method="POST" action="{{ route('password.update') }}" class="space-y-1">
			@csrf
			<input type="hidden" name="token" value="{{ $token }}">

			<div class="field">
				<label for="email" class="label">Email</label>
				<input type="email"
				       id="email"
				       name="email"
				       value="{{ old('email', $email) }}"
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
				<label for="password" class="label">Nowe hasło</label>
				<input type="password"
				       id="password"
				       name="password"
				       placeholder="••••••••"
				       autocomplete="new-password"
				       autofocus
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
				<label for="password_confirmation" class="label">Potwierdź nowe hasło</label>
				<input type="password"
				       id="password_confirmation"
				       name="password_confirmation"
				       placeholder="••••••••"
				       autocomplete="new-password"
				       class="input"
				       required>
			</div>

			<button type="submit" class="btn btn-primary mt-2 w-full">Zmień hasło</button>
		</form>
	</div>
</div>
@endsection
