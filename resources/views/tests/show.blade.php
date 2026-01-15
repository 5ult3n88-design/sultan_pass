@extends('layouts.dashboard', [
    'title' => __('Test Details'),
    'subtitle' => __('View test information and manage submissions'),
])

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $test->title }}</h1>
            <p class="mt-1 text-sm text-slate-400">{{ ucfirst($test->test_type) }} {{ __('Test') }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('tests.grade', $test) }}"
                class="rounded-lg bg-emerald-500/80 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                {{ __('View Submissions') }}
            </a>
            <a href="{{ route('tests.edit', $test) }}"
                class="rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-2 text-sm font-semibold text-amber-300 hover:bg-amber-500/20">
                {{ __('Edit Test') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-lg border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Test Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">{{ __('Test Information') }}</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-400">{{ __('Status') }}</span>
                        <span class="rounded-full px-2 py-1 text-xs font-semibold
                            @if($test->status === 'published') bg-emerald-500/20 text-emerald-300
                            @elseif($test->status === 'draft') bg-slate-500/20 text-slate-300
                            @else bg-rose-500/20 text-rose-300 @endif">
                            {{ ucfirst($test->status) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">{{ __('Type') }}</span>
                        <span class="text-white">{{ ucfirst($test->test_type) }}</span>
                    </div>
                    @if($test->isPercentile())
                        <div class="flex justify-between">
                            <span class="text-slate-400">{{ __('Total Marks') }}</span>
                            <span class="text-white">{{ $test->total_marks }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">{{ __('Passing Marks') }}</span>
                            <span class="text-white">{{ $test->passing_marks }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-slate-400">{{ __('Duration') }}</span>
                        <span class="text-white">{{ $test->duration_minutes ?? __('Flexible') }} {{ __('mins') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">{{ __('Questions') }}</span>
                        <span class="text-white">{{ $test->questions->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">{{ __('Created By') }}</span>
                        <span class="text-white">{{ $test->creator->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">{{ __('Created At') }}</span>
                        <span class="text-white">{{ $test->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                </div>

                @if($test->description)
                    <div class="mt-4 border-t border-white/10 pt-4">
                        <h3 class="font-semibold text-white">{{ __('Description') }}</h3>
                        <p class="mt-2 text-sm text-slate-300">{{ $test->description }}</p>
                    </div>
                @endif
            </div>

            @if($test->isCategorical() && $test->categories->count())
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="text-lg font-semibold text-white">{{ __('Categories') }}</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($test->categories as $category)
                            <span class="rounded-full px-3 py-1.5 text-sm font-semibold text-slate-900"
                                style="background-color: {{ $category->color ?? '#e5b453' }}">
                                {{ $category->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">{{ __('Questions') }}</h2>
                <div class="mt-4 space-y-4">
                    @foreach($test->questions as $index => $question)
                        <div class="rounded-lg border border-white/5 bg-slate-900/40 p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-xs text-amber-400">{{ __('Question') }} {{ $index + 1 }}</p>
                                    <p class="mt-1 font-semibold text-white">{{ $question->question_text }}</p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ ucfirst(str_replace('_', ' ', $question->question_type)) }}
                                        @if($test->isPercentile())
                                            • {{ __('Marks') }}: {{ $question->marks }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            @if($question->isMultipleChoice())
                                <div class="mt-3 space-y-2 text-sm">
                                    @foreach($question->answerChoices as $choice)
                                        <div class="flex items-center gap-2 rounded border border-white/5 bg-black/20 px-3 py-2">
                                            @if($test->isPercentile() && $choice->is_correct)
                                                <span class="text-emerald-400">✓</span>
                                            @endif
                                            <span class="text-slate-200">{{ $choice->choice_text }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Stats Sidebar -->
        <div class="space-y-6">
            @if(auth()->user()->hasRoleOrAbove('assessor'))
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="text-lg font-semibold text-white">{{ __('Assign Participants') }}</h2>
                    <p class="mt-1 text-xs text-slate-400">{{ __('Select participants who should take this test') }}</p>
                    <form action="{{ route('tests.assign', $test) }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <input type="text" id="participants-search"
                            placeholder="{{ __('Search participants...') }}"
                            class="w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/40">
                        <select name="participant_ids[]" multiple size="8"
                            class="w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/40">
                            @foreach($participants as $participant)
                                <option value="{{ $participant->id }}">
                                    {{ $participant->full_name ?? $participant->username }} @if($participant->department) ({{ $participant->department }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="w-full rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">
                            {{ __('Assign Selected') }}
                        </button>
                    </form>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="text-lg font-semibold text-white">{{ __('Assigned Participants') }}</h2>
                    @if($assignedParticipants->isEmpty())
                        <p class="mt-3 text-sm text-slate-400">{{ __('No participants assigned yet.') }}</p>
                    @else
                        <div class="mt-4 space-y-2 text-sm">
                            @foreach($assignedParticipants as $assignment)
                                <div class="flex items-center justify-between rounded-lg border border-white/10 bg-slate-900/40 px-3 py-2">
                                    <div>
                                        <div class="text-slate-100">
                                            {{ $assignment->participant->full_name ?? $assignment->participant->username }}
                                        </div>
                                        <div class="text-xs text-slate-400">{{ $assignment->participant->email }}</div>
                                    </div>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                                        @if($assignment->status === 'assigned') bg-slate-500/20 text-slate-200
                                        @elseif($assignment->status === 'in_progress') bg-amber-500/20 text-amber-300
                                        @elseif($assignment->status === 'submitted') bg-emerald-500/20 text-emerald-300
                                        @else bg-blue-500/20 text-blue-300 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $assignment->status)) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">{{ __('Statistics') }}</h2>
                <div class="mt-4 space-y-4">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-emerald-400">{{ $test->assignments->count() }}</p>
                        <p class="text-xs text-slate-400">{{ __('Total Submissions') }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-lg bg-slate-900/40 p-3 text-center">
                            <p class="text-xl font-bold text-amber-400">
                                {{ $test->assignments->where('status', 'in_progress')->count() }}
                            </p>
                            <p class="text-xs text-slate-400">{{ __('In Progress') }}</p>
                        </div>
                        <div class="rounded-lg bg-slate-900/40 p-3 text-center">
                            <p class="text-xl font-bold text-emerald-400">
                                {{ $test->assignments->where('status', 'submitted')->count() }}
                            </p>
                            <p class="text-xs text-slate-400">{{ __('Completed') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">{{ __('Actions') }}</h2>
                <div class="mt-4 space-y-2">
                    @if($test->status === 'draft')
                        <form action="{{ route('tests.update', $test) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="published">
                            <button type="submit"
                                class="w-full rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">
                                {{ __('Publish Test') }}
                            </button>
                        </form>
                    @elseif($test->status === 'published')
                        <form action="{{ route('tests.update', $test) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="archived">
                            <button type="submit"
                                class="w-full rounded-lg bg-slate-500 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-600">
                                {{ __('Archive Test') }}
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('tests.destroy', $test) }}" method="POST"
                        onsubmit="return confirm('{{ __('Are you sure you want to delete this test?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full rounded-lg border border-rose-500/30 bg-rose-500/10 px-4 py-2 text-sm font-semibold text-rose-300 hover:bg-rose-500/20">
                            {{ __('Delete Test') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function attachParticipantsSearch() {
            const input = document.getElementById('participants-search');
            const select = document.querySelector('select[name="participant_ids[]"]');
            if (!input || !select) return;

            input.addEventListener('input', () => {
                const term = input.value.toLowerCase();
                Array.from(select.options).forEach((opt) => {
                    const label = (opt.textContent || '').toLowerCase();
                    opt.hidden = term && !label.includes(term);
                });
            });
        })();
    </script>
@endsection
