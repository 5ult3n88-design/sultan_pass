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
            $locale = app()->getLocale();
            $systemInstruction = "You are an experienced organizational psychologist who understands each applicant through their assessment data. "
                . "Provide clear, concise, and human insights. Use short paragraphs and simple bullet points. "
                . "Be warm, empathetic, and avoid repetition. "
                . "CRITICAL: Use ONLY plain text. Never use markdown symbols like **, ###, ####, or other formatting characters. ";

            if ($locale === 'ar') {
                $systemInstruction .= "The user interface language is Arabic. You MUST write your entire answer in Modern Standard Arabic only. "
                    . "Do NOT use any Chinese characters or Chinese words at all. "
                    . "If the input contains English or Chinese, understand it internally but rephrase everything in Arabic.";
            } else {
                $systemInstruction .= "Respond in the interface language '{$locale}' when possible and avoid using Chinese characters "
                    . "unless the user explicitly asks for a Chinese translation.";
            }

            $response = $this->localAI->chat([
                [
                    'role' => 'system',
                    'content' => $systemInstruction,
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
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
        $locale = app()->getLocale();
        $isArabic = $locale === 'ar';

        switch ($mode) {
            case 'test_based':
                $prompt = "CONTEXT (for you only, do not repeat mechanically):\n";
                $prompt .= "Participant: " . $context['participants'][0]['name'] . " - "
                    . ($context['participants'][0]['rank'] ?? 'N/A')
                    . " (" . ($context['participants'][0]['department'] ?? 'N/A') . ")\n";

                if (!empty($context['participants'][0]['assessments'])) {
                    $prompt .= "Related assessments:\n";
                    foreach ($context['participants'][0]['assessments'] as $assessment) {
                        $prompt .= "- " . ucfirst($assessment['type']) . " on " . $assessment['date'] . "\n";
                    }
                }

                $prompt .= "\nUSER QUESTION (answer this kindly and directly):\n";
                $prompt .= $question ?: ($isArabic
                    ? "أعطني انطباعاً نفسياً عاماً عن أداء هذا المشارك في هذا الاختبار."
                    : "Give me a brief psychological view of this participant's performance in the test.");

                $prompt .= "\n\nUse the assessments as background only. Speak like a psychologist talking to a colleague: "
                    . "1–2 short paragraphs and a few concrete observations or suggestions.";
                break;

            case 'mission_based':
                $prompt = "CONTEXT (for you only, do not repeat mechanically):\n";
                $prompt .= "Candidate: " . $context['participants'][0]['name'] . " - "
                    . ($context['participants'][0]['rank'] ?? 'N/A')
                    . " (" . ($context['participants'][0]['department'] ?? 'N/A') . ")\n";
                $prompt .= "Assessments completed: " . count($context['participants'][0]['assessments']) . "\n\n";

                if ($context['mission']) {
                    $prompt .= "Mission / role description:\n" . $context['mission'] . "\n\n";
                }

                $prompt .= "USER QUESTION (answer this as a psychologist thinking about mission fit):\n";
                $prompt .= $question ?: ($isArabic
                    ? "هل هذا المشارك مناسب لهذه المهمة من ناحية السلوك والكفاءة النفسية؟"
                    : "Is this participant a good fit for this mission from a behavioral and psychological perspective?");

                $prompt .= "\n\nUse the assessments only as background. Give a short, human explanation of fit, "
                    . "with 2–3 key strengths and 2–3 concerns in natural language.";
                break;

            case 'overall':
                $prompt = "CONTEXT (for you only, do not repeat mechanically):\n";
                $prompt .= "Candidate: " . $context['participants'][0]['name'] . " - "
                    . ($context['participants'][0]['rank'] ?? 'N/A')
                    . " (" . ($context['participants'][0]['department'] ?? 'N/A') . ")\n";
                $prompt .= "Total assessments: " . count($context['participants'][0]['assessments']) . "\n\n";

                $prompt .= "USER QUESTION (overall psychological reflection):\n";
                $prompt .= $question ?: ($isArabic
                    ? "أعطني صورة نفسية عامة عن هذا المشارك، نقاط قوته والجوانب التي تحتاج إلى دعم."
                    : "Give me an overall psychological view of this participant, including key strengths and areas that need support.");

                $prompt .= "\n\nReply in a warm, human tone, as if you know the candidate well from their assessments. "
                    . "1–2 short paragraphs are enough, plus a few practical suggestions.";
                break;

            case 'comparison':
                $prompt = "CONTEXT (for you only, do not repeat mechanically):\n";
                $prompt .= "We are comparing several candidates for a mission.\n";
                $prompt .= "Candidates:\n";
                foreach ($context['participants'] as $i => $participant) {
                    $prompt .= ($i + 1) . ". " . $participant['name'] . " - "
                        . ($participant['rank'] ?? 'N/A')
                        . " (" . ($participant['department'] ?? 'N/A') . ")\n";
                }

                if ($context['mission']) {
                    $prompt .= "\nMission requirements:\n" . $context['mission'] . "\n";
                }

                if ($question) {
                    $prompt .= "\nUSER QUESTION (focus of the comparison):\n" . $question . "\n";
                }

                $prompt .= "\nUse the assessment history only as background. Give a short, intuitive comparison like a psychologist advising a selection panel: "
                    . "who seems the best fit and why, and any major psychological risks or support needs.";
                break;
        }

        if ($isArabic) {
            $prompt .= "\n\nمهم: استخدم لغة عربية فصحى بسيطة وواضحة، وتحدّث بأسلوب إنساني موجّه للمستخدم، "
                . "وليس تقريراً رقمياً جامداً.";
        }

        return $prompt;
    }
}
