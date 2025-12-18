@extends('layouts.dashboard', [
    'title' => __('Participants'),
    'subtitle' => __('View participants (read-only)'),
])

@section('content')
    <div class="mb-6">
        <form method="GET" action="{{ route('manager.participants') }}" class="flex items-center gap-3">
            <select name="status" class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
                <option value="">{{ __('All statuses') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option>
            </select>
            <select name="department" class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
                <option value="">{{ __('All departments') }}</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" @selected(request('department') === $dept)>{{ $dept }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg bg-uae-gold-300/90 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-uae-gold-300">
                {{ __('Filter') }}
            </button>
        </form>
    </div>

    <div class="mb-4 rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
        {{ __('You have read-only access to participant information. Contact an administrator to make changes.') }}
    </div>

    <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
        <table class="min-w-full divide-y divide-white/10 text-sm">
            <thead class="bg-white/5 text-left text-xs uppercase tracking-wide text-slate-300">
                <tr>
                    <th class="px-5 py-3">{{ __('Username') }}</th>
                    <th class="px-5 py-3">{{ __('Full name') }}</th>
                    <th class="px-5 py-3">{{ __('Email') }}</th>
                    <th class="px-5 py-3">{{ __('Department') }}</th>
                    <th class="px-5 py-3">{{ __('Rank') }}</th>
                    <th class="px-5 py-3">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-slate-100">
                @forelse($participants as $participant)
                    <tr>
                        <td class="px-5 py-4 font-medium">{{ $participant->username }}</td>
                        <td class="px-5 py-4">{{ $participant->full_name ?? '—' }}</td>
                        <td class="px-5 py-4">{{ $participant->email }}</td>
                        <td class="px-5 py-4">{{ $participant->department ?? '—' }}</td>
                        <td class="px-5 py-4">{{ $participant->rank ?? '—' }}</td>
                        <td class="px-5 py-4">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $participant->status === 'active' ? 'bg-emerald-500/20 text-emerald-200' : 'bg-rose-500/20 text-rose-200' }}">
                                {{ ucfirst($participant->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-300">
                            {{ __('No participants found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $participants->links() }}
    </div>
@endsection




