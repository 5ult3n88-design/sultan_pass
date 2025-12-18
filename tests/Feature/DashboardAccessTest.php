<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('dashboard.admin'));

        $response->assertOk();
        $response->assertSeeText('Administrator Dashboard');
    }

    public function test_participant_cannot_view_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'participant']);

        $response = $this->actingAs($user)->get(route('dashboard.admin'));

        $response->assertForbidden();
    }
}

