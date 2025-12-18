@extends('layouts.dashboard', [
    'title' => __('Password Reset Requests'),
    'subtitle' => __('Review and approve user-initiated password resets'),
])

@section('content')
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-6">
        <div>
            <p class="text-sm text-slate-300">
                {{ __('Pending requests are awaiting a super administrator to generate a new password for the requester.') }}
            </p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200">
            <span class="inline-flex h-3 w-3 rounded-full {{ $pendingCount > 0 ? 'bg-amber-400' : 'bg-emerald-400' }}"></span>
            <span>
                {{ trans_choice('{0} No pending requests|{1} :count pending request|[2,*] :count pending requests', $pendingCount, ['count' => $pendingCount]) }}
            </span>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
        <table class="min-w-full divide-y divide-white/10 text-sm">
            <thead class="bg-white/5 text-left text-xs uppercase tracking-wide text-slate-300">
                <tr>
                    <th class="px-5 py-3">{{ __('Requester') }}</th>
                    <th class="px-5 py-3">{{ __('Email') }}</th>
                    <th class="px-5 py-3">{{ __('Status') }}</th>
                    <th class="px-5 py-3">{{ __('Requested on') }}</th>
                    <th class="px-5 py-3">{{ __('Processed by') }}</th>
                    <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-slate-100">
                @forelse($requests as $resetRequest)
                    <tr>
                        <td class="px-5 py-4">
                            <div class="flex flex-col">
                                <span class="font-semibold">
                                    {{ $resetRequest->user?->full_name ?: $resetRequest->user?->username }}
                                </span>
                                <span class="text-xs text-slate-400">
                                    {{ __('Token: :token', ['token' => \Illuminate\Support\Str::limit($resetRequest->token, 12, '...')]) }}
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            {{ $resetRequest->email }}
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $statusColors = [
                                    \App\Models\PasswordResetRequest::STATUS_PENDING => 'bg-amber-500/20 text-amber-200',
                                    \App\Models\PasswordResetRequest::STATUS_APPROVED => 'bg-emerald-500/20 text-emerald-200',
                                    \App\Models\PasswordResetRequest::STATUS_DECLINED => 'bg-rose-500/20 text-rose-200',
                                ];
                            @endphp
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusColors[$resetRequest->status] ?? 'bg-slate-500/20 text-slate-200' }}">
                                {{ __(ucfirst($resetRequest->status)) }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            {{ optional($resetRequest->created_at)->format('Y-m-d H:i') ?? '—' }}
                        </td>
                        <td class="px-5 py-4">
                            {{ $resetRequest->approver?->username ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.password-resets.show', $resetRequest) }}" class="rounded-lg border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                                {{ __('View') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-300">
                            {{ __('No password reset requests have been submitted yet.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $requests->links() }}
    </div>
@endsection

