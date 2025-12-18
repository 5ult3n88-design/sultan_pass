@extends('layouts.dashboard', [
    'title' => __('Administrator Dashboard'),
    'subtitle' => __('Full control of assessments, roles, and localization'),
])

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">{{ __('Assessments') }}</h2>
            <p class="text-xs text-slate-500">{{ __('Create and launch new tests for your organization.') }}</p>
        </div>
        <a href="{{ route('assessments.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-uae-gold-300/90 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-uae-gold-300">
            <span class="text-base">+</span>
            {{ __('Create assessment') }}
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-4">
        <x-dashboard.stat-card :value="$metrics['users']" label="{{ __('Total users') }}" icon="users" />
        <x-dashboard.stat-card :value="$metrics['assessments']" label="{{ __('Assessments') }}" icon="clipboard" />
        <x-dashboard.stat-card :value="$metrics['active_plans']" label="{{ __('Active development plans') }}" icon="sparkles" />
        <x-dashboard.stat-card :value="$metrics['notifications']" label="{{ __('Notifications sent') }}" icon="bell" />
    </div>

    <div class="mt-8 grid gap-6 md:grid-cols-3">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 shadow-lg">
            <p class="text-xs font-semibold uppercase tracking-wide text-silver-400">{{ __('Active participants') }}</p>
            <p class="mt-3 text-3xl font-semibold text-white">{{ number_format($dashboardStats['participants_active']) }}</p>
            <p class="mt-2 text-xs text-silver-400">{{ __('Users ready to take assessments') }}</p>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 shadow-lg">
            <p class="text-xs font-semibold uppercase tracking-wide text-silver-400">{{ __('Live assessments') }}</p>
            <p class="mt-3 text-3xl font-semibold text-white">{{ number_format($dashboardStats['live_assessments']) }}</p>
            <p class="mt-2 text-xs text-silver-400">{{ __('Currently active and ongoing tests') }}</p>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 shadow-lg">
            <p class="text-xs font-semibold uppercase tracking-wide text-silver-400">{{ __('Pending password resets') }}</p>
            <p class="mt-3 text-3xl font-semibold text-white">{{ number_format($dashboardStats['pending_resets']) }}</p>
            <p class="mt-2 text-xs text-silver-400">{{ __('Requests awaiting approval') }}</p>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-5 shadow-lg">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-silver-400">{{ __('Upcoming assessment') }}</p>
                <h3 class="mt-2 text-xl font-semibold text-white">
                    {{ optional($dashboardStats['next_assessment'])->type ?? __('No upcoming assessments scheduled.') }}
                </h3>
            </div>
            @if($dashboardStats['next_assessment'])
                <span class="rounded-full bg-uae-gold-300/20 px-3 py-1 text-sm font-semibold text-uae-gold-100">
                    {{ __(ucfirst($dashboardStats['next_assessment']->status ?? 'active')) }}
                </span>
            @endif
        </div>
        @if($dashboardStats['next_assessment'])
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-silver-400">{{ __('Starts') }}</dt>
                    <dd class="text-sm text-silver-100">
                        {{ optional($dashboardStats['next_assessment']->start_date)->translatedFormat('d M Y - h:i A') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-silver-400">{{ __('Type') }}</dt>
                    <dd class="text-sm text-silver-100">
                        {{ ucfirst($dashboardStats['next_assessment']->type ?? __('Assessment')) }}
                    </dd>
                </div>
            </dl>
        @endif
    </div>

    @php
        $statusLabels = [
            'draft' => __('Draft'),
            'active' => __('Active'),
            'published' => __('Published'),
            'archived' => __('Archived'),
            'completed' => __('Completed'),
        ];
    @endphp

    <div class="mt-10 grid gap-6 xl:grid-cols-3">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">{{ __('Assessments by status') }}</h2>
                <span class="text-xs text-silver-400">{{ __('Total') }}: {{ $assessmentStatusStats->sum('total') }}</span>
            </div>
            <div class="mt-6 space-y-4">
                @forelse($assessmentStatusStats as $bucket)
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold text-silver-300">
                            <span>{{ $statusLabels[$bucket['status']] ?? ucfirst($bucket['status']) }}</span>
                            <span>{{ $bucket['total'] }}</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-iron-800">
                            <div class="h-full rounded-full bg-uae-gold-300" style="width: {{ $bucket['percentage'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-silver-400">{{ __('No assessment data available.') }}</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">{{ __('Daily completions (last :days days)', ['days' => $dailyCompletionSeries->count()]) }}</h2>
                <span class="text-xs text-silver-400">{{ __('Completions') }}</span>
            </div>
            @if($dailyCompletionSeries->isEmpty())
                <p class="mt-6 text-sm text-silver-400">{{ __('No completions recorded yet.') }}</p>
            @else
                <div class="mt-8 flex h-48 items-end gap-3">
                    @foreach($dailyCompletionSeries as $entry)
                        <div class="flex-1 text-center">
                            <div class="mx-auto flex h-32 w-8 items-end rounded-full bg-iron-800/80">
                                <div class="w-full rounded-full bg-uae-gold-300/70" style="height: calc(6px + {{ $entry['percentage'] }}%)"></div>
                            </div>
                            <p class="mt-2 text-sm font-semibold text-white">{{ $entry['total'] }}</p>
                            <p class="text-[0.65rem] uppercase tracking-wide text-silver-400">{{ $entry['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">{{ __('Top performers this week') }}</h2>
                <span class="text-xs text-silver-400">{{ __('Score') }}</span>
            </div>
            <div class="mt-6 space-y-4">
                @forelse($topPerformers as $performer)
                    <div class="flex items-center justify-between rounded-xl border border-white/5 bg-slate-900/40 px-4 py-3">
                        <div>
                            <p class="font-semibold text-white">{{ $performer->full_name ?: $performer->username }}</p>
                            <p class="text-xs text-silver-400">{{ $performer->assessment_type ?? __('Assessment') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold text-uae-gold-200">{{ number_format($performer->score, 1) }}</p>
                            <p class="text-xs text-silver-400">{{ optional($performer->updated_at)->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-silver-400">{{ __('No performance data yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-10 grid gap-8 lg:grid-cols-2">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-white">{{ __('User roles distribution') }}</h2>
            </div>
            <div class="h-64">
                <canvas id="userRolesChart" data-chart-data="{{ json_encode($userRolesChart) }}"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-white">{{ __('Assessments by status') }}</h2>
            </div>
            <div class="h-64">
                <canvas id="assessmentsBarChart" data-chart-data="{{ json_encode($assessmentsBarChart) }}"></canvas>
            </div>
        </div>
    </div>

    <div class="mt-10 grid gap-8 lg:grid-cols-2">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">{{ __('Latest users') }}</h2>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-uae-gold-300 hover:text-uae-gold-100">{{ __('View all') }}</a>
            </div>
            <ul class="mt-6 space-y-4 text-sm text-slate-200">
                @forelse($recentUsers as $user)
                    <li class="flex items-center justify-between rounded-xl border border-white/5 bg-slate-900/40 px-4 py-3">
                        <div>
                            <p class="font-semibold">{{ $user->username }}</p>
                            <p class="text-xs text-slate-400">{{ $user->email }}</p>
                        </div>
                        <div class="text-right">
                            <span class="rounded-full bg-uae-gold-300/20 px-2.5 py-0.5 text-xs font-semibold text-uae-gold-200">
                                {{ ucfirst($user->role) }}
                            </span>
                            <p class="mt-1 text-xs text-slate-400">{{ $user->created_at->diffForHumans() }}</p>
                        </div>
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-white/10 px-4 py-6 text-center text-slate-400">
                        {{ __('No user activity yet.') }}
                    </li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">{{ __('Language distribution') }}</h2>
                <a href="{{ route('admin.languages.index') }}" class="text-sm text-uae-gold-300 hover:text-uae-gold-100">{{ __('Manage languages') }}</a>
            </div>
            <ul class="mt-6 space-y-3 text-sm text-slate-200">
                @foreach($languages as $language)
                    <li class="flex items-center justify-between rounded-xl border border-white/5 bg-slate-900/40 px-4 py-3">
                        <div>
                            <p class="font-semibold">{{ $language->name }}</p>
                            <p class="text-xs uppercase tracking-wide text-slate-400">{{ $language->code }}</p>
                        </div>
                        <span class="rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-xs font-semibold text-emerald-200">
                            {{ trans_choice('{0} no users|{1} :count user|[2,*] :count users', $language->users_count, ['count' => $language->users_count]) }}
                        </span>
                    </li>
                @endforeach
                @if($languages->isEmpty())
                    <li class="rounded-xl border border-dashed border-white/10 px-4 py-6 text-center text-slate-400">
                        {{ __('Add languages to enable multilingual content.') }}
                    </li>
                @endif
            </ul>
        </div>
    </div>
@endsection

