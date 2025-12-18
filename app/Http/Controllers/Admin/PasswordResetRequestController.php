<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PasswordResetRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetRequestController extends Controller
{
    public function index(): View
    {
        $requests = PasswordResetRequest::query()
            ->with(['user:id,username,full_name,email,role', 'approver:id,username'])
            ->orderByRaw("FIELD(status, ?, ?, ?) DESC", [
                PasswordResetRequest::STATUS_PENDING,
                PasswordResetRequest::STATUS_APPROVED,
                PasswordResetRequest::STATUS_DECLINED,
            ])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.password-resets.index', [
            'requests' => $requests,
            'pendingCount' => PasswordResetRequest::pending()->count(),
        ]);
    }

    public function show(PasswordResetRequest $passwordResetRequest): View
    {
        $passwordResetRequest->load(['user:id,username,full_name,email,role', 'approver:id,username,full_name']);

        $temporaryPassword = null;

        if (
            $passwordResetRequest->status === PasswordResetRequest::STATUS_APPROVED
            && $passwordResetRequest->temporary_password_encrypted
        ) {
            try {
                $temporaryPassword = decrypt($passwordResetRequest->temporary_password_encrypted);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return view('admin.password-resets.show', [
            'requestRecord' => $passwordResetRequest,
            'temporaryPassword' => session('temporary_password', $temporaryPassword),
        ]);
    }

    public function approve(Request $request, PasswordResetRequest $passwordResetRequest): RedirectResponse
    {
        if ($passwordResetRequest->status !== PasswordResetRequest::STATUS_PENDING) {
            return redirect()
                ->route('admin.password-resets.show', $passwordResetRequest)
                ->withErrors(['status' => __('This password reset request has already been processed.')]);
        }

        $plainPassword = Str::random(14);

        DB::transaction(function () use ($passwordResetRequest, $request, $plainPassword) {
            $user = $passwordResetRequest->user;

            $user->forceFill([
                'password' => Hash::make($plainPassword),
                'remember_token' => Str::random(60),
            ])->save();

            $passwordResetRequest->forceFill([
                'status' => PasswordResetRequest::STATUS_APPROVED,
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'temporary_password_encrypted' => encrypt($plainPassword),
                'temporary_password_expires_at' => now()->addDay(),
            ])->save();

            Notification::create([
                'user_id' => $user->id,
                'message' => __('Your password reset request has been approved. Please contact your administrator to obtain your temporary password.'),
                'notification_type' => 'password_reset',
                'sent_at' => now(),
            ]);
        });

        $passwordResetRequest->refresh();

        return redirect()
            ->route('admin.password-resets.show', $passwordResetRequest)
            ->with('status', __('Password reset request approved. Share the temporary password with the user.'))
            ->with('temporary_password', $plainPassword);
    }

    public function decline(Request $request, PasswordResetRequest $passwordResetRequest): RedirectResponse
    {
        if ($passwordResetRequest->status !== PasswordResetRequest::STATUS_PENDING) {
            return redirect()
                ->route('admin.password-resets.show', $passwordResetRequest)
                ->withErrors(['status' => __('This password reset request has already been processed.')]);
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($passwordResetRequest, $validated) {
            $passwordResetRequest->forceFill([
                'status' => PasswordResetRequest::STATUS_DECLINED,
                'declined_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ])->save();

            Notification::create([
                'user_id' => $passwordResetRequest->user_id,
                'message' => __('Your password reset request was declined. Please contact your administrator for further assistance.'),
                'notification_type' => 'password_reset',
                'sent_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.password-resets.index')
            ->with('status', __('Password reset request declined.'));
    }
}
