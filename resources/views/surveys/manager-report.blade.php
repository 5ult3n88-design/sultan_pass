@extends('layouts.role', [
    'title' => __('Manager insights'),
    'subtitle' => __('Assessment :id performance overview', ['id' => $assessmentId]),
])

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1.6fr,1fr]">
        <section class="rounded-2xl border border-white/10 bg-white/5 p-6">
            <div class="flex flex-col gap-6">
                <header>
                    <h2 class="text-lg font-semibold text-white">{{ $overview['title'] }}</h2>
                    <p class="mt-1 text-xs text-slate-400">{{ __('Status') }}: {{ $overview['status'] }}</p>
                    <div class="mt-4 flex flex-wrap gap-4 text-sm text-slate-200">
                        <div class="rounded-xl border border-white/10 bg-slate-900/40 px-4 py-3">
                            <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('Completion') }}</p>
                            <p class="mt-1 text-xl font-semibold">{{ $overview['submitted'] }} / {{ $overview['total'] }}</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-slate-900/40 px-4 py-3">
                            <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('Average score') }}</p>
                            <p class="mt-1 text-xl font-semibold">{{ $overview['avgScore'] }}%</p>
                        </div>
                    </div>
                </header>

                <div>
                    <h3 class="text-sm font-semibold text-white">{{ __('Competency trends') }}</h3>
                    <div class="mt-4 space-y-3 text-sm text-slate-200">
                        @foreach($competencyBreakdown as $item)
                            <div>
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold">{{ $item['name'] }}</p>
                                    <span>{{ $item['score'] }}%</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-slate-800">
                                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-uae-gold-300" style="width: {{ $item['score'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-white">{{ __('Top performers') }}</h3>
                    <div class="mt-4 space-y-3 text-sm text-slate-200">
                        @foreach($participants->where('score')->sortByDesc('score')->take(3) as $participant)
                            <div class="flex items-center justify-between rounded-xl border border-white/10 bg-slate-900/40 px-4 py-3">
                                <p class="font-semibold">{{ $participant['name'] }}</p>
                                <span class="rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-xs font-semibold text-emerald-200">
                                    {{ $participant['score'] }}%
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <aside class="rounded-2xl border border-white/10 bg-white/5 p-6">
            <h3 class="text-sm font-semibold text-white">{{ __('Participant snapshot') }}</h3>
            <ul class="mt-4 space-y-3 text-sm text-slate-200">
                @foreach($participants as $participant)
                    <li class="rounded-xl border border-white/10 bg-slate-900/40 px-4 py-3">
                        <p class="font-semibold">{{ $participant['name'] }}</p>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ __('Status') }}: {{ ucfirst(str_replace('_', ' ', $participant['status'])) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ __('Score') }}: {{ $participant['score'] ?? __('Pending') }}
                        </p>
                    </li>
                @endforeach
            </ul>
            <div class="mt-6 flex flex-col gap-3 text-sm">
                <button class="rounded-lg border border-white/10 px-4 py-2 text-slate-200 hover:bg-white/10">
                    {{ __('Download manager summary') }}
                </button>
                <button class="rounded-lg bg-uae-gold-300/80 px-4 py-2 font-semibold text-white hover:bg-uae-gold-300">
                    {{ __('Share insights with leadership') }}
                </button>
            </div>
        </aside>
    </div>
@endsection

