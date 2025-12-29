@extends('layouts.dashboard')

@section('content')
<x-slot name="title">{{ __('AI Assistant') }}</x-slot>
<x-slot name="subtitle">{{ __('Get AI-powered insights about participants and assessments') }}</x-slot>

<style>
    /* AI Response Formatting Styles */
    .formatted-response {
        line-height: 1.8;
    }

    .formatted-response .header-section {
        color: #D4AF37;
        font-size: 1.125rem;
        font-weight: 700;
        margin-top: 1.25rem;
        margin-bottom: 0.75rem;
        letter-spacing: 0.025em;
        text-transform: uppercase;
    }

    .formatted-response .header-section:first-child {
        margin-top: 0;
    }

    .formatted-response .numbered-section {
        color: #E5E7EB;
        font-weight: 600;
        font-size: 1.05rem;
        display: block;
        margin-top: 0.75rem;
        margin-bottom: 0.25rem;
    }

    .formatted-response .keyword {
        color: #F59E0B;
        font-weight: 700;
        font-size: 1.05rem;
    }

    .formatted-response .bullet {
        color: #D4AF37;
        font-size: 1.2rem;
        margin-right: 0.5rem;
    }

    .formatted-response .assessment {
        color: #10B981;
        font-weight: 700;
        padding: 0.125rem 0.25rem;
        border-radius: 0.25rem;
    }

    .formatted-response .ranking {
        color: #D4AF37;
        font-weight: 800;
        font-size: 1.1rem;
        background: rgba(212, 175, 55, 0.1);
        padding: 0.125rem 0.375rem;
        border-radius: 0.25rem;
    }

    /* Better spacing for readability */
    .formatted-response br + br {
        display: block;
        margin-top: 0.5rem;
    }

    /* Comparison Table Styles */
    .comparison-table-wrapper {
        margin-top: 0.5rem;
    }

    .comparison-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        background: rgba(31, 35, 40, 0.5);
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .comparison-table thead {
        background: linear-gradient(135deg, rgba(212, 175, 55, 0.15), rgba(212, 175, 55, 0.05));
        border-bottom: 2px solid rgba(212, 175, 55, 0.3);
    }

    .comparison-table th {
        padding: 1rem;
        text-align: left;
        font-weight: 700;
        font-size: 1.05rem;
        color: #D4AF37;
        border-right: 1px solid rgba(212, 175, 55, 0.1);
    }

    .comparison-table th:first-child {
        width: 120px;
        background: rgba(212, 175, 55, 0.08);
    }

    .comparison-table th:last-child {
        border-right: none;
    }

    .comparison-table .candidate-detail {
        display: block;
        font-size: 0.85rem;
        font-weight: 400;
        color: #9CA3AF;
        margin-top: 0.25rem;
    }

    .comparison-table tbody tr {
        border-bottom: 1px solid rgba(212, 175, 55, 0.1);
    }

    .comparison-table tbody tr:last-child {
        border-bottom: none;
    }

    .comparison-table tbody tr:hover {
        background: rgba(212, 175, 55, 0.05);
    }

    .comparison-table td {
        padding: 1rem;
        vertical-align: top;
        border-right: 1px solid rgba(212, 175, 55, 0.1);
        color: #E5E7EB;
    }

    .comparison-table td:last-child {
        border-right: none;
    }

    .comparison-table .row-label {
        font-weight: 600;
        color: #D4AF37;
        background: rgba(212, 175, 55, 0.08);
    }

    .comparison-table .rank-detail {
        display: block;
        font-size: 0.9rem;
        color: #9CA3AF;
        margin-top: 0.5rem;
        line-height: 1.5;
    }

    .strengths-list {
        list-style: none;
        padding: 0;
        margin-top: 0.75rem;
    }

    .strengths-list li {
        padding: 0.5rem 0;
        color: #E5E7EB;
        line-height: 1.6;
    }

    .recommendation-text {
        color: #E5E7EB;
        line-height: 1.8;
        margin-top: 0.75rem;
        padding: 1rem;
        background: rgba(212, 175, 55, 0.05);
        border-left: 3px solid #D4AF37;
        border-radius: 0.25rem;
    }

    /* Custom Dropdown Styles */
    .custom-dropdown {
        position: relative;
    }

    .dropdown-button {
        cursor: pointer;
        user-select: none;
    }

    .dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 50;
        margin-top: 0.25rem;
        max-height: 16rem;
        overflow-y: auto;
        background: #1F2328;
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
        display: none;
    }

    .dropdown-menu.show {
        display: block;
    }

    .dropdown-item {
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        transition: background-color 0.15s;
    }

    .dropdown-item:hover {
        background: rgba(212, 175, 55, 0.1);
    }

    .dropdown-item input[type="checkbox"] {
        width: 1.125rem;
        height: 1.125rem;
        accent-color: #D4AF37;
        cursor: pointer;
    }

    .dropdown-item label {
        flex: 1;
        cursor: pointer;
        color: #E5E7EB;
        font-size: 0.875rem;
    }

    .dropdown-selected-count {
        display: inline-block;
        margin-left: 0.5rem;
        padding: 0.125rem 0.5rem;
        background: rgba(212, 175, 55, 0.2);
        color: #D4AF37;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Panel: Chat Interface -->
    <div class="lg:col-span-2">
        <div class="bg-iron-900/80 backdrop-blur rounded-xl border border-uae-gold-300/20 overflow-hidden">
            <!-- Chat Messages -->
            <div id="chat-messages" class="h-[500px] overflow-y-auto p-6 space-y-4" style="scrollbar-width: thin; scrollbar-color: rgba(182, 138, 53, 0.3) transparent;">
                <!-- Welcome Message -->
                <div class="flex gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-uae-gold-300/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-uae-gold-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="bg-iron-800/50 rounded-lg p-4 border border-uae-gold-300/10">
                            <p class="text-silver-200 text-sm">{{ __('Welcome to the AI Assistant! Select an analysis mode and configure the options to get started. I can help you with:') }}</p>
                            <ul class="mt-3 space-y-1 text-sm text-silver-300 list-disc list-inside">
                                <li>{{ __('Performance analysis of specific assessments') }}</li>
                                <li>{{ __('Mission fit evaluations') }}</li>
                                <li>{{ __('Overall participant analysis') }}</li>
                                <li>{{ __('Candidate comparisons') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="border-t border-uae-gold-300/20 p-4 bg-iron-900">
                <div class="flex gap-3">
                    <input
                        type="text"
                        id="user-question"
                        placeholder="{{ __('Ask a specific question (optional)...') }}"
                        class="flex-1 bg-iron-800 border border-uae-gold-300/20 rounded-lg px-4 py-3 text-silver-200 placeholder-silver-500 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/50"
                    >
                    <button
                        onclick="sendMessage()"
                        id="send-btn"
                        class="bg-uae-gold-400 hover:bg-uae-gold-500 text-iron-900 font-semibold px-6 py-3 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ __('Ask AI') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel: Configuration -->
    <div class="lg:col-span-1">
        <div class="bg-iron-900/80 backdrop-blur rounded-xl border border-uae-gold-300/20 p-6 space-y-6">
            <h3 class="text-lg font-semibold text-white">{{ __('Analysis Mode') }}</h3>

            <!-- Mode Selection -->
            <div class="space-y-3">
                <label class="flex items-start gap-3 cursor-pointer group">
                    <input type="radio" name="mode" value="test_based" checked class="mt-1 text-uae-gold-400 focus:ring-uae-gold-400">
                    <div>
                        <div class="text-sm font-medium text-silver-200 group-hover:text-uae-gold-300">{{ __('Test-Based Analysis') }}</div>
                        <div class="text-xs text-silver-400">{{ __('Analyze performance in a specific assessment') }}</div>
                    </div>
                </label>

                <label class="flex items-start gap-3 cursor-pointer group">
                    <input type="radio" name="mode" value="mission_based" class="mt-1 text-uae-gold-400 focus:ring-uae-gold-400">
                    <div>
                        <div class="text-sm font-medium text-silver-200 group-hover:text-uae-gold-300">{{ __('Mission Fit') }}</div>
                        <div class="text-xs text-silver-400">{{ __('Evaluate fit for a specific mission/role') }}</div>
                    </div>
                </label>

                <label class="flex items-start gap-3 cursor-pointer group">
                    <input type="radio" name="mode" value="overall" class="mt-1 text-uae-gold-400 focus:ring-uae-gold-400">
                    <div>
                        <div class="text-sm font-medium text-silver-200 group-hover:text-uae-gold-300">{{ __('Overall Analysis') }}</div>
                        <div class="text-xs text-silver-400">{{ __('Complete profile across all assessments') }}</div>
                    </div>
                </label>

                <label class="flex items-start gap-3 cursor-pointer group">
                    <input type="radio" name="mode" value="comparison" class="mt-1 text-uae-gold-400 focus:ring-uae-gold-400">
                    <div>
                        <div class="text-sm font-medium text-silver-200 group-hover:text-uae-gold-300">{{ __('Compare Candidates') }}</div>
                        <div class="text-xs text-silver-400">{{ __('Rank multiple candidates for a role') }}</div>
                    </div>
                </label>
            </div>

            <hr class="border-uae-gold-300/20">

            <!-- Participant Selection -->
            <div id="participant-section">
                <label class="block text-sm font-medium text-silver-200 mb-2">
                    <span id="participant-label">{{ __('Select Participant') }}</span>
                    <span class="text-uae-gold-400">*</span>
                </label>

                <!-- Custom Dropdown -->
                <div class="custom-dropdown">
                    <!-- Dropdown Button -->
                    <div id="participant-dropdown-btn" class="dropdown-button w-full bg-iron-800 border border-uae-gold-300/20 rounded-lg px-3 py-2.5 text-silver-200 text-sm focus:outline-none focus:ring-2 focus:ring-uae-gold-300/50 flex items-center justify-between">
                        <span id="participant-selected-text" class="text-silver-400">{{ __('Click to select participants...') }}</span>
                        <svg class="w-4 h-4 text-silver-400 transition-transform" id="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>

                    <!-- Dropdown Menu -->
                    <div id="participant-dropdown-menu" class="dropdown-menu">
                        <!-- Search Bar -->
                        <div class="p-3 border-b border-uae-gold-300/20">
                            <div class="relative">
                                <input
                                    type="text"
                                    id="participant-search"
                                    placeholder="{{ __('Search by name or department...') }}"
                                    class="w-full bg-iron-900 border border-uae-gold-300/20 rounded-lg pl-9 pr-3 py-2 text-silver-200 text-sm placeholder-silver-500 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/50"
                                >
                                <svg class="absolute left-3 top-2.5 w-4 h-4 text-silver-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Participant Items -->
                        <div id="participant-items">
                            @foreach($participants as $participant)
                                <div class="dropdown-item" data-name="{{ strtolower($participant->full_name) }}" data-department="{{ strtolower($participant->department ?? '') }}">
                                    <input
                                        type="checkbox"
                                        id="participant-{{ $participant->id }}"
                                        value="{{ $participant->id }}"
                                        class="participant-checkbox"
                                    >
                                    <label for="participant-{{ $participant->id }}">
                                        {{ $participant->full_name }}
                                        <span class="text-silver-400">({{ $participant->department ?? 'N/A' }})</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <p id="participant-hint" class="mt-1 text-xs text-silver-400">{{ __('Select one participant') }}</p>
            </div>

            <!-- Assessment Selection (for test-based mode) -->
            <div id="assessment-section">
                <label class="block text-sm font-medium text-silver-200 mb-2">
                    {{ __('Select Assessment') }}
                    <span id="assessment-required" class="text-uae-gold-400 hidden">*</span>
                </label>
                <select
                    id="assessment-select"
                    class="w-full bg-iron-800 border border-uae-gold-300/20 rounded-lg px-3 py-2 text-silver-200 text-sm focus:outline-none focus:ring-2 focus:ring-uae-gold-300/50"
                >
                    <option value="">{{ __('All Assessments') }}</option>
                    @foreach($assessments as $assessment)
                        <option value="{{ $assessment->id }}">
                            {{ $assessment->translations->first()->title ?? $assessment->type }} - {{ $assessment->created_at->format('Y-m-d') }}
                        </option>
                    @endforeach
                </select>
                <p id="assessment-hint" class="mt-1 text-xs text-silver-400">{{ __('Optional for broader analysis') }}</p>
            </div>

            <!-- Mission Details (for mission_based and comparison modes) -->
            <div id="mission-section" class="hidden">
                <label class="block text-sm font-medium text-silver-200 mb-2">
                    {{ __('Mission/Role Details') }}
                    <span class="text-uae-gold-400">*</span>
                </label>
                <textarea
                    id="mission-details"
                    rows="4"
                    placeholder="{{ __('Describe the mission requirements, key skills needed, responsibilities, challenges...') }}"
                    class="w-full bg-iron-800 border border-uae-gold-300/20 rounded-lg px-3 py-2 text-silver-200 text-sm placeholder-silver-500 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/50"
                ></textarea>
                <p class="mt-1 text-xs text-silver-400">{{ __('Be specific about requirements for better analysis') }}</p>
            </div>
        </div>
    </div>
</div>

<script>
    // Update UI based on selected mode
    function updateModeUI(mode) {
        const missionSection = document.getElementById('mission-section');
        const assessmentSection = document.getElementById('assessment-section');
        const participantLabel = document.getElementById('participant-label');
        const participantHint = document.getElementById('participant-hint');
        const assessmentRequired = document.getElementById('assessment-required');
        const assessmentHint = document.getElementById('assessment-hint');

        // Reset all
        missionSection.classList.add('hidden');
        assessmentRequired.classList.add('hidden');

        switch(mode) {
            case 'test_based':
                participantLabel.textContent = 'Select Participant';
                participantHint.textContent = 'Select one participant to analyze';
                assessmentRequired.classList.remove('hidden');
                assessmentHint.textContent = 'Required: Select specific assessment to analyze';
                break;

            case 'mission_based':
                participantLabel.textContent = 'Select Participant';
                participantHint.textContent = 'Select one participant to evaluate';
                missionSection.classList.remove('hidden');
                assessmentHint.textContent = 'Optional: Focus on specific assessment or use all data';
                break;

            case 'overall':
                participantLabel.textContent = 'Select Participant';
                participantHint.textContent = 'Select one participant for complete analysis';
                assessmentHint.textContent = 'Optional: Focus on specific assessment or use all';
                break;

            case 'comparison':
                participantLabel.textContent = 'Select Participants (Multiple)';
                participantHint.textContent = 'Select 2+ participants to compare (Hold Ctrl/Cmd)';
                missionSection.classList.remove('hidden');
                assessmentHint.textContent = 'Optional: Compare based on specific assessment';
                break;
        }
    }

    // Show/hide sections based on mode
    document.querySelectorAll('input[name="mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            updateModeUI(this.value);
        });
    });

    // Initialize on page load
    updateModeUI('test_based');

    // Dropdown functionality
    const dropdownBtn = document.getElementById('participant-dropdown-btn');
    const dropdownMenu = document.getElementById('participant-dropdown-menu');
    const dropdownArrow = document.getElementById('dropdown-arrow');
    const selectedText = document.getElementById('participant-selected-text');
    const participantCheckboxes = document.querySelectorAll('.participant-checkbox');

    // Toggle dropdown
    dropdownBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownMenu.classList.toggle('show');
        dropdownArrow.style.transform = dropdownMenu.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.remove('show');
            dropdownArrow.style.transform = 'rotate(0deg)';
        }
    });

    // Prevent dropdown from closing when clicking inside menu
    dropdownMenu.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Update selected text when checkboxes change
    function updateSelectedText() {
        const checkedBoxes = document.querySelectorAll('.participant-checkbox:checked');
        const count = checkedBoxes.length;

        if (count === 0) {
            selectedText.innerHTML = '<span class="text-silver-400">Click to select participants...</span>';
        } else if (count === 1) {
            const label = document.querySelector(`label[for="${checkedBoxes[0].id}"]`);
            const name = label.textContent.trim().split('(')[0].trim();
            selectedText.innerHTML = `${name} <span class="dropdown-selected-count">${count}</span>`;
        } else {
            const label = document.querySelector(`label[for="${checkedBoxes[0].id}"]`);
            const firstName = label.textContent.trim().split('(')[0].trim();
            selectedText.innerHTML = `${firstName} <span class="dropdown-selected-count">+${count - 1} more</span>`;
        }
    }

    // Handle checkbox changes
    participantCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedText);
    });

    // Participant search functionality
    document.getElementById('participant-search').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const items = document.querySelectorAll('.dropdown-item');

        items.forEach(item => {
            const name = item.getAttribute('data-name');
            const department = item.getAttribute('data-department');

            // Show item if search term matches name or department
            if (name.includes(searchTerm) || department.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });

    async function sendMessage() {
        const mode = document.querySelector('input[name="mode"]:checked').value;
        const checkedBoxes = document.querySelectorAll('.participant-checkbox:checked');
        const participantIds = Array.from(checkedBoxes).map(cb => cb.value);
        const assessmentId = document.getElementById('assessment-select').value;
        const missionDetails = document.getElementById('mission-details').value;
        const question = document.getElementById('user-question').value;
        const sendBtn = document.getElementById('send-btn');
        const chatMessages = document.getElementById('chat-messages');

        // Mode-specific validation
        if (participantIds.length === 0) {
            alert('{{ __("Please select at least one participant") }}');
            return;
        }

        // Test-based mode: requires 1 participant and 1 assessment
        if (mode === 'test_based') {
            if (participantIds.length > 1) {
                alert('{{ __("Test-Based Analysis: Please select only ONE participant") }}');
                return;
            }
            if (!assessmentId) {
                alert('{{ __("Test-Based Analysis: Please select a specific assessment") }}');
                return;
            }
        }

        // Mission-fit mode: requires 1 participant and mission details
        if (mode === 'mission_based') {
            if (participantIds.length > 1) {
                alert('{{ __("Mission Fit: Please select only ONE participant") }}');
                return;
            }
            if (!missionDetails || missionDetails.trim() === '') {
                alert('{{ __("Mission Fit: Please provide mission/role details") }}');
                return;
            }
        }

        // Overall mode: requires 1 participant only
        if (mode === 'overall') {
            if (participantIds.length > 1) {
                alert('{{ __("Overall Analysis: Please select only ONE participant") }}');
                return;
            }
        }

        // Comparison mode: requires 2+ participants and mission details
        if (mode === 'comparison') {
            if (participantIds.length < 2) {
                alert('{{ __("Compare Candidates: Please select at least 2 participants") }}');
                return;
            }
            if (!missionDetails || missionDetails.trim() === '') {
                alert('{{ __("Compare Candidates: Please provide mission/role details for comparison") }}');
                return;
            }
        }

        // Add user message to chat
        const userMessage = document.createElement('div');
        userMessage.className = 'flex gap-3 justify-end';
        userMessage.innerHTML = `
            <div class="flex-1 max-w-2xl">
                <div class="bg-uae-gold-400/20 rounded-lg p-4 border border-uae-gold-300/20">
                    <p class="text-silver-200 text-sm">${question || '{{ __("Analyze based on selected configuration") }}'}</p>
                </div>
            </div>
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full bg-iron-800 flex items-center justify-center">
                    <svg class="w-6 h-6 text-silver-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
        `;
        chatMessages.appendChild(userMessage);

        // Add loading message
        const loadingMessage = document.createElement('div');
        loadingMessage.className = 'flex gap-3';
        loadingMessage.id = 'loading-message';
        loadingMessage.innerHTML = `
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full bg-uae-gold-300/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-uae-gold-300 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <div class="bg-iron-800/50 rounded-lg p-4 border border-uae-gold-300/10">
                    <p class="text-silver-300 text-sm">{{ __('AI is thinking... This may take 30-60 seconds.') }}</p>
                </div>
            </div>
        `;
        chatMessages.appendChild(loadingMessage);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // Disable send button
        sendBtn.disabled = true;

        try {
            const response = await fetch('{{ route("ai-assistant.chat") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    mode,
                    participant_ids: participantIds,
                    assessment_id: assessmentId || null,
                    mission_details: missionDetails,
                    question: question || null
                })
            });

            const data = await response.json();

            // Remove loading message
            document.getElementById('loading-message').remove();

            if (data.success) {
                // Add AI response
                const aiMessage = document.createElement('div');
                aiMessage.className = 'flex gap-3';
                aiMessage.innerHTML = `
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-uae-gold-300/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-uae-gold-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="bg-iron-800/50 rounded-lg p-6 border border-uae-gold-300/10">
                            <div class="text-silver-200 text-base font-sans formatted-response">${formatAIResponse(data.response)}</div>
                        </div>
                    </div>
                `;
                chatMessages.appendChild(aiMessage);
                chatMessages.scrollTop = chatMessages.scrollHeight;

                // Clear question input
                document.getElementById('user-question').value = '';
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            document.getElementById('loading-message').remove();
            alert('Error: ' + error.message);
        } finally {
            sendBtn.disabled = false;
        }
    }

    // Format AI response with bold keywords and structured layout
    function formatAIResponse(text) {
        // Check if this is a comparison (has CANDIDATES or multiple numbered rankings)
        const isComparison = text.includes('CANDIDATES:') || (text.match(/\b\d+(?:st|nd|rd|th)\b/g) || []).length >= 2;

        if (isComparison) {
            return formatComparisonAsTable(text);
        }

        // Replace ALL-CAPS headers with bold HTML and gold color
        text = text.replace(/^([A-Z][A-Z\s]+):$/gm, '<div class="header-section"><strong>$1:</strong></div>');

        // Bold numbered sections (1., 2., 3., etc.)
        text = text.replace(/^(\d+\.\s+[A-Z][A-Z\s]+)/gm, '<strong class="numbered-section">$1</strong>');

        // Bold keywords in parentheses like (Good/Fair/Poor) or rankings (1st, 2nd)
        text = text.replace(/\(([^)]+)\)/g, '<strong class="keyword">($1)</strong>');

        // Format bullet points with gold bullets
        text = text.replace(/^-\s+/gm, '<span class="bullet">•</span> ');

        // Bold stand-alone assessment words (Good, Fair, Poor, etc.)
        text = text.replace(/\b(Good|Fair|Poor|Excellent|Strong|Weak|High|Medium|Low)\b/g, '<strong class="assessment">$1</strong>');

        // Format ranking positions (1st, 2nd, 3rd, etc.)
        text = text.replace(/\b(\d+(?:st|nd|rd|th))\b/g, '<strong class="ranking">$1</strong>');

        // Add line breaks for readability
        text = text.replace(/\n/g, '<br>');

        return text;
    }

    // Format comparison responses as a table
    function formatComparisonAsTable(text) {
        // Extract candidates - try multiple patterns
        let candidates = [];

        // Pattern 1: "CANDIDATES:\n1. Name - Rank (Department)"
        const candidatesMatch1 = text.match(/CANDIDATES:\n((?:\d+\.\s+.+\n?)+)/);
        if (candidatesMatch1) {
            const candidateLines = candidatesMatch1[1].trim().split('\n');
            candidates = candidateLines.map(line => {
                const match = line.match(/\d+\.\s+(.+?)\s+-\s+(.+?)\s+\((.+?)\)/);
                if (match) {
                    return {
                        name: match[1].trim(),
                        rank: match[2].trim(),
                        department: match[3].trim()
                    };
                }
                return null;
            }).filter(c => c !== null);
        }

        // Pattern 2: Extract from ranking sections like "#### 1. Name - Rank (Department)"
        if (candidates.length === 0) {
            const candidateMatches = text.matchAll(/####?\s*\d+\.\s+(.+?)\s+-\s+(.+?)\s+\((.+?)\)/g);
            candidates = Array.from(candidateMatches).map(match => ({
                name: match[1].trim(),
                rank: match[2].trim(),
                department: match[3].trim()
            }));
        }

        // Extract rankings from various formats
        let rankings = [];

        // Try to find individual ranking entries like "#### 1. Name - Rank (Department)"
        const rankingMatches = text.matchAll(/####?\s*(\d+)\.\s+(.+?)\s+-\s+(.+?)\s+\((.+?)\)\s*#####\s*Brief Reason:\s*(.+?)(?=####|\*\*Key Strengths|###|$)/gs);

        for (const match of rankingMatches) {
            const position = match[1] === '1' ? '1st' : match[1] === '2' ? '2nd' : match[1] === '3' ? '3rd' : match[1] + 'th';
            rankings.push({
                position: position,
                name: match[2].trim(),
                rank: match[3].trim(),
                department: match[4].trim(),
                details: match[5].trim().replace(/\n/g, ' ').substring(0, 150) + '...'
            });
        }

        // Extract key strengths - look for patterns with bullets or dashes
        let strengths = [];
        const strengthsMatch = text.match(/(?:\*\*)?Key Strengths(?:\*\*)?:?\s*((?:[-•\*].+(?:\n|$))+)/i);
        if (strengthsMatch) {
            strengths = strengthsMatch[1].split('\n')
                .filter(l => l.trim() && /^[-•\*]/.test(l.trim()))
                .map(l => l.replace(/^[-•\*\s]+/, '').replace(/\*\*/g, '').trim())
                .filter(s => s.length > 0);
        }

        // Extract recommendation
        let recommendation = '';
        const recommendationMatch = text.match(/(?:\*\*)?Recommendation(?:\*\*)?:?\s*((?:.|\n)*?)(?=###|####|\*\*Final Thoughts|$)/i);
        if (recommendationMatch) {
            recommendation = recommendationMatch[1].trim()
                .replace(/^[-•]\s*/, '')
                .replace(/\n/g, ' ')
                .substring(0, 300);
        }

        // Build table HTML
        let html = '<div class="comparison-table-wrapper">';

        // Candidates table
        if (candidates.length > 0 && rankings.length > 0) {
            html += '<div class="header-section"><strong>CANDIDATE COMPARISON</strong></div>';
            html += '<table class="comparison-table">';
            html += '<thead><tr>';
            html += '<th style="width: 100px;">Position</th>';
            candidates.forEach(c => {
                html += `<th>${c.name}<br><span class="candidate-detail">${c.rank}<br>${c.department}</span></th>`;
            });
            html += '</tr></thead>';
            html += '<tbody>';

            // Create a row for each ranking
            rankings.forEach(r => {
                html += '<tr>';
                html += `<td class="row-label"><strong class="ranking">${r.position}</strong></td>`;

                // Match this ranking to candidates
                candidates.forEach(c => {
                    if (c.name === r.name) {
                        html += `<td><span class="rank-detail">${r.details}</span></td>`;
                    } else {
                        html += `<td class="text-center" style="opacity: 0.3;">—</td>`;
                    }
                });
                html += '</tr>';
            });

            html += '</tbody></table>';
        }

        // Key strengths section
        if (strengths.length > 0) {
            html += '<div class="header-section" style="margin-top: 1.5rem;"><strong>KEY STRENGTHS (Top Candidate)</strong></div>';
            html += '<ul class="strengths-list">';
            strengths.forEach(s => {
                html += `<li><span class="bullet">•</span> ${s}</li>`;
            });
            html += '</ul>';
        }

        // Recommendation section
        if (recommendation) {
            html += '<div class="header-section" style="margin-top: 1.5rem;"><strong>RECOMMENDATION</strong></div>';
            html += `<div class="recommendation-text">${recommendation}</div>`;
        }

        html += '</div>';

        return html;
    }

    // Allow Enter key to send
    document.getElementById('user-question').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
</script>
@endsection
