<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AIService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected int $timeout;
    protected string $provider;

    public function __construct()
    {
        $this->provider = config('services.ai.provider', 'gemini');
        $this->apiKey = config("services.ai.{$this->provider}.api_key");
        $this->baseUrl = config("services.ai.{$this->provider}.base_url");
        $this->model = config("services.ai.{$this->provider}.model");
        $this->timeout = config("services.ai.{$this->provider}.timeout", 30);

        if (empty($this->apiKey)) {
            throw new Exception("AI API key is not configured. Please set AI_{$this->provider}_API_KEY in your .env file.");
        }
    }

    /**
     * Make a chat completion request to AI API
     */
    protected function chat(array $messages, array $options = []): array
    {
        try {
            switch ($this->provider) {
                case 'gemini':
                    return $this->chatGemini($messages, $options);
                case 'openai':
                    return $this->chatOpenAI($messages, $options);
                case 'deepseek':
                    return $this->chatDeepSeek($messages, $options);
                default:
                    throw new Exception("Unsupported AI provider: {$this->provider}");
            }
        } catch (Exception $e) {
            Log::error('AI API Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Chat with Google Gemini (FREE!)
     */
    protected function chatGemini(array $messages, array $options = []): array
    {
        // Convert messages to Gemini format
        $contents = [];
        $systemInstruction = '';

        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $systemInstruction = $message['content'];
            } else {
                $contents[] = [
                    'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $message['content']]]
                ];
            }
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'maxOutputTokens' => $options['max_tokens'] ?? 2000,
            ]
        ];

        if ($systemInstruction) {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $systemInstruction]]
            ];
        }

        // Add JSON mode if requested
        if (isset($options['response_format']) && $options['response_format']['type'] === 'json_object') {
            $payload['generationConfig']['responseMimeType'] = 'application/json';
        }

        $url = $this->baseUrl . '/models/' . $this->model . ':generateContent?key=' . $this->apiKey;

        $response = Http::timeout($this->timeout)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

        if (!$response->successful()) {
            throw new Exception('Gemini API request failed: ' . $response->body());
        }

        $data = $response->json();

        // Convert Gemini response to standard format
        return [
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => $data['candidates'][0]['content']['parts'][0]['text'] ?? ''
                    ]
                ]
            ]
        ];
    }

    /**
     * Chat with OpenAI
     */
    protected function chatOpenAI(array $messages, array $options = []): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($this->baseUrl . '/chat/completions', array_merge([
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ], $options));

        if (!$response->successful()) {
            throw new Exception('OpenAI API request failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Chat with DeepSeek
     */
    protected function chatDeepSeek(array $messages, array $options = []): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($this->baseUrl . '/chat/completions', array_merge([
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ], $options));

        if (!$response->successful()) {
            throw new Exception('DeepSeek API request failed: ' . $response->body());
        }

        return $response->json();
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

        $response = $this->chat($messages, [
            'temperature' => 0.3,
            'response_format' => ['type' => 'json_object']
        ]);

        return json_decode($response['choices'][0]['message']['content'], true);
    }

    /**
     * Analyze qualitative responses (open-ended text, assessor notes)
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

        $response = $this->chat($messages, [
            'temperature' => 0.5,
            'response_format' => ['type' => 'json_object']
        ]);

        return json_decode($response['choices'][0]['message']['content'], true);
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

        $response = $this->chat($messages, [
            'temperature' => 0.6,
            'max_tokens' => 3000
        ]);

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
                'content' => 'You are an expert talent development consultant. Based on assessment results, provide specific, actionable development recommendations including training activities, coaching focus areas, and developmental assignments. Return structured JSON data.'
            ],
            [
                'role' => 'user',
                'content' => $this->buildRecommendationPrompt($candidateProfile)
            ]
        ];

        $response = $this->chat($messages, [
            'temperature' => 0.7,
            'response_format' => ['type' => 'json_object']
        ]);

        return json_decode($response['choices'][0]['message']['content'], true);
    }

    /**
     * Identify strengths and weaknesses from assessment data
     */
    public function identifyStrengthsWeaknesses(array $competencyScores): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an expert in competency assessment analysis. Identify key strengths and development areas based on competency scores and behavioral evidence. Return structured JSON data.'
            ],
            [
                'role' => 'user',
                'content' => "Analyze these competency scores and identify the top 3-5 strengths and top 3-5 development areas:\n\n" .
                    json_encode($competencyScores, JSON_PRETTY_PRINT) .
                    "\n\nProvide concise, specific descriptions for each strength and development area.\n\nReturn as JSON with keys: strengths (array of objects with competency, score, description), development_areas (array of objects with competency, score, description)"
            ]
        ];

        $response = $this->chat($messages, [
            'temperature' => 0.4,
            'response_format' => ['type' => 'json_object']
        ]);

        return json_decode($response['choices'][0]['message']['content'], true);
    }

    /**
     * Build scoring prompt from assessment data
     */
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

        $prompt .= "\nProvide:\n";
        $prompt .= "1. Individual competency scores (0-100 scale)\n";
        $prompt .= "2. Overall assessment score\n";
        $prompt .= "3. Rationale for each score\n";
        $prompt .= "4. Key evidence supporting the scores\n";
        $prompt .= "5. Behavioral observations\n\n";
        $prompt .= "Return response as JSON with structure: {competency_scores: [], overall_score: number, rationale: {}, evidence: {}, observations: []}";

        return $prompt;
    }

    /**
     * Build report generation prompt
     */
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
            $prompt .= "\n";
        }

        if (!empty($reportData['strengths'])) {
            $prompt .= "Identified Strengths:\n" . implode("\n", $reportData['strengths']) . "\n\n";
        }

        if (!empty($reportData['weaknesses'])) {
            $prompt .= "Development Areas:\n" . implode("\n", $reportData['weaknesses']) . "\n\n";
        }

        $prompt .= "Create a professional narrative report with the following sections:\n";
        $prompt .= "1. Executive Summary\n";
        $prompt .= "2. Overall Performance Assessment\n";
        $prompt .= "3. Key Strengths\n";
        $prompt .= "4. Development Opportunities\n";
        $prompt .= "5. Recommendations for Next Steps\n\n";
        $prompt .= "Tone: Professional, constructive, balanced\n";
        $prompt .= "Length: 800-1200 words\n";

        return $prompt;
    }

    /**
     * Build recommendation generation prompt
     */
    protected function buildRecommendationPrompt(array $candidateProfile): string
    {
        $prompt = "Generate personalized development recommendations for:\n\n";

        if (!empty($candidateProfile['assessment_results'])) {
            $prompt .= "Assessment Results:\n" . json_encode($candidateProfile['assessment_results'], JSON_PRETTY_PRINT) . "\n\n";
        }

        if (!empty($candidateProfile['strengths'])) {
            $prompt .= "Strengths:\n" . implode("\n", $candidateProfile['strengths']) . "\n\n";
        }

        if (!empty($candidateProfile['development_areas'])) {
            $prompt .= "Development Areas:\n" . implode("\n", $candidateProfile['development_areas']) . "\n\n";
        }

        if (!empty($candidateProfile['role_context'])) {
            $prompt .= "Role/Context: " . $candidateProfile['role_context'] . "\n\n";
        }

        $prompt .= "Provide:\n";
        $prompt .= "1. 3-5 specific development recommendations\n";
        $prompt .= "2. Training programs or courses to consider\n";
        $prompt .= "3. On-the-job development activities\n";
        $prompt .= "4. Coaching focus areas\n";
        $prompt .= "5. Resources and tools\n\n";
        $prompt .= "Return as JSON with structure: {recommendations: [], training: [], activities: [], coaching_focus: [], resources: []}";

        return $prompt;
    }
}
