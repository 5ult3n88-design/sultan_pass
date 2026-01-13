@extends('layouts.dashboard', [
    'title' => __('Create New Test'),
    'subtitle' => __('Choose the type of test you want to create'),
])

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-8 rounded-2xl border border-white/10 bg-white/5 p-8">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-white">{{ __('Select Test Type') }}</h2>
                <p class="mt-2 text-sm text-slate-400">
                    {{ __('Choose between a scored percentile test or a categorical assessment') }}
                </p>
                <div id="selected-type-banner" class="mt-3 text-sm font-semibold text-amber-300 hidden">
                    {{ __('Selected:') }} <span id="selected-type-label"></span>
                </div>
            </div>

            <form action="{{ route('tests.create-type') }}" method="POST">
                @csrf

                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Percentile Test Option -->
                    <label class="group relative cursor-pointer" data-test-card="percentile">
                        <input type="radio" name="test_type" value="percentile" class="peer sr-only" required />
                        <div
                            class="rounded-xl border-2 border-white/10 bg-white/5 p-6 transition-all duration-200 hover:border-white/20 peer-checked:border-amber-500 peer-checked:border-4 peer-checked:ring-4 peer-checked:ring-amber-400/40 peer-checked:bg-amber-500/10 peer-checked:shadow-lg peer-checked:shadow-amber-500/20">
                            <div class="mb-4 flex items-center justify-center">
                                <div
                                    class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-500/20 text-xl font-semibold text-amber-200">
                                    {{ __('PT') }}
                                </div>
                            </div>
                            <h3 class="mb-2 text-center text-lg font-semibold text-white">
                                {{ __('Percentile Test') }}
                            </h3>
                            <p class="text-center text-sm text-slate-300">
                                {{ __('Scored exams like IQ tests, x/100 assessments with pass/fail criteria') }}
                            </p>

                            <div class="mt-4 space-y-2 text-xs text-slate-400">
                                <div class="flex items-start gap-2">
                                    <span class="text-amber-400">•</span>
                                    <span>{{ __('Set total marks and passing criteria') }}</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-amber-400">•</span>
                                    <span>{{ __('Multiple choice with correct answers') }}</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-amber-400">•</span>
                                    <span>{{ __('Typed answers for manual grading') }}</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-amber-400">•</span>
                                    <span>{{ __('Auto-grading and percentage scores') }}</span>
                                </div>
                            </div>

                            <div class="absolute right-4 top-4 hidden peer-checked:block">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-500 text-white shadow-lg font-bold">
                                    {{ __('Selected') }}
                                </div>
                            </div>
                        </div>
                    </label>

                    <!-- Categorical Test Option -->
                    <label class="group relative cursor-pointer" data-test-card="categorical">
                        <input type="radio" name="test_type" value="categorical" class="peer sr-only" required />
                        <div
                            class="rounded-xl border-2 border-white/10 bg-white/5 p-6 transition-all duration-200 hover:border-white/20 peer-checked:border-emerald-500 peer-checked:border-4 peer-checked:ring-4 peer-checked:ring-emerald-400/40 peer-checked:bg-emerald-500/10 peer-checked:shadow-lg peer-checked:shadow-emerald-500/20">
                            <div class="mb-4 flex items-center justify-center">
                                <div
                                    class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/20 text-xl font-semibold text-emerald-200">
                                    {{ __('CT') }}
                                </div>
                            </div>
                            <h3 class="mb-2 text-center text-lg font-semibold text-white">
                                {{ __('Categorical Test') }}
                            </h3>
                            <p class="text-center text-sm text-slate-300">
                                {{ __('Personality tests, type assessments like 12 personalities, MBTI, etc.') }}
                            </p>

                            <div class="mt-4 space-y-2 text-xs text-slate-400">
                                <div class="flex items-start gap-2">
                                    <span class="text-emerald-400">•</span>
                                    <span>{{ __('Define custom categories/types') }}</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-emerald-400">•</span>
                                    <span>{{ __('Map answers to category buckets') }}</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-emerald-400">•</span>
                                    <span>{{ __('Manual category assignment for typed answers') }}</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-emerald-400">•</span>
                                    <span>{{ __('Results show dominant category') }}</span>
                                </div>
                            </div>

                            <div class="absolute right-4 top-4 hidden peer-checked:block">
                                <div
                                    class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500 text-white shadow-lg font-bold">
                                    {{ __('Selected') }}
                                </div>
                            </div>
                        </div>
                    </label>
                </div>

                @error('test_type')
                    <div class="mt-4 rounded-lg bg-rose-500/10 border border-rose-500/20 px-4 py-3 text-sm text-rose-200">
                        {{ $message }}
                    </div>
                @enderror

                <div class="mt-8 flex items-center justify-between gap-4">
                    <a href="{{ route('dashboard.' . auth()->user()->role) }}"
                        class="rounded-lg border border-white/10 px-6 py-3 text-sm font-semibold text-slate-300 transition hover:bg-white/5">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                        id="continue-button"
                        class="rounded-lg bg-amber-500 px-8 py-3 text-sm font-semibold text-white shadow transition hover:bg-amber-600 disabled:cursor-not-allowed disabled:opacity-50"
                        disabled>
                        {{ __('Continue') }} →
                    </button>
                </div>
            </form>
        </div>

        <!-- Help Section -->
        <div class="rounded-xl border border-white/5 bg-white/5 p-6">
            <h3 class="mb-3 font-semibold text-white">{{ __('Need Help Choosing?') }}</h3>
            <div class="space-y-3 text-sm text-slate-300">
                <div>
                    <strong class="text-amber-400">{{ __('Use Percentile') }}</strong>
                    {{ __('when you need to measure performance against a standard (e.g., IQ tests, knowledge exams, skill assessments with grades).') }}
                </div>
                <div>
                    <strong class="text-emerald-400">{{ __('Use Categorical') }}</strong>
                    {{ __('when you want to classify people into types or personalities (e.g., Myers-Briggs, Enneagram, learning styles, leadership types).') }}
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('[data-test-card]');
            const banner = document.getElementById('selected-type-banner');
            const label = document.getElementById('selected-type-label');
            const continueBtn = document.getElementById('continue-button');

            cards.forEach(card => {
                card.addEventListener('click', () => {
                    const value = card.getAttribute('data-test-card');
                    label.textContent = value === 'percentile' ? '{{ __('Percentile Test') }}' : '{{ __('Categorical Test') }}';
                    banner.classList.remove('hidden');
                    continueBtn.disabled = false;
                });
            });
        });
    </script>
@endsection
