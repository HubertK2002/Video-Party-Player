<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomInvitation;
use App\Models\RoomUser;
use Illuminate\Support\Str;

class RoomInvitationController extends Controller
{
    public function generateLink(Room $room)
    {
        $user = auth()->user();
        if ($room->owner_id !== $user->id) {
            return redirect()->route('rooms.show', $room->id)->with('error', 'Nie masz uprawnień do wygenerowania linku do zaproszenia.');
        }

        // Generate a unique invite link
        $inviteLink = route('rooms.join', ['room' => $room->id, 'token' => Str::random(32)]);
        $invitation = new RoomInvitation();
        $invitation->room_id = $room->id;
        $invitation->invitation_code = Str::random(32);
        $invitation->save();

        return redirect()->route('rooms.show', $room->id)->with('success', 'Link do zaproszenia został wygenerowany pomyślnie!');
    }

    public function join(Room $room, $token)
    {
        // Here you can implement logic to validate the token if needed
        if ($room->invitations()->where('invitation_code', $token)->doesntExist()) {
            return redirect()->route('rooms.index')->with('error', 'Nieprawidłowy token zaproszenia.');
        }

        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Musisz być zalogowany, aby dołączyć do pokoju.');
        }

        // Check if the user is already a member of the room
        if ($room->users()->where('user_id', $user->id)->exists()) {
            return redirect()->route('rooms.show', $room->id)->with('info', 'Jesteś już członkiem tego pokoju.');
        }

        // Add the user to the room
        $roomUser = new RoomUser();
        $roomUser->room_id = $room->id;
        $roomUser->user_id = $user->id;
        $roomUser->save();

        return redirect()->route('rooms.show', $room->id)->with('success', 'Dołączyłeś do pokoju pomyślnie!');
    }

    public function index(Room $room)
    {
        $user = auth()->user();
        if ($room->owner_id !== $user->id) {
            return redirect()->route('rooms.show', $room->id)->with('error', 'Nie masz uprawnień do przeglądania zaproszeń do tego pokoju.');
        }

        $invitations = $room->invitations()->get();
        return view('rooms.invitations', compact('room', 'invitations'));
    }

    public function store(Room $room)
    {
        $user = auth()->user();

        if ($room->owner_id !== $user->id) {
            return error()->json(['error' => 'Nie masz uprawnień do generowania zaproszeń do tego pokoju.'], 403);
        }

        $user = auth()->user();

        if ($room->owner_id !== $user->id) {
            return error()->json(['error' => 'Nie masz uprawnień do generowania zaproszeń do tego pokoju.'], 403);
        }

        // Generate a unique invitation code
        $invitationCode = Str::random(32);

        // Create a new RoomInvitation
        $invitation = new RoomInvitation();
        $invitation->room_id = $room->id;
        $invitation->invitation_code = $invitationCode;
        $invitation->save();

        return json_encode(['success' => true, 'invitation_code' => $invitationCode]);
    }

    public function destroy(Room $room, RoomInvitation $invitation)
    {
        $user = auth()->user();

        if ($room->owner_id !== $user->id) {
            return redirect()->route('rooms.show', $room->id)->with('error', 'Nie masz uprawnień do usuwania zaproszeń do tego pokoju.');
        }

        // Check if the invitation belongs to the room
        if ($invitation->room_id !== $room->id) {
            return redirect()->route('rooms.show', $room->id)->with('error', 'To zaproszenie nie należy do tego pokoju.');
        }

        // Delete the invitation
        $invitation->delete();

        return redirect()->route('rooms.invitations', $room->id)->with('success', 'Zaproszenie zostało usunięte pomyślnie!');
    }
}
