@extends($layout, [
    'title' => __('Create assessment'),
    'subtitle' => __('Define a new test for your participants'),
])

@section('content')
    <form action="{{ route('assessments.store') }}" method="POST" enctype="multipart/form-data" class="mx-auto max-w-6xl space-y-6" id="assessment-form">
        @csrf
        
        {{-- Basic Information Section --}}
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg shadow-black/10">
            <h2 class="text-lg font-semibold text-white">{{ __('Assessment details') }}</h2>
            <p class="mt-1 text-xs text-slate-400">{{ __('Provide the core information about this assessment.') }}</p>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div>
                    <label for="type" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">
                        {{ __('Assessment type') }}
                    </label>
                    <select id="type" name="type" class="mt-2 w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
                        <option value="">{{ __('Select type') }}</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" @selected(old('type') === $type)>{{ __(ucfirst(str_replace('_', ' ', $type))) }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">
                        {{ __('Status') }}
                    </label>
                    <select id="status" name="status" class="mt-2 w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(old('status', 'draft') === $status)>{{ __(ucfirst($status)) }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div>
                    <label for="start_date" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">
                        {{ __('Start date') }}
                    </label>
                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" class="mt-2 w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
                    @error('start_date')
                        <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">
                        {{ __('End date') }}
                    </label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" class="mt-2 w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40">
                    @error('end_date')
                        <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Scoring Mode Section --}}
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg shadow-black/10">
            <h2 class="text-lg font-semibold text-white">{{ __('Scoring mode') }}</h2>
            <p class="mt-1 text-xs text-slate-400">{{ __('Choose how this assessment will be scored.') }}</p>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <label class="relative cursor-pointer">
                    <input type="radio" name="scoring_mode" value="categorical" class="peer sr-only" @checked(old('scoring_mode', 'percentile') === 'categorical')>
                    <div class="rounded-xl border-2 border-white/10 bg-slate-900/50 p-4 transition peer-checked:border-uae-gold-300 peer-checked:bg-uae-gold-300/10">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-500/20">
                                <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-white">{{ __('Categorical') }}</p>
                                <p class="text-xs text-slate-400">{{ __('Personality test style with multiple categories') }}</p>
                            </div>
                        </div>
                    </div>
                </label>

                <label class="relative cursor-pointer">
                    <input type="radio" name="scoring_mode" value="percentile" class="peer sr-only" @checked(old('scoring_mode', 'percentile') === 'percentile')>
                    <div class="rounded-xl border-2 border-white/10 bg-slate-900/50 p-4 transition peer-checked:border-uae-gold-300 peer-checked:bg-uae-gold-300/10">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/20">
                                <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-white">{{ __('Percentile') }}</p>
                                <p class="text-xs text-slate-400">{{ __('Traditional scoring with maximum points') }}</p>
                            </div>
                        </div>
                    </div>
                </label>
            </div>
            @error('scoring_mode')
                <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
            @enderror
        </div>

        {{-- Categories Section (Categorical Mode Only) --}}
        <div id="categories-section" class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg shadow-black/10" style="display: none;">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">{{ __('Categories') }}</h2>
                    <p class="mt-1 text-xs text-slate-400">{{ __('Define the categories for this categorical assessment.') }}</p>
                </div>
                <button type="button" id="add-category-btn" class="rounded-lg bg-purple-500/20 px-4 py-2 text-sm font-semibold text-purple-300 transition hover:bg-purple-500/30">
                    + {{ __('Add category') }}
                </button>
            </div>

            <div id="categories-container" class="mt-6 space-y-4">
                {{-- Categories will be added here dynamically --}}
            </div>
            @error('categories')
                <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
            @enderror
        </div>

        {{-- Localized Content Section --}}
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg shadow-black/10">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">{{ __('Localized content') }}</h2>
                    <p class="mt-1 text-xs text-slate-400">{{ __('Provide titles and descriptions for the languages you support.') }}</p>
                </div>
                <span class="rounded-full bg-uae-gold-300/20 px-3 py-1 text-xs font-semibold text-uae-gold-200">
                    {{ trans_choice('{0} No languages configured|{1} :count language|[2,*] :count languages', $languages->count(), ['count' => $languages->count()]) }}
                </span>
            </div>

            <div class="mt-6 space-y-6">
                @forelse($languages as $language)
                    <div class="rounded-xl border border-white/10 bg-slate-900/50 p-5">
                        <h3 class="text-sm font-semibold text-white">
                            {{ $language->name }} <span class="text-xs uppercase text-slate-400">({{ $language->code }})</span>
                        </h3>
                        <input type="hidden" name="translations[{{ $loop->index }}][language_id]" value="{{ $language->id }}">

                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    {{ __('Title') }}
                                </label>
                                <input type="text" name="translations[{{ $loop->index }}][title]" value="{{ old("translations.$loop->index.title") }}" class="mt-2 w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40" placeholder="{{ __('Leadership Simulation') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    {{ __('Description') }}
                                </label>
                                <textarea name="translations[{{ $loop->index }}][description]" rows="3" class="mt-2 w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40" placeholder="{{ __('Explain the objective of this assessment for participants.') }}">{{ old("translations.$loop->index.description") }}</textarea>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-white/10 px-4 py-6 text-center text-sm text-slate-300">
                        {{ __('Add languages in the admin panel to localize assessments.') }}
                    </p>
                @endforelse
            </div>

            @error('translations')
                <p class="mt-4 text-xs text-rose-300">{{ $message }}</p>
            @enderror
        </div>

        {{-- Questions Section --}}
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg shadow-black/10">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">{{ __('Questions') }}</h2>
                    <p class="mt-1 text-xs text-slate-400">{{ __('Build your assessment questions.') }}</p>
                </div>
                <button type="button" id="add-question-btn" class="rounded-lg bg-uae-gold-300/80 px-4 py-2 text-sm font-semibold text-white transition hover:bg-uae-gold-300">
                    + {{ __('Add question') }}
                </button>
            </div>

            <div id="questions-container" class="mt-6 space-y-6">
                {{-- Questions will be added here dynamically --}}
            </div>
            @error('questions')
                <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
            @enderror
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ auth()->user()->role === 'admin' ? route('dashboard.admin') : route('dashboard.manager') }}" class="rounded-lg border border-white/10 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/10">
                {{ __('Cancel') }}
            </a>
            <button type="submit" class="rounded-lg bg-uae-gold-300/90 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-uae-gold-300">
                {{ __('Create assessment') }}
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            window.languages = @json($languages->map(fn($lang) => ['id' => $lang->id, 'name' => $lang->name, 'code' => $lang->code]));
        </script>
        @vite(['resources/js/assessment-builder.js'])
    @endpush
@endsection
