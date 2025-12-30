<?php

namespace App\Http\Controllers;

use App\Services\LocalAIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AIDemoController extends Controller
{
    protected LocalAIService $localAI;

    public function __construct()
    {
        $this->localAI = new LocalAIService();
    }

    /**
     * Show AI demo page
     */
    public function index()
    {
        return view('ai-demo');
    }

    /**
     * Analyze qualitative response
     */
    public function analyzeQualitative(Request $request): JsonResponse
    {
        $request->validate([
            'response' => 'required|string',
            'competency_context' => 'nullable|string',
        ]);

        try {
            $analysis = $this->localAI->analyzeQualitativeResponse(
                $request->input('response'),
                $request->input('competency_context', '')
            );

            return response()->json([
                'success' => true,
                'analysis' => $analysis,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Identify strengths and weaknesses
     */
    public function analyzeStrengths(Request $request): JsonResponse
    {
        $request->validate([
            'competency_scores' => 'required|array',
        ]);

        try {
            $analysis = $this->localAI->identifyStrengthsWeaknesses(
                $request->input('competency_scores')
            );

            return response()->json([
                'success' => true,
                'analysis' => $analysis,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
