<!DOCTYPE html>
<html lang="pl" class="h-full">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>@yield('title', 'Reverb Broadcast')</title>
	<link rel="icon" href="/favicon.ico">
	@vite(['resources/css/app.css', 'resources/js/app.js'])
	@stack('head')
</head>
<body class="flex min-h-full flex-col">

	@unless (View::hasSection('bare'))
		@include('partials.navbar')
	@endunless

	<main class="flex-1 @unless(View::hasSection('bare')) px-4 py-8 sm:px-6 lg:px-8 @endunless">
		@unless (View::hasSection('bare'))
			<div class="mx-auto w-full max-w-6xl">
				@include('partials.flash')
			</div>
		@endunless

		@yield('content')
	</main>

	@unless (View::hasSection('bare'))
		<footer class="border-t border-white/5 px-4 py-6 sm:px-6 lg:px-8">
			<div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 text-xs text-mist-500 sm:flex-row">
				<p>Reverb Broadcast — oglądaj filmy razem ze znajomymi.</p>
				<p>Zbudowane na Laravel + Reverb</p>
			</div>
		</footer>
	@endunless

	@stack('scripts')
</body>
</html>
