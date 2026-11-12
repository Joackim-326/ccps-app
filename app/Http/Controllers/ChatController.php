<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ContactNote;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    private function authorizeChat(Request $request, User $other): void
    {
        abort_unless($request->user()->canChatWith($other), 403, 'You cannot chat with this user.');
    }

    private function payload(Message $m, int $me): array
    {
        return [
            'id' => $m->id,
            'mine' => $m->sender_id === $me,
            'body' => $m->body,
            'time' => $m->created_at->format('d M, H:i'),
        ];
    }

    public function index(Request $request, ?User $user = null)
    {
        $me = $request->user();
        $contacts = $me->chatContacts();

        $unread = Message::where('recipient_id', $me->id)->where('recipient_deleted', false)->whereNull('read_at')
            ->selectRaw('sender_id, count(*) as c')->groupBy('sender_id')->pluck('c', 'sender_id');
        $notes = ContactNote::where('owner_id', $me->id)->pluck('description', 'contact_id');

        $messages = collect();
        if ($user) {
            $this->authorizeChat($request, $user);
            Message::where('sender_id', $user->id)->where('recipient_id', $me->id)->whereNull('read_at')->update(['read_at' => now()]);
            $messages = Message::thread($me->id, $user->id)->orderBy('id')->get()->map(fn ($m) => $this->payload($m, $me->id));
        }

        return view('chat.index', compact('contacts', 'unread', 'notes', 'user', 'messages'));
    }

    /** Polling endpoint: returns messages newer than ?after=ID and marks incoming ones as read. */
    public function poll(Request $request, User $user)
    {
        $this->authorizeChat($request, $user);
        $me = $request->user();

        $new = Message::thread($me->id, $user->id)->where('id', '>', (int) $request->query('after', 0))->orderBy('id')->get();
        Message::where('sender_id', $user->id)->where('recipient_id', $me->id)->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['messages' => $new->map(fn ($m) => $this->payload($m, $me->id))->values()]);
    }

    public function store(Request $request, User $user)
    {
        $this->authorizeChat($request, $user);
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $m = Message::create(['sender_id' => $request->user()->id, 'recipient_id' => $user->id, 'body' => trim($data['body'])]);

        return $request->wantsJson()
            ? response()->json(['message' => $this->payload($m, $request->user()->id)])
            : redirect()->route('chat.index', $user);
    }

    public function unread(Request $request)
    {
        return response()->json(['count' => $request->user()->unreadMessageCount()]);
    }

    public function clear(Request $request, User $user)
    {
        $this->authorizeChat($request, $user);
        $me = $request->user();
        $scope = $request->validate(['scope' => ['required', 'in:me,all']])['scope'];

        if ($scope === 'all') {
            Message::where(fn ($q) => $q->where('sender_id', $me->id)->where('recipient_id', $user->id))
                ->orWhere(fn ($q) => $q->where('sender_id', $user->id)->where('recipient_id', $me->id))->delete();
        } else {
            Message::where('sender_id', $me->id)->where('recipient_id', $user->id)->update(['sender_deleted' => true]);
            Message::where('sender_id', $user->id)->where('recipient_id', $me->id)->update(['recipient_deleted' => true]);
        }

        ActivityLog::record('chat_cleared', "Cleared chat with {$user->name} ".($scope === 'all' ? 'for everyone' : 'for self'));
        return redirect()->route('chat.index', $user)->with('status', $scope === 'all' ? 'Chat cleared for everyone.' : 'Chat cleared for you.');
    }

    public function note(Request $request, User $user)
    {
        $this->authorizeChat($request, $user);
        $data = $request->validate(['description' => ['nullable', 'string', 'max:500']]);

        if (blank($data['description'] ?? null)) {
            ContactNote::where('owner_id', $request->user()->id)->where('contact_id', $user->id)->delete();
        } else {
            ContactNote::updateOrCreate(['owner_id' => $request->user()->id, 'contact_id' => $user->id], ['description' => $data['description']]);
        }

        return back()->with('status', 'Contact description saved.');
    }
}
