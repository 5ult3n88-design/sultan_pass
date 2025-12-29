<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Local AI Service for running AI models locally (offline)
 * Supports: Ollama, LM Studio, or any OpenAI-compatible local server
 */
class LocalAIService
{
    protected string $baseUrl;
    protected string $model;
    protected int $timeout;

    public function __construct()
    {
        // Default to Ollama running locally
        $this->baseUrl = config('services.local_ai.base_url', 'http://localhost:11434');
        $this->model = config('services.local_ai.model', 'deepseek-r1:7b');
        $this->timeout = config('services.local_ai.timeout', 120); // Longer timeout for local inference
    }

    /**
     * Make a chat completion request to local AI
     */
    public function chat(array $messages, array $options = []): array
    {
        try {
            // Build prompt from messages
            $prompt = $this->buildPromptFromMessages($messages);

            $payload = [
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false,
                'options' => [
                    'temperature' => $options['temperature'] ?? 0.7,
                    'num_predict' => $options['max_tokens'] ?? 2000,
                ]
            ];

            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/api/generate', $payload);

            if (!$response->successful()) {
                throw new Exception('Local AI request failed: ' . $response->body());
            }

            $data = $response->json();

            // Convert to standard format
            return [
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => $data['response'] ?? ''
                        ]
                    ]
                ]
            ];
        } catch (Exception $e) {
            Log::error('Local AI Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build a single prompt from messages array
     */
    protected function buildPromptFromMessages(array $messages): string
    {
        $prompt = '';

        foreach ($messages as $message) {
            $role = $message['role'];
            $content = $message['content'];

            if ($role === 'system') {
                $prompt .= "System: {$content}\n\n";
            } elseif ($role === 'user') {
                $prompt .= "User: {$content}\n\n";
            } elseif ($role === 'assistant') {
                $prompt .= "Assistant: {$content}\n\n";
            }
        }

        $prompt .= "Assistant: ";

        return $prompt;
    }

    /**
     * Score an assessment based on responses and rubric
     */
    public function scoreAssessment(array $assessmentData): array
    {
        $prompt = $this->buildScoringPrompt($assessmentData);

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an expert psychometric assessor. Analyze assessment responses and provide detailed, objective scoring based on competency frameworks. Return structured JSON data with scores, rationale, and evidence.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        $response = $this->chat($messages, ['temperature' => 0.3]);

        $content = $response['choices'][0]['message']['content'];

        // Extract JSON from response
        return $this->extractJSON($content);
    }

    /**
     * Analyze qualitative responses
     */
    public function analyzeQualitativeResponse(string $response, string $competencyContext = ''): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an expert in qualitative analysis for psychometric assessments. Extract key themes, behavioral indicators, strengths, and development areas from text responses. Return structured JSON data.'
            ],
            [
                'role' => 'user',
                'content' => "Analyze this qualitative response" .
                    ($competencyContext ? " in the context of '{$competencyContext}'" : "") .
                    ":\n\n{$response}\n\nProvide:\n1. Key themes\n2. Behavioral indicators observed\n3. Strengths demonstrated\n4. Development areas identified\n5. Overall assessment\n\nReturn as JSON with keys: key_themes (array), behavioral_indicators (array), strengths (array), development_areas (array), overall_assessment (string)"
            ]
        ];

        $response = $this->chat($messages, ['temperature' => 0.5]);
        $content = $response['choices'][0]['message']['content'];

        return $this->extractJSON($content);
    }

    /**
     * Generate assessment report narrative
     */
    public function generateReportNarrative(array $reportData): string
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an expert assessment report writer. Create clear, professional, constructive narratives that summarize candidate performance, highlight strengths, identify development areas, and provide actionable recommendations.'
            ],
            [
                'role' => 'user',
                'content' => $this->buildReportPrompt($reportData)
            ]
        ];

        $response = $this->chat($messages, ['temperature' => 0.6, 'max_tokens' => 3000]);

        return $response['choices'][0]['message']['content'];
    }

    /**
     * Generate personalized development recommendations
     */
    public function generateRecommendations(array $candidateProfile): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an expert talent development consultant. Based on assessment results, provide specific, actionable development recommendations. Return structured JSON data.'
            ],
            [
                'role' => 'user',
                'content' => $this->buildRecommendationPrompt($candidateProfile)
            ]
        ];

        $response = $this->chat($messages, ['temperature' => 0.7]);
        $content = $response['choices'][0]['message']['content'];

        return $this->extractJSON($content);
    }

    /**
     * Identify strengths and weaknesses
     */
    public function identifyStrengthsWeaknesses(array $competencyScores): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an expert in competency assessment analysis. Identify key strengths and development areas based on competency scores. Return structured JSON data.'
            ],
            [
                'role' => 'user',
                'content' => "Analyze these competency scores and identify the top 3-5 strengths and top 3-5 development areas:\n\n" .
                    json_encode($competencyScores, JSON_PRETTY_PRINT) .
                    "\n\nReturn as JSON with keys: strengths (array of objects with competency, score, description), development_areas (array of objects with competency, score, description)"
            ]
        ];

        $response = $this->chat($messages, ['temperature' => 0.4]);
        $content = $response['choices'][0]['message']['content'];

        return $this->extractJSON($content);
    }

    /**
     * Extract JSON from text that may contain markdown or extra text
     */
    protected function extractJSON(string $text): array
    {
        // Try to find JSON between curly braces
        if (preg_match('/\{.*\}/s', $text, $matches)) {
            try {
                return json_decode($matches[0], true) ?? [];
            } catch (Exception $e) {
                // Fall through to try parsing the whole text
            }
        }

        // Try parsing the whole text as JSON
        try {
            return json_decode($text, true) ?? [];
        } catch (Exception $e) {
            Log::warning('Failed to extract JSON from response: ' . $text);
            return [];
        }
    }

    // Prompt building methods (same as before)
    protected function buildScoringPrompt(array $assessmentData): string
    {
        $prompt = "Please score the following assessment:\n\n";
        $prompt .= "Assessment Title: " . ($assessmentData['title'] ?? 'N/A') . "\n";
        $prompt .= "Assessment Type: " . ($assessmentData['type'] ?? 'N/A') . "\n\n";
        $prompt .= "Competencies to Assess:\n";
        foreach ($assessmentData['competencies'] ?? [] as $competency) {
            $prompt .= "- " . $competency['name'] . " (Weight: " . ($competency['weight'] ?? 'N/A') . ")\n";
            if (!empty($competency['description'])) {
                $prompt .= "  Description: " . $competency['description'] . "\n";
            }
        }
        $prompt .= "\nCandidate Responses:\n";
        foreach ($assessmentData['responses'] ?? [] as $response) {
            $prompt .= "\nQuestion: " . ($response['question'] ?? 'N/A') . "\n";
            $prompt .= "Response: " . ($response['answer'] ?? 'N/A') . "\n";
        }
        $prompt .= "\nReturn response as JSON with structure: {competency_scores: [], overall_score: number, rationale: {}, evidence: {}, observations: []}";
        return $prompt;
    }

    protected function buildReportPrompt(array $reportData): string
    {
        $prompt = "Generate a comprehensive assessment report for:\n\n";
        $prompt .= "Candidate: " . ($reportData['participant_name'] ?? 'N/A') . "\n";
        $prompt .= "Assessment: " . ($reportData['assessment_title'] ?? 'N/A') . "\n";
        $prompt .= "Overall Score: " . ($reportData['overall_score'] ?? 'N/A') . "\n\n";
        if (!empty($reportData['competency_scores'])) {
            $prompt .= "Competency Scores:\n";
            foreach ($reportData['competency_scores'] as $competency => $score) {
                $prompt .= "- {$competency}: {$score}\n";
            }
        }
        $prompt .= "\nCreate a professional narrative report (800-1200 words).";
        return $prompt;
    }

    protected function buildRecommendationPrompt(array $candidateProfile): string
    {
        $prompt = "Generate personalized development recommendations.\n\n";
        if (!empty($candidateProfile['strengths'])) {
            $prompt .= "Strengths:\n" . implode("\n", $candidateProfile['strengths']) . "\n\n";
        }
        if (!empty($candidateProfile['development_areas'])) {
            $prompt .= "Development Areas:\n" . implode("\n", $candidateProfile['development_areas']) . "\n\n";
        }
        $prompt .= "Return as JSON with: {recommendations: [], training: [], activities: [], coaching_focus: [], resources: []}";
        return $prompt;
    }
}
