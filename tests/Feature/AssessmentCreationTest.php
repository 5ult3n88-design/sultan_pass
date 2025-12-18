<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AssessmentCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_access_create_assessment_form(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($manager)->get(route('assessments.create'));

        $response->assertOk();
        $response->assertSeeText(__('Assessment details'));
    }

    public function test_manager_can_create_assessment_with_translations(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $english = Language::factory()->create(['code' => 'en']);
        $arabic = Language::factory()->create(['code' => 'ar']);

        $payload = [
            'type' => 'psychometric',
            'status' => 'draft',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addWeek()->format('Y-m-d'),
            'translations' => [
                [
                    'language_id' => $english->id,
                    'title' => 'Leadership Simulation',
                    'description' => 'Assess leadership skills under pressure.',
                ],
                [
                    'language_id' => $arabic->id,
                    'title' => 'محاكاة القيادة',
                    'description' => 'قيّم مهارات القيادة تحت الضغط.',
                ],
            ],
        ];

        $response = $this->actingAs($manager)->post(route('assessments.store'), $payload);

        $response->assertRedirect(route('dashboard.manager'));
        $this->assertDatabaseHas('assessments', [
            'type' => 'psychometric',
            'created_by' => $manager->id,
        ]);

        $assessment = Assessment::first();

        $this->assertDatabaseHas('assessment_translations', [
            'assessment_id' => $assessment->id,
            'language_id' => $english->id,
            'title' => 'Leadership Simulation',
        ]);

        $this->assertDatabaseHas('assessment_translations', [
            'assessment_id' => $assessment->id,
            'language_id' => $arabic->id,
            'title' => 'محاكاة القيادة',
        ]);
    }

    public function test_participant_cannot_access_assessment_creation(): void
    {
        $participant = User::factory()->create(['role' => 'participant']);

        $this->actingAs($participant)
            ->get(route('assessments.create'))
            ->assertForbidden();
    }
}

