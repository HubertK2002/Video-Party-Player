<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RoomVideoController extends Controller
{
    public function upload(Request $request, Room $room)
    {
        $user = auth()->user();
        if ($room->owner_id !== $user->id) {
            return redirect()->route('rooms.show', $room->id)->with('error', 'Nie masz uprawnień do przesyłania wideo do tego pokoju.');
        }

        $request->validate([
            'video' => 'required|file|mimetypes:video/mp4', // 2GB limit
        ]);

        $videoFile = $request->file('video');

        $path = Storage::disk('public')->putFileAs(
            null,
            $videoFile,
            $room->id,
        );

        return redirect()->route('rooms.show', $room->id)->with('success', 'Wideo zostało przesłane pomyślnie!');
    }

    public function init(Request $request, Room $room)
    {
        $user = auth()->user();
        if (!$user || $room->owner_id !== $user->id) {
            return response()->json(['success' => false, 'error' => 'Nie masz uprawnień do przesyłania wideo do tego pokoju.'], 403);
        }

        $validated = $request->validate([
            'size' => 'required|integer|min:1|max:' . (8 * 1024 * 1024 * 1024),
            'chunks' => 'required|integer|min:1|max:200000',
        ]);

        $directory = storage_path('app/chunked-uploads');
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        foreach (glob($directory . '/*') as $stale) {
            if (is_file($stale) && filemtime($stale) < now()->subDay()->getTimestamp()) {
                @unlink($stale);
            }
        }

        $uploadId = Str::random(40);

        file_put_contents($directory . '/' . $uploadId . '.json', json_encode([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'size' => (int) $validated['size'],
            'chunks' => (int) $validated['chunks'],
            'received' => 0,
        ]));

        return response()->json(['success' => true, 'upload_id' => $uploadId]);
    }

    public function chunk(Request $request, Room $room)
    {
        $user = auth()->user();
        if (!$user || $room->owner_id !== $user->id) {
            return response()->json(['success' => false, 'error' => 'Nie masz uprawnień do przesyłania wideo do tego pokoju.'], 403);
        }

        $validated = $request->validate([
            'upload_id' => ['required', 'string', 'regex:/^[A-Za-z0-9]{40}$/'],
            'index' => 'required|integer|min:0',
            'chunk' => 'required|file',
        ]);

        $directory = storage_path('app/chunked-uploads');
        $metaPath = $directory . '/' . $validated['upload_id'] . '.json';
        $partPath = $directory . '/' . $validated['upload_id'] . '.part';

        if (!is_file($metaPath)) {
            return response()->json(['success' => false, 'error' => 'Transfer wygasł lub nie istnieje. Rozpocznij przesyłanie od nowa.'], 404);
        }

        $meta = json_decode(file_get_contents($metaPath), true);

        if ($meta['room_id'] !== $room->id || $meta['user_id'] !== $user->id) {
            return response()->json(['success' => false, 'error' => 'Ten transfer nie należy do tego pokoju.'], 403);
        }

        if ((int) $validated['index'] !== $meta['received']) {
            return response()->json([
                'success' => false,
                'error' => 'Kawałki przyszły w złej kolejności.',
                'expected' => $meta['received'],
            ], 409);
        }

        $source = fopen($request->file('chunk')->getRealPath(), 'rb');
        $target = fopen($partPath, $meta['received'] === 0 ? 'wb' : 'ab');
        stream_copy_to_stream($source, $target);
        fclose($source);
        fclose($target);

        clearstatcache(true, $partPath);

        if (filesize($partPath) > $meta['size']) {
            @unlink($partPath);
            @unlink($metaPath);
            return response()->json(['success' => false, 'error' => 'Przesłano więcej danych niż zapowiedziano.'], 422);
        }

        $meta['received']++;
        file_put_contents($metaPath, json_encode($meta));

        return response()->json(['success' => true, 'received' => $meta['received']]);
    }

    public function finish(Request $request, Room $room)
    {
        $user = auth()->user();
        if (!$user || $room->owner_id !== $user->id) {
            return response()->json(['success' => false, 'error' => 'Nie masz uprawnień do przesyłania wideo do tego pokoju.'], 403);
        }

        $validated = $request->validate([
            'upload_id' => ['required', 'string', 'regex:/^[A-Za-z0-9]{40}$/'],
        ]);

        $directory = storage_path('app/chunked-uploads');
        $metaPath = $directory . '/' . $validated['upload_id'] . '.json';
        $partPath = $directory . '/' . $validated['upload_id'] . '.part';

        if (!is_file($metaPath) || !is_file($partPath)) {
            return response()->json(['success' => false, 'error' => 'Transfer wygasł lub nie istnieje. Rozpocznij przesyłanie od nowa.'], 404);
        }

        $meta = json_decode(file_get_contents($metaPath), true);

        if ($meta['room_id'] !== $room->id || $meta['user_id'] !== $user->id) {
            return response()->json(['success' => false, 'error' => 'Ten transfer nie należy do tego pokoju.'], 403);
        }

        clearstatcache(true, $partPath);

        if ($meta['received'] !== $meta['chunks'] || filesize($partPath) !== $meta['size']) {
            return response()->json(['success' => false, 'error' => 'Plik dotarł niekompletny. Spróbuj jeszcze raz.'], 422);
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($partPath);

        if ($mimeType !== 'video/mp4') {
            @unlink($partPath);
            @unlink($metaPath);
            return response()->json(['success' => false, 'error' => 'Obsługiwane są tylko pliki MP4 (wykryto: ' . $mimeType . ').'], 422);
        }

        Storage::disk('public')->delete($room->id);
        $stream = fopen($partPath, 'rb');
        Storage::disk('public')->writeStream((string) $room->id, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        @unlink($partPath);
        @unlink($metaPath);

        session()->flash('success', 'Wideo zostało przesłane pomyślnie!');

        return response()->json([
            'success' => true,
            'redirect' => route('rooms.show', $room->id),
        ]);
    }

    public function stream(Request $request, Room $room)
    {
        $disk = Storage::disk('public');

        if (!$disk->exists((string) $room->id)) {
            abort(404, 'W tym pokoju nie ma jeszcze filmu.');
        }

        return response()->file($disk->path((string) $room->id), [
            'Content-Type' => 'video/mp4',
            'Accept-Ranges' => 'bytes',
        ]);
    }
}
