<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->query('role')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $t = '%'.$request->query('q').'%';
                $q->where(fn ($w) => $w->where('first_name', 'like', $t)->orWhere('middle_name', 'like', $t)->orWhere('email', 'like', $t)->orWhere('contact', 'like', $t));
            })
            ->orderBy('first_name')->paginate(15)->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'contact' => ['required', 'string', 'max:30'],
            'alt_contact' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(User::ROLES)],
            'password' => ['nullable', Password::min(8)],
        ]);

        if ($user->isCreator() && $data['role'] !== User::CREATOR && User::where('role', User::CREATOR)->count() <= 1) {
            return back()->withErrors(['role' => 'The system must keep at least one creator.'], 'user'.$user->id);
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $oldRole = $user->role;
        $user->update($data);
        ActivityLog::record('user_updated', "Updated {$user->email}".($oldRole !== $user->role ? " (role {$oldRole} → {$user->role})" : ''));

        return back()->with('status', "{$user->name} updated.");
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        if ($user->isCreator() && User::where('role', User::CREATOR)->count() <= 1) {
            return back()->with('error', 'The system must keep at least one creator.');
        }

        $user->delete();
        ActivityLog::record('user_deleted', "Deleted user {$user->email}");
        return back()->with('status', 'User deleted.');
    }
}
