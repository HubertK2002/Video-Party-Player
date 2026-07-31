<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Reverb Broadcast - Rooms</title>
	@vite(['resources/js/app.js']) {{-- Load JS --}}
	</head>
	<body>
		<h1 class="text-3xl font-bold mb-4">Utwórz nowy pokój</h1>
		@if ($errors->any())
		    <div class="alert alert-danger">
		        <ul class="mb-0">
		            @foreach ($errors->all() as $error)
		                <li>{{ $error }}</li>
		            @endforeach
		        </ul>
		    </div>
		@endif
		<form action="{{ route('rooms.store') }}" method="POST">
			@csrf
			<div class="mb-4">
				<label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nazwa pokoju</label>
				<input type="text" name="name" id="name" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
			</div>
			<div class="mb-4">
				<label for="is_public" class="block text-gray-700 text-sm font-bold mb-2">Publiczny</label>
				<input type="checkbox" name="is_public" id="is_public" class="mr-2 leading-tight">
				<input type="hidden" name="is_public" value="0">
			</div>
			<button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Utwórz pokój</button>
		</form>
	</body>
</html>