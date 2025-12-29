<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\DeepSeekService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AssessmentScoringController extends Controller
{
    protected DeepSeekService $deepSeekService;

    public function __construct(DeepSeekService $deepSeekService)
    {
        $this->deepSeekService = $deepSeekService;
    }

    /**
     * Score an assessment using AI
     */
    public function scoreAssessment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'assessment_id' => 'required|integer',
            'participant_id' => 'required|integer',
            'competencies' => 'required|array',
            'competencies.*.name' => 'required|string',
            'competencies.*.weight' => 'required|numeric',
            'competencies.*.description' => 'nullable|string',
            'responses' => 'required|array',
            'responses.*.question' => 'required|string',
            'responses.*.answer' => 'required|string',
        ]);

        try {
            $assessmentData = [
                'title' => $request->input('title', 'Assessment'),
                'type' => $request->input('type', 'psychometric'),
                'competencies' => $validated['competencies'],
                'responses' => $validated['responses'],
            ];

            $scoringResult = $this->deepSeekService->scoreAssessment($assessmentData);

            // TODO: Save results to database (assessment_participants, assessor_notes, etc.)
            // Example:
            // DB::table('assessment_participants')->where('id', $validated['participant_id'])->update([
            //     'overall_score' => $scoringResult['overall_score'],
            //     'ai_scored_at' => now(),
            // ]);

            return response()->json([
                'success' => true,
                'data' => $scoringResult,
                'message' => 'Assessment scored successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Assessment scoring failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to score assessment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Analyze qualitative response using AI
     */
    public function analyzeQualitativeResponse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'response' => 'required|string',
            'competency_context' => 'nullable|string',
        ]);

        try {
            $analysis = $this->deepSeekService->analyzeQualitativeResponse(
                $validated['response'],
                $validated['competency_context'] ?? ''
            );

            return response()->json([
                'success' => true,
                'data' => $analysis,
            ]);
        } catch (\Exception $e) {
            Log::error('Qualitative analysis failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze response: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Identify strengths and weaknesses from competency scores
     */
    public function identifyStrengthsWeaknesses(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'competency_scores' => 'required|array',
        ]);

        try {
            $analysis = $this->deepSeekService->identifyStrengthsWeaknesses(
                $validated['competency_scores']
            );

            return response()->json([
                'success' => true,
                'data' => $analysis,
            ]);
        } catch (\Exception $e) {
            Log::error('Strengths/weaknesses identification failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to identify strengths and weaknesses: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate assessment report narrative
     */
    public function generateReportNarrative(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'participant_name' => 'required|string',
            'assessment_title' => 'required|string',
            'overall_score' => 'required|numeric',
            'competency_scores' => 'required|array',
            'strengths' => 'nullable|array',
            'weaknesses' => 'nullable|array',
        ]);

        try {
            $narrative = $this->deepSeekService->generateReportNarrative($validated);

            return response()->json([
                'success' => true,
                'data' => [
                    'narrative' => $narrative,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Report narrative generation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate development recommendations
     */
    public function generateRecommendations(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'assessment_results' => 'nullable|array',
            'strengths' => 'nullable|array',
            'development_areas' => 'nullable|array',
            'role_context' => 'nullable|string',
        ]);

        try {
            $recommendations = $this->deepSeekService->generateRecommendations($validated);

            return response()->json([
                'success' => true,
                'data' => $recommendations,
            ]);
        } catch (\Exception $e) {
            Log::error('Recommendation generation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate recommendations: ' . $e->getMessage(),
            ], 500);
        }
    }
}
