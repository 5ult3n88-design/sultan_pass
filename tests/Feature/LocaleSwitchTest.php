<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_switch_locale(): void
    {
        $response = $this->from('/')->post(route('locale.switch'), ['locale' => 'ar']);

        $response->assertRedirect('/');
        $this->assertEquals('ar', session('locale'));
    }

    public function test_authenticated_user_can_switch_locale(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->from('/dashboard/admin')
            ->post(route('locale.switch'), [
                'locale' => 'ar',
                'redirect' => '/dashboard/admin',
            ]);

        $response->assertRedirect('/dashboard/admin');
        $this->assertEquals('ar', session('locale'));
    }
}

