<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ManagerUserController extends Controller
{
    protected array $editableRoles = ['participant', 'assessor', 'manager'];

    public function index(Request $request): View
    {
        $users = User::query()
            ->whereIn('role', $this->editableRoles)
            ->when($request->get('role'), fn ($q, $role) => $q->where('role', $role))
            ->when($request->get('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->get('search'), function ($q, $term) {
                $term = trim($term);
                $q->where(function ($query) use ($term) {
                    $query->where('username', 'like', "%{$term}%")
                        ->orWhere('full_name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->orderBy('full_name')
            ->orderBy('username')
            ->paginate(15)
            ->withQueryString();

        return view('manager.users.index', [
            'users' => $users,
            'roles' => $this->editableRoles,
        ]);
    }

    public function edit(User $user): View
    {
        abort_unless(in_array($user->role, $this->editableRoles, true), 403);

        return view('manager.users.edit', [
            'user' => $user->load('language'),
            'roles' => $this->editableRoles,
            'languages' => Language::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, $this->editableRoles, true), 403);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100', 'unique:users,username,' . $user->id],
            'full_name' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:' . implode(',', $this->editableRoles)],
            'language_pref' => ['nullable', 'exists:languages,id'],
            'rank' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $payload = [
            'username' => $validated['username'],
            'full_name' => $validated['full_name'] ?? null,
            'email' => $validated['email'],
            'role' => $validated['role'],
            'language_pref' => $validated['language_pref'] ?? null,
            'rank' => $validated['rank'] ?? null,
            'department' => $validated['department'] ?? null,
            'status' => $validated['status'],
        ];

        $statusMessage = __('User updated successfully.');

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
            $statusMessage = __('User updated successfully. Password reset to: :password', ['password' => $validated['password']]);
        }

        $user->update($payload);

        return redirect()
            ->route('manager.users.edit', $user)
            ->with('status', $statusMessage);
    }
}
