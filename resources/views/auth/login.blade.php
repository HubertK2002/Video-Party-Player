<div>
	@error('email')
    <div class="text-red-500">
        {{ $message }}
    </div>
	@enderror
	<form method="POST" action="/login">
		@csrf
	<input type="email" name="email" placeholder="Email" class="input input-bordered w-full max-w-xs" required>
	<input type="password" name="password" placeholder="Hasło" class="input input-bordered w-full max-w-xs mt-4" required>
	<button type="submit" class="btn btn-primary w-full max-w-xs mt-4">Zaloguj się</button>
	</form>
	<p class="text-center text-sm mt-4">
		Już nie masz konta?
		<a href="/register" class="link link-primary">Zarejestruj się</a>
	</p>
</div>