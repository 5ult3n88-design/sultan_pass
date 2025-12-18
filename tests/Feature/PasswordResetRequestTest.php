<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_request_password_reset_and_notifies_admin(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $participant = User::factory()->create([
            'role' => 'participant',
            'email' => 'participant@example.com',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => $participant->email,
        ]);

        $response->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_requests', [
            'user_id' => $participant->id,
            'email' => $participant->email,
            'status' => PasswordResetRequest::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'notification_type' => 'password_reset',
        ]);
    }

    public function test_admin_can_approve_password_reset_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $user = User::factory()->create([
            'role' => 'participant',
            'password' => Hash::make('initial-pass'),
        ]);

        $requestRecord = PasswordResetRequest::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'token' => (string) Str::uuid(),
        ]);

        $originalHash = $user->password;

        $response = $this->actingAs($admin)->post(route('admin.password-resets.approve', $requestRecord));

        $response
            ->assertRedirect(route('admin.password-resets.show', $requestRecord))
            ->assertSessionHas('temporary_password')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_requests', [
            'id' => $requestRecord->id,
            'status' => PasswordResetRequest::STATUS_APPROVED,
            'approved_by' => $admin->id,
        ]);

        $updatedUser = $user->fresh();

        $this->assertNotSame($originalHash, $updatedUser->password);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $updatedUser->id,
            'notification_type' => 'password_reset',
        ]);
    }
}

