<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Assessment;
use App\Services\LocalAIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AIAssistantController extends Controller
{
    protected LocalAIService $localAI;

    public function __construct(LocalAIService $localAI)
    {
        $this->localAI = $localAI;
    }

    /**
     * Show AI Assistant page
     */
    public function index()
    {
        $participants = User::where('role', 'participant')
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'username', 'department', 'rank']);

        $assessments = Assessment::with('translations')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('ai-assistant.index', compact('participants', 'assessments'));
    }

    /**
     * Handle AI chat request
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mode' => 'required|in:test_based,mission_based,overall,comparison',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'exists:users,id',
            'assessment_id' => 'nullable|exists:assessments,id',
            'mission_details' => 'nullable|string',
            'question' => 'nullable|string',
        ]);

        $mode = $validated['mode'];
        $participantIds = $validated['participant_ids'];

        // Mode-specific validation
        if ($mode === 'test_based') {
            if (count($participantIds) !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Test-Based Analysis requires exactly one participant',
                ], 422);
            }
            if (empty($validated['assessment_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Test-Based Analysis requires a specific assessment',
                ], 422);
            }
        }

        if ($mode === 'mission_based') {
            if (count($participantIds) !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mission Fit requires exactly one participant',
                ], 422);
            }
            if (empty($validated['mission_details'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mission Fit requires mission/role details',
                ], 422);
            }
        }

        if ($mode === 'overall' && count($participantIds) !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Overall Analysis requires exactly one participant',
            ], 422);
        }

        if ($mode === 'comparison') {
            if (count($participantIds) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comparison requires at least 2 participants',
                ], 422);
            }
            if (empty($validated['mission_details'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comparison requires mission/role details',
                ], 422);
            }
        }

        try {
            $mode = $validated['mode'];
            $participantIds = $validated['participant_ids'];
            $assessmentId = $validated['assessment_id'] ?? null;
            $missionDetails = $validated['mission_details'] ?? null;
            $question = $validated['question'] ?? null;

            // Get participant data
            $participants = User::whereIn('id', $participantIds)
                ->with(['assessments' => function ($query) use ($assessmentId) {
                    if ($assessmentId) {
                        $query->where('assessments.id', $assessmentId);
                    }
                }])
                ->get();

            // Build context based on mode
            $context = $this->buildContext($mode, $participants, $assessmentId, $missionDetails);

            // Generate AI response
            $prompt = $this->buildPrompt($mode, $context, $question);
            $response = $this->localAI->chat([
                [
                    'role' => 'system',
                    'content' => 'You are an expert HR psychometric assessment analyst. Provide clear, concise, and well-organized insights. Use bullet points and short paragraphs. Be direct and avoid repetition. Format your response with clear sections using ALL-CAPS headers. CRITICAL: Use ONLY plain text. Never use markdown symbols like **, ###, ####, or other formatting characters.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ], ['temperature' => 0.5, 'max_tokens' => 500]);

            $aiResponse = $response['choices'][0]['message']['content'];

            return response()->json([
                'success' => true,
                'response' => $aiResponse,
                'mode' => $mode,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build context based on mode
     */
    protected function buildContext(string $mode, $participants, $assessmentId, $missionDetails): array
    {
        $context = [
            'participants' => [],
            'assessments' => [],
            'mission' => $missionDetails,
        ];

        foreach ($participants as $participant) {
            $participantData = [
                'name' => $participant->full_name,
                'username' => $participant->username,
                'department' => $participant->department,
                'rank' => $participant->rank,
                'assessments' => [],
            ];

            // Add assessment data if available
            // Note: This is a simplified version. In production, you'd fetch actual assessment responses
            foreach ($participant->assessments as $assessment) {
                $participantData['assessments'][] = [
                    'id' => $assessment->id,
                    'type' => $assessment->type,
                    'date' => $assessment->created_at->format('Y-m-d'),
                    // In production: Add actual scores, responses, competencies here
                ];
            }

            $context['participants'][] = $participantData;
        }

        return $context;
    }

    /**
     * Build prompt based on mode
     */
    protected function buildPrompt(string $mode, array $context, $question): string
    {
        $prompt = '';

        switch ($mode) {
            case 'test_based':
                $prompt = "Analyze assessment performance.\n\n";
                $prompt .= "PARTICIPANT: " . $context['participants'][0]['name'] . " - " . ($context['participants'][0]['rank'] ?? 'N/A') . " (" . ($context['participants'][0]['department'] ?? 'N/A') . ")\n\n";

                if (!empty($context['participants'][0]['assessments'])) {
                    $prompt .= "ASSESSMENT:\n";
                    foreach ($context['participants'][0]['assessments'] as $assessment) {
                        $prompt .= "- " . ucfirst($assessment['type']) . " (" . $assessment['date'] . ")\n";
                    }
                }

                if ($question) {
                    $prompt .= "\nFOCUS: " . $question . "\n";
                }

                $prompt .= "\nProvide:\n1. PERFORMANCE SUMMARY (2-3 sentences)\n2. KEY STRENGTHS (3 bullet points)\n3. AREAS FOR IMPROVEMENT (3 bullet points)\n4. RECOMMENDATION (1-2 sentences)";
                break;

            case 'mission_based':
                $prompt = "Evaluate mission fit.\n\n";
                $prompt .= "CANDIDATE: " . $context['participants'][0]['name'] . " - " . ($context['participants'][0]['rank'] ?? 'N/A') . " (" . ($context['participants'][0]['department'] ?? 'N/A') . ")\n";
                $prompt .= "Assessments Completed: " . count($context['participants'][0]['assessments']) . "\n\n";

                if ($context['mission']) {
                    $prompt .= "MISSION:\n" . $context['mission'] . "\n\n";
                }

                if ($question) {
                    $prompt .= "FOCUS: " . $question . "\n\n";
                }

                $prompt .= "Provide:\n1. FIT ASSESSMENT (Good/Fair/Poor with 1-2 sentence reason)\n2. RELEVANT STRENGTHS (3 bullet points)\n3. GAPS TO ADDRESS (3 bullet points)\n4. RECOMMENDATION (1-2 sentences)";
                break;

            case 'overall':
                $prompt = "Provide overall candidate analysis.\n\n";
                $prompt .= "CANDIDATE: " . $context['participants'][0]['name'] . " - " . ($context['participants'][0]['rank'] ?? 'N/A') . " (" . ($context['participants'][0]['department'] ?? 'N/A') . ")\n";
                $prompt .= "Total Assessments: " . count($context['participants'][0]['assessments']) . "\n\n";

                if ($question) {
                    $prompt .= "FOCUS: " . $question . "\n\n";
                }

                $prompt .= "Provide:\n1. OVERALL PROFILE (2-3 sentences)\n2. TOP COMPETENCIES (4 bullet points)\n3. DEVELOPMENT NEEDS (3 bullet points)\n4. CAREER POTENTIAL (1-2 sentences)";
                break;

            case 'comparison':
                $prompt = "Compare candidates for a leadership mission.\n\n";

                $prompt .= "CANDIDATES:\n";
                foreach ($context['participants'] as $i => $participant) {
                    $prompt .= ($i + 1) . ". " . $participant['name'] . " - " . ($participant['rank'] ?? 'N/A') . " (" . ($participant['department'] ?? 'N/A') . ")\n";
                }

                if ($context['mission']) {
                    $prompt .= "\nMISSION REQUIREMENTS:\n" . $context['mission'] . "\n";
                }

                if ($question) {
                    $prompt .= "\nFOCUS: " . $question . "\n";
                }

                $prompt .= "\nProvide (use plain text, NO markdown symbols like **, ###, or ####):\n";
                $prompt .= "1. RANKING\n";
                $prompt .= "   1st: [Candidate Name] - [One sentence reason]\n";
                $prompt .= "   2nd: [Candidate Name] - [One sentence reason]\n\n";
                $prompt .= "2. KEY STRENGTHS\n";
                $prompt .= "   - [Strength 1]\n";
                $prompt .= "   - [Strength 2]\n";
                $prompt .= "   - [Strength 3]\n\n";
                $prompt .= "3. RECOMMENDATION\n";
                $prompt .= "   [2-3 sentences]\n\n";
                $prompt .= "IMPORTANT: Use ONLY plain text. Do NOT use markdown formatting symbols.";
                break;
        }

        return $prompt;
    }
}
