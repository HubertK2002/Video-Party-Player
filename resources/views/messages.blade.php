@extends('layouts.app')

@section('title', 'Podgląd zdarzeń — Reverb Broadcast')

@section('content')
<div class="mx-auto w-full max-w-4xl">

	<div class="mb-6 flex items-center gap-3">
		<span class="live-dot"></span>
		<div>
			<h1 class="page-title">Nasłuch zdarzeń</h1>
			<p class="mt-1.5 text-sm text-mist-300">Kanał <code class="rounded bg-white/5 px-1.5 py-0.5 font-mono text-xs">public-messages</code> — podgląd na żywo.</p>
		</div>
	</div>

	<div class="card overflow-hidden">
		<div class="flex items-center gap-2 border-b border-white/5 px-5 py-3">
			<span class="h-2.5 w-2.5 rounded-full bg-red-400/70"></span>
			<span class="h-2.5 w-2.5 rounded-full bg-amber-400/70"></span>
			<span class="h-2.5 w-2.5 rounded-full bg-emerald-400/70"></span>
			<span class="ml-2 text-xs text-mist-500">message.sent</span>
		</div>
		<pre id="output" class="max-h-[28rem] overflow-auto p-5 font-mono text-xs leading-relaxed text-mist-300">Czekam na zdarzenia…</pre>
	</div>

</div>
@endsection

@push('scripts')
<script>
	document.addEventListener("DOMContentLoaded", function () {
		window.Echo.channel("public-messages")
			.listen(".message.sent", (event) => {
				console.log("Received:", event);
				document.getElementById("output").textContent = JSON.stringify(event, null, 2);
			});
	});
</script>
@endpush
