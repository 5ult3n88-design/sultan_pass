@extends('layouts.dashboard', [
    'title' => __('Reset request for :user', ['user' => $requestRecord->user?->full_name ?: $requestRecord->user?->username]),
    'subtitle' => __('Token :token', ['token' => $requestRecord->token]),
])

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.password-resets.index') }}" class="inline-flex items-center gap-2 text-sm text-uae-gold-300 hover:text-uae-gold-100">
            <span aria-hidden="true">&larr;</span>
            {{ __('Back to requests') }}
        </a>
    </div>

    @if($temporaryPassword)
        <div class="mb-6 rounded-2xl border border-emerald-500/40 bg-emerald-500/15 px-5 py-4">
            <p class="text-sm font-semibold text-emerald-100 uppercase tracking-wide">
                {{ __('Temporary password (share securely)') }}
            </p>
            <p class="mt-2 text-xl font-mono text-white">{{ $temporaryPassword }}</p>
            <p class="mt-3 text-xs text-emerald-200">
                {{ __('Expires on :date', ['date' => optional($requestRecord->temporary_password_expires_at)->format('Y-m-d H:i') ?? __('N/A')]) }}
            </p>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-white">{{ __('Request details') }}</h2>
            <dl class="space-y-3 text-sm text-slate-200">
                <div class="flex justify-between">
                    <dt class="text-slate-400">{{ __('Requester') }}</dt>
                    <dd class="text-right">
                        <div>{{ $requestRecord->user?->full_name ?? '—' }}</div>
                        <div class="text-xs text-slate-400">{{ $requestRecord->user?->username }}</div>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">{{ __('Email') }}</dt>
                    <dd>{{ $requestRecord->email }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">{{ __('Status') }}</dt>
                    <dd>
                        @php
                            $statusColors = [
                                \App\Models\PasswordResetRequest::STATUS_PENDING => 'bg-amber-500/20 text-amber-200',
                                \App\Models\PasswordResetRequest::STATUS_APPROVED => 'bg-emerald-500/20 text-emerald-200',
                                \App\Models\PasswordResetRequest::STATUS_DECLINED => 'bg-rose-500/20 text-rose-200',
                            ];
                        @endphp
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusColors[$requestRecord->status] ?? 'bg-slate-500/20 text-slate-200' }}">
                            {{ __(ucfirst($requestRecord->status)) }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">{{ __('Requested on') }}</dt>
                    <dd>{{ optional($requestRecord->created_at)->format('Y-m-d H:i') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">{{ __('Approved on') }}</dt>
                    <dd>{{ optional($requestRecord->approved_at)->format('Y-m-d H:i') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">{{ __('Declined on') }}</dt>
                    <dd>{{ optional($requestRecord->declined_at)->format('Y-m-d H:i') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-400">{{ __('Processed by') }}</dt>
                    <dd>{{ $requestRecord->approver?->username ?? '—' }}</dd>
                </div>
                @if($requestRecord->notes)
                    <div>
                        <dt class="text-slate-400">{{ __('Notes') }}</dt>
                        <dd class="mt-1 whitespace-pre-line">{{ $requestRecord->notes }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-lg font-semibold text-white">{{ __('Administrative actions') }}</h2>
            <p class="mt-1 text-xs text-slate-400">{{ __('Approve to generate a new password automatically, or decline with an optional note.') }}</p>

            @if($requestRecord->status === \App\Models\PasswordResetRequest::STATUS_PENDING)
                <div class="mt-5 space-y-4">
                    <form action="{{ route('admin.password-resets.approve', $requestRecord) }}" method="POST" class="space-y-3">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-emerald-500/80 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-500">
                            {{ __('Approve and generate password') }}
                        </button>
                    </form>

                    <form action="{{ route('admin.password-resets.decline', $requestRecord) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label for="notes" class="block text-xs font-semibold uppercase tracking-wide text-slate-400">
                                {{ __('Decline notes (optional)') }}
                            </label>
                            <textarea name="notes" id="notes" rows="3" class="mt-1 w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-200 focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-500/30">{{ old('notes') }}</textarea>
                        </div>
                        <button type="submit" class="w-full rounded-lg border border-rose-500/40 bg-rose-500/20 px-4 py-2 text-sm font-semibold text-rose-100 transition hover:bg-rose-500/30">
                            {{ __('Decline request') }}
                        </button>
                    </form>
                </div>
            @else
                <div class="mt-5 rounded-xl border border-white/10 bg-slate-900/60 px-4 py-3 text-sm text-slate-200">
                    <p class="font-semibold">
                        {{ __('This request has already been processed.') }}
                    </p>
                    <p class="mt-1 text-xs text-slate-400">
                        {{ __('You can share the stored temporary password above if approved, or update the user directly from the directory.') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection

