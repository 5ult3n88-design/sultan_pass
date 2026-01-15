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
                    <button type="button" id="export-scoring-btn" class="flex items-center justify-center gap-2 rounded-lg border border-white/10 px-4 py-2 text-slate-200 hover:bg-white/10 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('Export scoring sheet') }}
                    </button>
                    <button type="button" id="send-reminder-btn" class="flex items-center justify-center gap-2 rounded-lg border border-white/10 px-4 py-2 text-slate-200 hover:bg-white/10 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ __('Send reminder to pending participants') }}
                    </button>
                    <button type="button" id="publish-report-btn" class="flex items-center justify-center gap-2 rounded-lg bg-emerald-500/80 px-4 py-2 font-semibold text-white hover:bg-emerald-500 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('Publish interim report') }}
                    </button>
                </div>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6 text-sm text-slate-200">
                <h3 class="text-sm font-semibold text-white">{{ __('Assessment notes') }}</h3>
                <textarea id="assessment-notes" rows="6" class="mt-3 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40" placeholder="{{ __('Record calibration notes, anomalies, or adjustments here...') }}"></textarea>
                <button type="button" id="save-notes-btn" class="mt-3 flex items-center justify-center gap-2 rounded-lg bg-uae-gold-300/80 px-4 py-2 text-sm font-semibold text-white hover:bg-uae-gold-300 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('Save notes') }}
                </button>
            </div>
        </aside>
    </div>

    {{-- Status Toast --}}
    <div id="status-toast" class="fixed bottom-4 right-4 z-50 hidden transform transition-all duration-300 translate-y-full opacity-0">
        <div class="flex items-center gap-3 rounded-lg bg-emerald-500 px-4 py-3 text-white shadow-lg">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span id="toast-message"></span>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const assessmentId = {{ $assessmentId }};
        const participants = @json($participants);
        const overview = @json($overview);

        function showToast(message, isError = false) {
            const toast = document.getElementById('status-toast');
            const toastMessage = document.getElementById('toast-message');
            const toastContainer = toast.querySelector('div');

            toastMessage.textContent = message;
            toastContainer.classList.remove('bg-emerald-500', 'bg-red-500');
            toastContainer.classList.add(isError ? 'bg-red-500' : 'bg-emerald-500');

            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.remove('translate-y-full', 'opacity-0');
            }, 10);

            setTimeout(() => {
                toast.classList.add('translate-y-full', 'opacity-0');
                setTimeout(() => toast.classList.add('hidden'), 300);
            }, 3000);
        }

        // Export Scoring Sheet - Generate CSV
        document.getElementById('export-scoring-btn').addEventListener('click', function() {
            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> {{ __("Exporting...") }}';
            btn.disabled = true;

            setTimeout(() => {
                // Build CSV content
                let csv = 'Name,Status,Score,Strengths\n';
                participants.forEach(p => {
                    const strengths = p.strengths ? p.strengths.join('; ') : '';
                    const score = p.score !== null ? p.score : 'Pending';
                    csv += `"${p.name}","${p.status}","${score}","${strengths}"\n`;
                });

                // Add summary
                csv += '\n';
                csv += `Assessment,${overview.title}\n`;
                csv += `Total Participants,${overview.total}\n`;
                csv += `Submitted,${overview.submitted}\n`;
                csv += `Average Score,${overview.avgScore}%\n`;

                // Download CSV
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `scoring_sheet_assessment_${assessmentId}.csv`;
                link.click();

                btn.innerHTML = originalHtml;
                btn.disabled = false;
                showToast('{{ __("Scoring sheet exported successfully") }}');
            }, 500);
        });

        // Send Reminder to Pending Participants
        document.getElementById('send-reminder-btn').addEventListener('click', function() {
            const btn = this;
            const pendingParticipants = participants.filter(p => p.status === 'pending');

            if (pendingParticipants.length === 0) {
                showToast('{{ __("No pending participants to remind") }}', true);
                return;
            }

            if (!confirm('{{ __("Send reminder to pending participant(s)?") }} (' + pendingParticipants.length + ')')) {
                return;
            }

            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> {{ __("Sending...") }}';
            btn.disabled = true;

            // Simulate sending reminders (in production, this would be an AJAX call)
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                showToast('{{ __("Reminder sent to") }} ' + pendingParticipants.length + ' {{ __("participant(s)") }}');
            }, 1000);
        });

        // Publish Interim Report
        document.getElementById('publish-report-btn').addEventListener('click', function() {
            const btn = this;

            if (!confirm('{{ __("Are you sure you want to publish the interim report? This will be visible to managers.") }}')) {
                return;
            }

            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> {{ __("Publishing...") }}';
            btn.disabled = true;

            // Simulate publishing (in production, this would be an AJAX call)
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                showToast('{{ __("Interim report published successfully") }}');
            }, 1000);
        });

        // Save Notes
        document.getElementById('save-notes-btn').addEventListener('click', function() {
            const btn = this;
            const notes = document.getElementById('assessment-notes').value;

            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> {{ __("Saving...") }}';
            btn.disabled = true;

            // Save to localStorage for now (in production, this would be an AJAX call to save to database)
            setTimeout(() => {
                localStorage.setItem(`assessment_notes_${assessmentId}`, notes);
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                showToast('{{ __("Notes saved successfully") }}');
            }, 500);
        });

        // Load saved notes from localStorage
        const savedNotes = localStorage.getItem(`assessment_notes_${assessmentId}`);
        if (savedNotes) {
            document.getElementById('assessment-notes').value = savedNotes;
        }
    });
    </script>
@endsection
