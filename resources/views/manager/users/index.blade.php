@extends('layouts.dashboard', [
    'title' => __('Manage users'),
    'subtitle' => __('Update roles and profiles for your team'),
])

@section('content')
    <form method="GET" action="{{ route('manager.users.index') }}" class="mb-6 grid gap-4 rounded-xl border border-white/10 bg-white/5 p-4 md:grid-cols-4">
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="{{ __('Search by name, username, or email') }}"
            class="w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-100 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40"
        >
        <select name="role" class="w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-100">
            <option value="">{{ __('All roles') }}</option>
            @foreach($roles as $role)
                <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
            @endforeach
        </select>
        <select name="status" class="w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-100">
            <option value="">{{ __('All statuses') }}</option>
            <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
            <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option>
        </select>
        <button class="rounded-lg bg-uae-gold-300/90 px-4 py-2 text-sm font-semibold text-white hover:bg-uae-gold-300">
            {{ __('Filter') }}
        </button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
        <table class="min-w-full divide-y divide-white/10 text-sm">
            <thead class="bg-white/5 text-left text-xs uppercase tracking-wide text-slate-300">
                <tr>
                    <th class="px-5 py-3">{{ __('Username') }}</th>
                    <th class="px-5 py-3">{{ __('Full name') }}</th>
                    <th class="px-5 py-3">{{ __('Email') }}</th>
                    <th class="px-5 py-3">{{ __('Role') }}</th>
                    <th class="px-5 py-3">{{ __('Status') }}</th>
                    <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-slate-100">
                @forelse($users as $user)
                    <tr class="hover:bg-white/5">
                        <td class="px-5 py-4 font-medium">{{ $user->username }}</td>
                        <td class="px-5 py-4">{{ $user->full_name ?? '—' }}</td>
                        <td class="px-5 py-4">{{ $user->email }}</td>
                        <td class="px-5 py-4">{{ ucfirst($user->role) }}</td>
                        <td class="px-5 py-4">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $user->status === 'active' ? 'bg-emerald-500/20 text-emerald-200' : 'bg-rose-500/20 text-rose-200' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('manager.users.edit', $user) }}"
                                class="rounded-lg border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-200 hover:bg-white/10">
                                {{ __('Edit') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-300">
                            {{ __('No users found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
@endsection
