<header class="sticky top-0 z-40 border-b border-white/5 bg-ink-950/70 backdrop-blur-xl">
	<nav class="mx-auto flex h-16 max-w-6xl items-center gap-3 px-4 sm:px-6 lg:px-8">

		<a href="{{ route('home') }}" class="group flex items-center gap-2.5">
			<img src="/icon.png" alt="" class="h-9 w-9 rounded-xl shadow-lg shadow-black/40 transition group-hover:scale-105">
			<span class="hidden text-base font-semibold tracking-tight sm:block">
				Reverb <span class="gradient-text">Broadcast</span>
			</span>
		</a>

		<div class="ml-2 hidden items-center gap-1 md:flex">
			<a href="{{ route('home') }}" class="nav-link @if(request()->routeIs('home')) nav-link-active @endif">Start</a>
			<a href="{{ route('rooms.index') }}" class="nav-link @if(request()->routeIs('rooms.index')) nav-link-active @endif">Pokoje</a>
			@auth
				<a href="{{ route('rooms.owned') }}" class="nav-link @if(request()->routeIs('rooms.owned')) nav-link-active @endif">Moje pokoje</a>
			@endauth
		</div>

		<div class="ml-auto flex items-center gap-2">
			@auth
				<a href="{{ route('rooms.create') }}" class="btn btn-primary btn-sm hidden sm:inline-flex">
					<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
					Nowy pokój
				</a>
				<div class="flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 py-1 pr-1 pl-2.5">
					<span class="avatar h-6 w-6">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
					<span class="hidden max-w-28 truncate text-sm text-mist-300 sm:block">{{ auth()->user()->name }}</span>
					<a href="/logout" class="btn btn-ghost btn-sm" title="Wyloguj się">
						<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
						<span class="sr-only">Wyloguj się</span>
					</a>
				</div>
			@endauth

			@guest
				<a href="/login" class="btn btn-ghost btn-sm">Zaloguj się</a>
				<a href="/register" class="btn btn-primary btn-sm">Zarejestruj się</a>
			@endguest
		</div>
	</nav>

	<div class="flex items-center gap-1 border-t border-white/5 px-4 py-2 md:hidden">
		<a href="{{ route('home') }}" class="nav-link @if(request()->routeIs('home')) nav-link-active @endif">Start</a>
		<a href="{{ route('rooms.index') }}" class="nav-link @if(request()->routeIs('rooms.index')) nav-link-active @endif">Pokoje</a>
		@auth
			<a href="{{ route('rooms.owned') }}" class="nav-link @if(request()->routeIs('rooms.owned')) nav-link-active @endif">Moje pokoje</a>
			<a href="{{ route('rooms.create') }}" class="nav-link">Nowy</a>
		@endauth
	</div>
</header>
