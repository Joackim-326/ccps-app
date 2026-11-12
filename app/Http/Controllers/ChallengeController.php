<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChallengeController extends Controller
{
    private function assignees()
    {
        return User::whereIn('role', [User::OFFICER, User::CREATOR])->orderBy('role')->orderBy('first_name')->get();
    }

    public function create()
    {
        return view('challenges.create', ['categories' => Challenge::CATEGORIES, 'assignees' => $this->assignees()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'index_number' => ['required', 'string', 'max:60'],
            'category' => ['required', Rule::in(Challenge::CATEGORIES)],
            'other_category' => ['nullable', 'required_if:category,Other', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'assigned_to' => ['required', Rule::exists('users', 'id')->whereIn('role', [User::OFFICER, User::CREATOR])],
        ], ['other_category.required_if' => 'Please specify the challenge when choosing "Other".']);

        if ($data['category'] !== 'Other') {
            $data['other_category'] = null;
        }

        $challenge = Challenge::create($data + ['created_by' => $request->user()->id, 'status' => Challenge::IN_PROGRESS]);
        $to = User::find($data['assigned_to']);
        ActivityLog::record('challenge_created', "{$challenge->reference} ({$challenge->index_number}) submitted to {$to->name}");

        return redirect()->route('challenges.index')->with('status', "Challenge {$challenge->reference} submitted to {$to->name}.");
    }

    /** Own list: receptionist => challenges they registered; officer/creator => challenges assigned to them. */
    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->query('status') === Challenge::ATTENDED ? Challenge::ATTENDED : Challenge::IN_PROGRESS;

        $challenges = Challenge::with(['creator', 'assignee'])
            ->where('status', $status)
            ->when($user->isReceptionist(), fn ($q) => $q->where('created_by', $user->id), fn ($q) => $q->where('assigned_to', $user->id))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->query('q').'%';
                $q->where(fn ($w) => $w->where('index_number', 'like', $term)->orWhere('reference', 'like', $term));
            })
            ->latest()->paginate(10)->withQueryString();

        return view('challenges.index', [
            'challenges' => $challenges,
            'status' => $status,
            'assignees' => $user->isReceptionist() ? $this->assignees() : collect(),
        ]);
    }

    public function reassign(Request $request, Challenge $challenge)
    {
        $user = $request->user();
        abort_unless($user->isCreator() || ($user->isReceptionist() && $challenge->created_by === $user->id), 403);
        abort_if($challenge->status === Challenge::ATTENDED, 422, 'Attended challenges cannot be reassigned.');

        $data = $request->validate([
            'assigned_to' => ['required', Rule::exists('users', 'id')->whereIn('role', [User::OFFICER, User::CREATOR])],
        ]);

        $to = User::find($data['assigned_to']);
        $challenge->update(['assigned_to' => $to->id]);
        ActivityLog::record('challenge_reassigned', "{$challenge->reference} sent to {$to->name}");

        return back()->with('status', "Challenge {$challenge->reference} sent to {$to->name}.");
    }

    public function attend(Request $request, Challenge $challenge)
    {
        abort_unless($request->user()->hasRole(User::OFFICER, User::CREATOR) && $challenge->assigned_to === $request->user()->id, 403);

        $challenge->update(['status' => Challenge::ATTENDED, 'attended_at' => now()]);
        ActivityLog::record('challenge_attended', "{$challenge->reference} marked as attended");

        return back()->with('status', "Challenge {$challenge->reference} marked as attended.");
    }
}
