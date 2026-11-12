<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    public function index(Request $request)
    {
        $challenges = Challenge::with(['creator', 'assignee'])
            ->when(in_array($request->query('status'), [Challenge::IN_PROGRESS, Challenge::ATTENDED]), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('assignee'), fn ($q) => $q->where('assigned_to', $request->query('assignee')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $t = '%'.$request->query('q').'%';
                $q->where(fn ($w) => $w->where('index_number', 'like', $t)->orWhere('reference', 'like', $t)->orWhere('description', 'like', $t));
            })
            ->latest()->paginate(12)->withQueryString();

        return view('admin.challenges', [
            'challenges' => $challenges,
            'assignees' => User::whereIn('role', [User::OFFICER, User::CREATOR])->orderBy('first_name')->get(),
        ]);
    }

    public function destroy(Challenge $challenge)
    {
        ActivityLog::record('challenge_deleted', "Deleted {$challenge->reference} ({$challenge->index_number})");
        $challenge->delete();
        return back()->with('status', 'Challenge deleted.');
    }
}
