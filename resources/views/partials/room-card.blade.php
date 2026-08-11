@php
	$isOwner = auth()->check() && $room->owner_id === auth()->id();
@endphp

<a href="{{ route('rooms.show', $room->id) }}" class="card card-hover group flex flex-col p-5">
	<div class="mb-4 flex items-start justify-between gap-3">
		<span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-gradient-to-br from-brand-500/20 to-flare-500/20 text-brand-300">
			<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="m10 8 6 4-6 4V8Z"/><rect x="2" y="4" width="20" height="16" rx="3"/>
			</svg>
		</span>
		<div class="flex flex-wrap justify-end gap-1.5">
			@if ($room->is_public)
				<span class="badge badge-public">
					<svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20Z"/></svg>
					Publiczny
				</span>
			@else
				<span class="badge badge-private">
					<svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
					Prywatny
				</span>
			@endif
			@if ($isOwner)
				<span class="badge badge-owner">Twój</span>
			@endif
		</div>
	</div>

	<h3 class="truncate text-lg font-semibold tracking-tight text-mist-100 transition group-hover:text-brand-300">
		{{ $room->name }}
	</h3>

	<div class="mt-2 flex items-center gap-2 text-sm text-mist-500">
		<span class="avatar h-5 w-5 text-[10px]">{{ mb_substr($room->owner->name, 0, 1) }}</span>
		<span class="truncate">{{ $room->owner->name }}</span>
	</div>

	<div class="mt-5 flex items-center gap-1.5 text-sm font-medium text-brand-300 opacity-0 transition group-hover:opacity-100">
		Wejdź do pokoju
		<svg class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
	</div>
</a>
