<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Assessment;
use App\Models\Test;
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
            ->orderByRaw('COALESCE(full_name, username)')
            ->get(['id', 'full_name', 'username', 'department', 'rank', 'status']);

        $assessments = Assessment::with('translations')
            ->orderBy('created_at', 'desc')
            ->get();

        $tests = Test::query()
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title', 'test_type', 'created_at']);

        return view('ai-assistant.index', compact('participants', 'assessments', 'tests'));
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
            'test_id' => 'nullable|exists:tests,id',
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
                    'message' => __('Test-Based Analysis requires exactly one participant'),
                ], 422);
            }
            if (empty($validated['assessment_id']) && empty($validated['test_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => __('Test-Based Analysis requires a specific assessment or test'),
                ], 422);
            }
        }

        if ($mode === 'mission_based') {
            if (count($participantIds) !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => __('Mission Fit requires exactly one participant'),
                ], 422);
            }
            if (empty($validated['mission_details'])) {
                return response()->json([
                    'success' => false,
                    'message' => __('Mission Fit requires mission/role details'),
                ], 422);
            }
        }

        if ($mode === 'overall' && count($participantIds) !== 1) {
            return response()->json([
                'success' => false,
                'message' => __('Overall Analysis requires exactly one participant'),
            ], 422);
        }

        if ($mode === 'comparison') {
            if (count($participantIds) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => __('Comparison requires at least 2 participants'),
                ], 422);
            }
            if (empty($validated['mission_details'])) {
                return response()->json([
                    'success' => false,
                    'message' => __('Comparison requires mission/role details'),
                ], 422);
            }
        }

        try {
            $mode = $validated['mode'];
            $participantIds = $validated['participant_ids'];
            $assessmentId = $validated['assessment_id'] ?? null;
            $testId = $validated['test_id'] ?? null;
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
            $context = $this->buildContext($mode, $participants, $assessmentId, $testId, $missionDetails);

            // Generate AI response
            $prompt = $this->buildPrompt($mode, $context, $question);
            $locale = app()->getLocale();
            $systemInstruction = "You are an experienced organizational psychologist who understands each applicant through their assessment data. "
                . "Provide clear, concise, and human insights. Use short paragraphs and simple bullet points. "
                . "Be warm, empathetic, and avoid repetition. "
                . "CRITICAL: Use ONLY plain text. Never use markdown symbols like **, ###, ####, or other formatting characters. ";

            if ($locale === 'ar') {
                $systemInstruction .= "\n\nLANGUAGE REQUIREMENT - THIS IS MANDATORY:\n"
                    . "You MUST write your ENTIRE response in Arabic (العربية) ONLY.\n"
                    . "- Use Modern Standard Arabic (الفصحى) throughout.\n"
                    . "- ABSOLUTELY NO Chinese characters (中文), Japanese, Korean, or any non-Arabic script.\n"
                    . "- ABSOLUTELY NO mixing of languages.\n"
                    . "- Even if the input data contains English names or terms, translate or transliterate them into Arabic.\n"
                    . "- Your response must be 100% in Arabic letters and Arabic numerals only.\n"
                    . "- This is a strict requirement with no exceptions.";
            } else {
                $systemInstruction .= "\n\nLANGUAGE REQUIREMENT: Respond entirely in English. "
                    . "Do not use Chinese, Japanese, Korean, or Arabic characters unless specifically requested.";
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
    protected function buildContext(string $mode, $participants, $assessmentId, $testId, $missionDetails): array
    {
        $context = [
            'participants' => [],
            'assessments' => [],
            'tests' => [],
            'mission' => $missionDetails,
        ];

        foreach ($participants as $participant) {
            $participantData = [
                'name' => $participant->full_name,
                'username' => $participant->username,
                'department' => $participant->department,
                'rank' => $participant->rank,
                'assessments' => [],
                'tests' => [],
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

            if (\Illuminate\Support\Facades\Schema::hasTable('test_assignments') && \Illuminate\Support\Facades\Schema::hasTable('test_results') && \Illuminate\Support\Facades\Schema::hasTable('tests')) {
                $testsQuery = \Illuminate\Support\Facades\DB::table('test_assignments as ta')
                    ->join('tests as t', 't.id', '=', 'ta.test_id')
                    ->leftJoin('test_results as tr', 'tr.test_assignment_id', '=', 'ta.id')
                    ->where('ta.participant_id', $participant->id);

                if ($testId) {
                    $testsQuery->where('t.id', $testId);
                }

                $testRows = $testsQuery
                    ->select(
                        't.id',
                        't.title',
                        't.test_type',
                        'tr.percentage',
                        'tr.completed_at'
                    )
                    ->orderByDesc('tr.completed_at')
                    ->limit(5)
                    ->get();

                foreach ($testRows as $testRow) {
                    $participantData['tests'][] = [
                        'id' => $testRow->id,
                        'title' => $testRow->title,
                        'type' => $testRow->test_type,
                        'percentage' => $testRow->percentage,
                        'date' => $testRow->completed_at ? \Illuminate\Support\Carbon::parse($testRow->completed_at)->format('Y-m-d') : null,
                    ];
                }
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

                if (!empty($context['participants'][0]['tests'])) {
                    $prompt .= "Related tests:\n";
                    foreach ($context['participants'][0]['tests'] as $test) {
                        $score = $test['percentage'] !== null ? $test['percentage'] . '%' : 'N/A';
                        $date = $test['date'] ?? 'N/A';
                        $prompt .= "- " . $test['title'] . " (" . $test['type'] . "), " . $score . " on " . $date . "\n";
                    }
                } elseif (!empty($context['participants'][0]['assessments'])) {
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
            $prompt .= "\n\n=== تعليمات اللغة (إلزامية) ===\n"
                . "اكتب ردك بالكامل باللغة العربية الفصحى فقط.\n"
                . "ممنوع منعاً باتاً استخدام أي حروف صينية أو يابانية أو أي لغة أخرى غير العربية.\n"
                . "استخدم أسلوباً إنسانياً واضحاً وبسيطاً.";
        }

        return $prompt;
    }
}
