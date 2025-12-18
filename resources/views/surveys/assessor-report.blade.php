@extends('layouts.role', [
    'title' => __('Assessor report'),
    'subtitle' => __('Assessment :id review workspace', ['id' => $assessmentId]),
])

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1.5fr,1fr]">
        <section class="rounded-2xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-lg font-semibold text-white">{{ $overview['title'] }}</h2>
            <p class="mt-1 text-xs text-slate-400">{{ __('Status') }}: {{ $overview['status'] }}</p>
            <div class="mt-4 grid gap-4 text-sm text-slate-200 sm:grid-cols-3">
                <div class="rounded-xl border border-white/10 bg-slate-900/40 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('Submitted') }}</p>
                    <p class="mt-1 text-xl font-semibold">{{ $overview['submitted'] }} / {{ $overview['total'] }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-slate-900/40 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('Average score') }}</p>
                    <p class="mt-1 text-xl font-semibold">{{ $overview['avgScore'] }}%</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-slate-900/40 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('Remaining') }}</p>
                    <p class="mt-1 text-xl font-semibold">{{ $overview['total'] - $overview['submitted'] }}</p>
                </div>
            </div>

            <div class="mt-6">
                <h3 class="text-sm font-semibold text-white">{{ __('Participants') }}</h3>
                <div class="mt-4 space-y-3 text-sm text-slate-200">
                    @foreach($participants as $participant)
                        <div class="flex items-start justify-between rounded-xl border border-white/10 bg-slate-900/40 px-4 py-3">
                            <div>
                                <p class="font-semibold">{{ $participant['name'] }}</p>
                                <p class="mt-1 text-xs text-slate-400">
                                    @forelse($participant['strengths'] as $strength)
                                        <span class="mr-1 rounded-full bg-uae-gold-300/20 px-2 py-0.5 text-[0.65rem] text-uae-gold-200">{{ $strength }}</span>
                                    @empty
                                        {{ __('Awaiting evaluation') }}
                                    @endforelse
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('Status') }}</p>
                                <p class="mt-1 font-semibold capitalize">{{ str_replace('_', ' ', $participant['status']) }}</p>
                                <p class="mt-2 text-xs text-slate-400">
                                    {{ __('Score') }}: {{ $participant['score'] ?? __('Pending') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        <aside class="space-y-6">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h3 class="text-sm font-semibold text-white">{{ __('Quick actions') }}</h3>
                <div class="mt-4 flex flex-col gap-3 text-sm">
                    <button class="rounded-lg border border-white/10 px-4 py-2 text-slate-200 hover:bg-white/10">
                        {{ __('Export scoring sheet') }}
                    </button>
                    <button class="rounded-lg border border-white/10 px-4 py-2 text-slate-200 hover:bg-white/10">
                        {{ __('Send reminder to pending participants') }}
                    </button>
                    <button class="rounded-lg bg-emerald-500/80 px-4 py-2 font-semibold text-white hover:bg-emerald-500">
                        {{ __('Publish interim report') }}
                    </button>
                </div>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6 text-sm text-slate-200">
                <h3 class="text-sm font-semibold text-white">{{ __('Assessment notes') }}</h3>
                <textarea rows="6" class="mt-3 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40" placeholder="{{ __('Record calibration notes, anomalies, or adjustments here...') }}"></textarea>
                <button class="mt-3 rounded-lg bg-uae-gold-300/80 px-4 py-2 text-sm font-semibold text-white hover:bg-uae-gold-300">
                    {{ __('Save notes') }}
                </button>
            </div>
        </aside>
    </div>
@endsection

