<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Language;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    protected array $availableRoles = ['admin', 'manager', 'assessor', 'participant'];

    public function index(Request $request): View
    {
        $this->ensureAdmin();

        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;

        $users = User::query()
            ->with('language')
            ->where('role', '!=', 'participant')
            ->when($request->get('role'), fn ($query, $role) => $query->where('role', $role))
            ->when($request->get('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->get('department'), fn ($query, $dept) => $query->where('department', $dept))
            ->when($request->get('rank'), fn ($query, $rank) => $query->where('rank', $rank))
            ->when($request->get('date_from'), fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($request->get('date_to'), fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->orderBy('role')
            ->orderBy('username')
            ->paginate($perPage)
            ->withQueryString();

        // Get unique departments and ranks for filter dropdowns
        $departments = User::where('role', '!=', 'participant')
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->sort()
            ->values();

        $ranks = User::where('role', '!=', 'participant')
            ->whereNotNull('rank')
            ->distinct()
            ->pluck('rank')
            ->sort()
            ->values();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => array_filter($this->availableRoles, fn($role) => $role !== 'participant'),
            'departments' => $departments,
            'ranks' => $ranks,
        ]);
    }

    public function participants(Request $request): View
    {
        $this->ensureAdmin();

        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? (int) $perPage : 10;

        $participants = User::query()
            ->with('language')
            ->where('role', 'participant')
            ->when($request->get('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->get('department'), fn ($query, $dept) => $query->where('department', $dept))
            ->when($request->get('rank'), fn ($query, $rank) => $query->where('rank', $rank))
            ->when($request->get('date_from'), fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($request->get('date_to'), fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when($request->get('assessment_type'), function ($query, $type) {
                $query->whereIn('id', function ($subQuery) use ($type) {
                    $subQuery->select('participant_id')
                        ->from('assessment_participants')
                        ->join('assessments', 'assessment_participants.assessment_id', '=', 'assessments.id')
                        ->where('assessments.type', $type);
                });
            })
            ->orderBy('full_name')
            ->orderBy('username')
            ->paginate($perPage)
            ->withQueryString();

        // Get unique departments and ranks for filter dropdowns
        $departments = User::where('role', 'participant')
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->sort()
            ->values();

        $ranks = User::where('role', 'participant')
            ->whereNotNull('rank')
            ->distinct()
            ->pluck('rank')
            ->sort()
            ->values();

        // Get assessment types for filter
        $assessmentTypes = Assessment::distinct()
            ->whereNotNull('type')
            ->pluck('type')
            ->sort()
            ->values();

        return view('admin.users.participants', [
            'participants' => $participants,
            'departments' => $departments,
            'ranks' => $ranks,
            'assessmentTypes' => $assessmentTypes,
        ]);
    }

    public function showImportForm(Request $request): View
    {
        $this->ensureAdmin();

        $isParticipant = $request->get('type') === 'participant' || $request->routeIs('admin.users.import-participant');
        
        return view('admin.users.import', [
            'roles' => $isParticipant ? ['participant'] : array_filter($this->availableRoles, fn($role) => $role !== 'participant'),
            'isParticipant' => $isParticipant,
        ]);
    }

    public function create(Request $request): View
    {
        $this->ensureAdmin();

        // If coming from participants section, only allow creating participants
        $isParticipant = $request->get('type') === 'participant' || $request->routeIs('admin.users.create-participant');
        
        $roles = $isParticipant 
            ? ['participant'] 
            : array_filter($this->availableRoles, fn($role) => $role !== 'participant');

        return view('admin.users.create', [
            'roles' => $roles,
            'languages' => Language::orderBy('name')->get(),
            'isParticipant' => $isParticipant,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'full_name' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'role' => ['required', 'in:' . implode(',', $this->availableRoles)],
            'language_pref' => ['nullable', 'exists:languages,id'],
            'rank' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $password = $validated['password'] ?? Str::random(16);

        $user = User::create([
            'username' => $validated['username'],
            'full_name' => $validated['full_name'] ?? null,
            'email' => $validated['email'],
            'role' => $validated['role'],
            'language_pref' => $validated['language_pref'] ?? null,
            'rank' => $validated['rank'] ?? null,
            'department' => $validated['department'] ?? null,
            'status' => $validated['status'],
            'password' => Hash::make($password),
        ]);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', __('User created successfully. Temporary password: :password', ['password' => $password]));
    }

    public function show(User $user): RedirectResponse
    {
        return redirect()->route('admin.users.edit', $user);
    }

    public function edit(User $user): View
    {
        $this->ensureAdmin();

        return view('admin.users.edit', [
            'user' => $user->load('language'),
            'roles' => $this->availableRoles,
            'languages' => Language::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100', 'unique:users,username,' . $user->id],
            'full_name' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:' . implode(',', $this->availableRoles)],
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
            ->route('admin.users.edit', $user)
            ->with('status', $statusMessage);
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureAdmin();

        if (auth()->id() === $user->id) {
            return back()->withErrors(['user' => __('You cannot delete your own account.')]);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('User deleted.'));
    }

    public function sendResetLink(User $user): RedirectResponse
    {
        $this->ensureAdmin();

        $status = Password::sendResetLink(['email' => $user->email]);

        return back()->with('status', __($status));
    }

    public function regeneratePassword(User $user): RedirectResponse
    {
        $this->ensureAdmin();

        $temporaryPassword = Str::random(12);

        $user->update([
            'password' => Hash::make($temporaryPassword),
        ]);

        return back()->with('status', __('Temporary password generated: :password', ['password' => $temporaryPassword]));
    }

    public function importCsv(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'role' => ['required', 'in:' . implode(',', $this->availableRoles)],
            'default_status' => ['required', 'in:active,inactive'],
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Skip header row
        $header = fgetcsv($handle);
        
        $imported = 0;
        $errors = [];
        $passwords = [];

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 2) {
                    continue;
                }

                $userId = trim($row[0] ?? '');
                $name = trim($row[1] ?? '');
                $email = trim($row[2] ?? $userId . '@example.com');
                $password = trim($row[3] ?? Str::random(12));

                if (empty($userId)) {
                    $errors[] = __('Row skipped: Missing user ID');
                    continue;
                }

                // Check if username already exists
                if (User::where('username', $userId)->exists()) {
                    $errors[] = __('User :id already exists', ['id' => $userId]);
                    continue;
                }

                // Check if email already exists
                if (User::where('email', $email)->exists()) {
                    $errors[] = __('Email :email already exists', ['email' => $email]);
                    continue;
                }

                $user = User::create([
                    'username' => $userId,
                    'full_name' => $name ?: null,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => $request->get('role'),
                    'status' => $request->get('default_status'),
                ]);

                $imported++;
                $passwords[] = [
                    'user_id' => $userId,
                    'name' => $name,
                    'password' => $password,
                ];
            }

            DB::commit();
            fclose($handle);

            // Store passwords in session for download
            session(['csv_import_passwords' => $passwords]);

            $message = __('Successfully imported :count users.', ['count' => $imported]);
            if (!empty($errors)) {
                $message .= ' ' . __('Errors: :errors', ['errors' => implode(', ', array_slice($errors, 0, 5))]);
            }

            $redirectRoute = $request->get('role') === 'participant' 
                ? route('admin.users.participants')
                : route('admin.users.index');

            return redirect()
                ->to($redirectRoute)
                ->with('status', $message)
                ->with('csv_passwords_available', true);
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            
            return back()
                ->withErrors(['csv_file' => __('Import failed: :message', ['message' => $e->getMessage()])])
                ->withInput();
        }
    }

    public function exportCsv(Request $request): Response
    {
        $this->ensureAdmin();

        $role = $request->get('role');
        $includePasswords = $request->get('include_passwords', false);

        $users = User::query()
            ->when($role, fn ($query) => $query->where('role', $role))
            ->orderBy('id')
            ->get();

        $filename = 'users_export_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($users, $includePasswords) {
            $file = fopen('php://output', 'w');
            
            // BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            $headers = ['ID', 'User ID', 'Name', 'Email', 'Role', 'Status', 'Department', 'Rank'];
            if ($includePasswords) {
                $headers[] = 'Password';
            }
            fputcsv($file, $headers);

            // Data rows
            foreach ($users as $user) {
                $row = [
                    $user->id,
                    $user->username,
                    $user->full_name ?? '',
                    $user->email,
                    $user->role,
                    $user->status,
                    $user->department ?? '',
                    $user->rank ?? '',
                ];
                
                if ($includePasswords) {
                    // Generate a temporary password for export (users will need to reset)
                    $row[] = '***RESET_REQUIRED***';
                }
                
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadImportedPasswords(): Response
    {
        $this->ensureAdmin();

        $passwords = session('csv_import_passwords', []);

        if (empty($passwords)) {
            abort(404, __('No passwords available for download.'));
        }

        $filename = 'imported_users_passwords_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($passwords) {
            $file = fopen('php://output', 'w');
            
            // BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, ['User ID', 'Name', 'Password']);

            // Data rows
            foreach ($passwords as $item) {
                fputcsv($file, [
                    $item['user_id'],
                    $item['name'],
                    $item['password'],
                ]);
            }

            fclose($file);
        };

        // Clear session after download
        session()->forget('csv_import_passwords');

        return response()->stream($callback, 200, $headers);
    }

    protected function ensureAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
    }
}
