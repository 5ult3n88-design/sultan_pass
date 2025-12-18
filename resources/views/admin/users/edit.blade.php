@extends('layouts.dashboard', [
    'title' => __('Edit user profile'),
    'subtitle' => __('Update role, credentials, and localization preferences'),
])

@section('content')
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid gap-8 lg:grid-cols-2">
        @csrf
        @method('PUT')
        <div class="space-y-6">
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-200">{{ __('Username') }}</label>
                <input name="username" value="{{ old('username', $user->username) }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-200">{{ __('Full name') }}</label>
                <input name="full_name" value="{{ old('full_name', $user->full_name) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-200">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-200">{{ __('Reset password (optional)') }}</label>
                <input type="text" name="password" value="" placeholder="{{ __('Enter new password or leave blank') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
            </div>
        </div>
        <div class="space-y-6">
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-200">{{ __('Role') }}</label>
                <select name="role" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
                    @foreach($roles as $role)
                        <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid gap-4 lg:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-200">{{ __('Rank') }}</label>
                    <input name="rank" value="{{ old('rank', $user->rank) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-200">{{ __('Department') }}</label>
                    <input name="department" value="{{ old('department', $user->department) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
                </div>
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-200">{{ __('Preferred language') }}</label>
                <select name="language_pref" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
                    <option value="">{{ __('Auto detect') }}</option>
                    @foreach($languages as $language)
                        <option value="{{ $language->id }}" @selected(old('language_pref', $user->language_pref) == $language->id)>{{ __($language->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-200">{{ __('Status') }}</label>
                <select name="status" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
                    <option value="active" @selected(old('status', $user->status) === 'active')>{{ __('Active') }}</option>
                    <option value="inactive" @selected(old('status', $user->status) === 'inactive')>{{ __('Inactive') }}</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-white/10 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">
                    {{ __('Back') }}
                </a>
                <button type="submit" class="rounded-lg bg-uae-gold-300/90 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-uae-gold-300">
                    {{ __('Save changes') }}
                </button>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-6" onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}');">
        @csrf
        @method('DELETE')
        <button class="rounded-lg border border-rose-500/40 bg-rose-500/20 px-4 py-2 text-sm font-semibold text-rose-100 transition hover:bg-rose-500/30">
            {{ __('Delete user') }}
        </button>
    </form>

    <div class="mt-10 grid gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('admin.users.send-reset', $user) }}" class="rounded-2xl border border-white/10 bg-white/5 p-6 text-sm text-slate-200">
            @csrf
            <h3 class="text-sm font-semibold text-white">{{ __('Send password reset email') }}</h3>
            <p class="mt-2 text-xs text-slate-400">{{ __('Trigger a secure reset link to the user\'s inbox.') }}</p>
            <button class="mt-4 rounded-lg border border-white/10 px-4 py-2 text-slate-200 hover:bg-white/10">
                {{ __('Send reset link') }}
            </button>
        </form>

        <form method="POST" action="{{ route('admin.users.generate-password', $user) }}" class="rounded-2xl border border-white/10 bg-white/5 p-6 text-sm text-slate-200">
            @csrf
            <h3 class="text-sm font-semibold text-white">{{ __('Generate temporary password') }}</h3>
            <p class="mt-2 text-xs text-slate-400">{{ __('Create a one-time password to share through secure channels.') }}</p>
            <button class="mt-4 rounded-lg bg-emerald-500/80 px-4 py-2 font-semibold text-white hover:bg-emerald-500">
                {{ __('Generate password') }}
            </button>
        </form>
    </div>
@endsection

