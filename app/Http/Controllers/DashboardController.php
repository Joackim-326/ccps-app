<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $scope = fn () => Challenge::query()
            ->when($user->isReceptionist(), fn ($q) => $q->where('created_by', $user->id))
            ->when($user->isOfficer(), fn ($q) => $q->where('assigned_to', $user->id));

        // Creators have their own queue on the dashboard; the system-wide totals live under Admin.
        if ($user->isCreator()) {
            $scope = fn () => Challenge::where('assigned_to', $user->id);
        }

        $stats = [
            'open' => $scope()->where('status', Challenge::IN_PROGRESS)->count(),
            'attended' => $scope()->where('status', Challenge::ATTENDED)->count(),
            'unread' => $user->unreadMessageCount(),
        ];

        if ($user->isCreator()) {
            $stats['users'] = User::count();
            $stats['all_open'] = Challenge::where('status', Challenge::IN_PROGRESS)->count();
            $stats['all_attended'] = Challenge::where('status', Challenge::ATTENDED)->count();
        }

        $recent = $scope()->with(['creator', 'assignee'])->latest()->limit(6)->get();

        return view('dashboard', compact('stats', 'recent'));
    }
}
