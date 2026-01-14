@extends('layouts.dashboard', [
    'title' => __('Assessments'),
    'subtitle' => __('View and review assessments assigned to you'),
])

@section('content')
    <div class="mb-6">
        <form method="GET" action="{{ route('assessor.assessments') }}" class="flex items-center gap-3">
            <select name="status" class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
                <option value="">{{ __('All statuses') }}</option>
                <option value="draft" @selected(request('status') === 'draft')>{{ __('Draft') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                <option value="closed" @selected(request('status') === 'closed')>{{ __('Closed') }}</option>
            </select>
            <select name="type" class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
                <option value="">{{ __('All types') }}</option>
                <option value="psychometric" @selected(request('type') === 'psychometric')>{{ __('Psychometric') }}</option>
                <option value="interview" @selected(request('type') === 'interview')>{{ __('Interview') }}</option>
                <option value="group_exercise" @selected(request('type') === 'group_exercise')>{{ __('Group exercise') }}</option>
                <option value="written_test" @selected(request('type') === 'written_test')>{{ __('Written test') }}</option>
                <option value="role_play" @selected(request('type') === 'role_play')>{{ __('Role play') }}</option>
                <option value="committee_interview" @selected(request('type') === 'committee_interview')>{{ __('Committee interview') }}</option>
                <option value="other" @selected(request('type') === 'other')>{{ __('Other') }}</option>
            </select>
            <button type="submit" class="rounded-lg bg-uae-gold-300/90 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-uae-gold-300">
                {{ __('Filter') }}
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
        <table class="min-w-full divide-y divide-white/10 text-sm">
            <thead class="bg-white/5 text-left text-xs uppercase tracking-wide text-slate-300">
                <tr>
                    <th class="px-5 py-3">{{ __('ID') }}</th>
                    <th class="px-5 py-3">{{ __('Title') }}</th>
                    <th class="px-5 py-3">{{ __('Type') }}</th>
                    <th class="px-5 py-3">{{ __('Status') }}</th>
                    <th class="px-5 py-3">{{ __('Start date') }}</th>
                    <th class="px-5 py-3">{{ __('End date') }}</th>
                    <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-slate-100">
                @forelse($assessments as $assessment)
                    <tr>
                        <td class="px-5 py-4 font-medium">#{{ $assessment->id }}</td>
                        <td class="px-5 py-4">{{ $assessment->translations->first()?->title ?? __('Untitled') }}</td>
                        <td class="px-5 py-4">
                            <span class="rounded-full bg-uae-gold-300/20 px-2.5 py-0.5 text-xs font-semibold text-uae-gold-200">
                                {{ __(ucfirst(str_replace('_', ' ', $assessment->type))) }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $assessment->status === 'active' ? 'bg-emerald-500/20 text-emerald-200' : ($assessment->status === 'closed' ? 'bg-rose-500/20 text-rose-200' : 'bg-slate-500/20 text-slate-200') }}">
                                {{ __(ucfirst($assessment->status)) }}
                            </span>
                        </td>
                        <td class="px-5 py-4">{{ $assessment->start_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-5 py-4">{{ $assessment->end_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('assessments.review', $assessment) }}" class="rounded-lg border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                                {{ __('Review') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-300">
                            {{ __('No assessments found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $assessments->links() }}
    </div>
@endsection


