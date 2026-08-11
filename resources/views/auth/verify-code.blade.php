@extends('layouts.app')

@section('title', 'Potwierdź email — Reverb Broadcast')

@section('content')
<div class="mx-auto flex w-full max-w-md flex-col justify-center py-10">

	<div class="mb-8 text-center">
		<h1 class="page-title">Potwierdź swój email</h1>
		<p class="mt-2 text-sm text-mist-300">
			Wysłaliśmy 6-cyfrowy kod na <span class="font-medium text-mist-100">{{ $user->email }}</span>.
		</p>
	</div>

	<div class="card card-body animate-in">
		<form method="POST" action="{{ route('verification.verify') }}" class="space-y-1">
			@csrf

			<div class="field">
				<label for="code" class="label">Kod z wiadomości</label>
				<input type="text"
				       id="code"
				       name="code"
				       inputmode="numeric"
				       pattern="[0-9]*"
				       maxlength="6"
				       placeholder="123456"
				       autocomplete="one-time-code"
				       autofocus
				       class="input text-center text-2xl tracking-[0.5em] @error('code') input-error @enderror"
				       required>
				@error('code')
					<p class="field-error">
						<svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
						{{ $message }}
					</p>
				@else
					<p class="muted mt-1.5">Kod jest ważny przez 10 minut.</p>
				@enderror
			</div>

			<button type="submit" class="btn btn-primary mt-2 w-full">Potwierdź konto</button>
		</form>

		<div class="divider">nie dotarł?</div>

		<form method="POST" action="{{ route('verification.resend') }}">
			@csrf
			<button type="submit" class="btn btn-secondary w-full">Wyślij kod ponownie</button>
		</form>

		<p class="mt-4 text-center text-sm text-mist-300">
			Zły adres?
			<a href="/register" class="font-medium text-brand-300 hover:text-brand-200">Zarejestruj się jeszcze raz</a>
		</p>
	</div>
</div>
@endsection
