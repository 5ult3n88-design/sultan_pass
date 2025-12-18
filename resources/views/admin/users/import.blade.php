@extends('layouts.dashboard', [
    'title' => isset($isParticipant) && $isParticipant ? __('Import Participants from CSV') : __('Import Users from CSV'),
    'subtitle' => isset($isParticipant) && $isParticipant ? __('Upload a CSV file to create multiple participants at once') : __('Upload a CSV file to create multiple users at once'),
])

@section('content')
    <div class="mb-6 space-y-4">
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-5 py-4 text-sm text-amber-200">
            <p class="font-semibold mb-2">{{ __('CSV Format Requirements:') }}</p>
            <ul class="list-disc list-inside space-y-1 text-xs">
                <li>{{ __('First column: User ID (username) - required') }}</li>
                <li>{{ __('Second column: Full Name - optional') }}</li>
                <li>{{ __('Third column: Email - optional (defaults to username@example.com)') }}</li>
                <li>{{ __('Fourth column: Password - optional (will be auto-generated if not provided)') }}</li>
                <li>{{ __('First row should be headers (will be skipped)') }}</li>
            </ul>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 px-5 py-4 text-sm text-slate-200">
            <p class="font-semibold mb-2 text-slate-300">{{ __('Example CSV Format:') }}</p>
            <pre class="text-xs font-mono bg-slate-900/60 p-3 rounded overflow-x-auto">User ID,Name,Email,Password
user001,John Doe,john.doe@example.com,
user002,Jane Smith,,
user003,Bob Johnson,bob@example.com,MyPassword123</pre>
        </div>
    </div>

    <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
        <form method="POST" action="{{ route('admin.users.import-csv') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if(isset($isParticipant) && $isParticipant)
                <input type="hidden" name="role" value="participant">
            @endif

            <div>
                <label for="csv_file" class="mb-2 block text-sm font-semibold text-slate-200">
                    {{ __('CSV File') }}
                </label>
                <input type="file" name="csv_file" id="csv_file" accept=".csv,.txt" required
                    class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 file:mr-4 file:rounded-lg file:border-0 file:bg-uae-gold-300/80 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-uae-gold-300 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
                @error('csv_file')
                    <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                @if(!isset($isParticipant) || !$isParticipant)
                <div>
                    <label for="role" class="mb-2 block text-sm font-semibold text-slate-200">
                        {{ __('Default Role') }}
                    </label>
                    <select name="role" id="role" required
                        class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected(old('role', 'participant') === $role)>
                                {{ ucfirst($role) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <div>
                    <label for="default_status" class="mb-2 block text-sm font-semibold text-slate-200">
                        {{ __('Default Status') }}
                    </label>
                    <select name="default_status" id="default_status" required
                        class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
                        <option value="active" @selected(old('default_status', 'active') === 'active')>
                            {{ __('Active') }}
                        </option>
                        <option value="inactive" @selected(old('default_status') === 'inactive')>
                            {{ __('Inactive') }}
                        </option>
                    </select>
                    @error('default_status')
                        <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ isset($isParticipant) && $isParticipant ? route('admin.users.participants') : route('admin.users.index') }}" 
                    class="rounded-lg border border-white/10 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/10">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" 
                    class="rounded-lg bg-uae-gold-300/90 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-uae-gold-300">
                    {{ isset($isParticipant) && $isParticipant ? __('Import Participants') : __('Import Users') }}
                </button>
            </div>
        </form>
    </div>

    @if(session('csv_passwords_available'))
        <div class="mt-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4">
            <p class="text-sm font-semibold text-emerald-200 mb-3">
                {{ isset($isParticipant) && $isParticipant ? __('Participants imported successfully! Download the passwords file:') : __('Users imported successfully! Download the passwords file:') }}
            </p>
            <a href="{{ route('admin.users.imported-passwords') }}" 
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-500/80 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-500">
                <span>📥</span>
                {{ __('Download Passwords CSV') }}
            </a>
        </div>
    @endif
@endsection

