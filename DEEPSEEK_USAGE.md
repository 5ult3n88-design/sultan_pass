# DeepSeek Integration Guide

This guide explains how to use DeepSeek AI in your eAssess psychometric assessment platform.

## Table of Contents

1. [Setup](#setup)
2. [Configuration](#configuration)
3. [Service Overview](#service-overview)
4. [API Endpoints](#api-endpoints)
5. [Usage Examples](#usage-examples)
6. [Integration Points](#integration-points)

## Setup

### 1. Install Dependencies

No additional packages needed! Laravel's built-in HTTP client (Guzzle) is used for API calls.

### 2. Get DeepSeek API Key

1. Sign up at [DeepSeek Platform](https://platform.deepseek.com/)
2. Generate an API key from your dashboard
3. Copy the API key for configuration

## Configuration

### Environment Variables

Update your `.env` file with your DeepSeek credentials:

```env
DEEPSEEK_API_KEY=your_actual_api_key_here
DEEPSEEK_BASE_URL=https://api.deepseek.com/v1
DEEPSEEK_MODEL=deepseek-chat
DEEPSEEK_TIMEOUT=30
```

The configuration is already added to `config/services.php`.

## Service Overview

### DeepSeekService Class

Location: `app/Services/DeepSeekService.php`

The service provides five main capabilities:

1. **scoreAssessment()** - Automatically score assessments based on responses
2. **analyzeQualitativeResponse()** - Analyze open-ended text responses
3. **generateReportNarrative()** - Create professional report narratives
4. **generateRecommendations()** - Generate development recommendations
5. **identifyStrengthsWeaknesses()** - Identify strengths and development areas

## API Endpoints

All endpoints are prefixed with `/api/ai/` and require authentication.

### 1. Score Assessment

**POST** `/api/ai/assessments/score`

Score an entire assessment using AI.

**Request Body:**
```json
{
  "assessment_id": 1,
  "participant_id": 5,
  "title": "Leadership Assessment",
  "type": "psychometric",
  "competencies": [
    {
      "name": "Leadership",
      "weight": 0.3,
      "description": "Ability to lead teams effectively"
    },
    {
      "name": "Communication",
      "weight": 0.25,
      "description": "Clear and effective communication"
    }
  ],
  "responses": [
    {
      "question": "Describe a time you led a team through a difficult challenge.",
      "answer": "In my previous role, I led a team of 8 engineers during a critical product launch..."
    },
    {
      "question": "How do you handle conflicts within your team?",
      "answer": "I believe in addressing conflicts early through open communication..."
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "competency_scores": [
      {
        "competency": "Leadership",
        "score": 85,
        "rationale": "Demonstrated strong leadership through...",
        "evidence": ["Led team of 8", "Critical product launch"]
      },
      {
        "competency": "Communication",
        "score": 78,
        "rationale": "Shows good communication practices...",
        "evidence": ["Open communication", "Early conflict resolution"]
      }
    ],
    "overall_score": 82,
    "observations": [
      "Strong team leadership capabilities",
      "Proactive approach to conflict resolution"
    ]
  },
  "message": "Assessment scored successfully"
}
```

### 2. Analyze Qualitative Response

**POST** `/api/ai/responses/analyze`

Analyze open-ended text responses or assessor notes.

**Request Body:**
```json
{
  "response": "The candidate demonstrated exceptional problem-solving skills during the case study. They approached the problem systematically, identified key stakeholders, and proposed a well-structured solution. However, they struggled with time management and appeared rushed towards the end.",
  "competency_context": "Problem Solving and Time Management"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "key_themes": [
      "Systematic problem-solving approach",
      "Stakeholder identification",
      "Time management challenges"
    ],
    "behavioral_indicators": [
      "Structured thinking",
      "Strategic planning",
      "Pressure under deadlines"
    ],
    "strengths": [
      "Exceptional problem-solving methodology",
      "Strong stakeholder awareness",
      "Well-structured solutions"
    ],
    "development_areas": [
      "Time management under pressure",
      "Pacing during assessments"
    ],
    "overall_assessment": "The candidate shows strong analytical and problem-solving capabilities with room for improvement in time management during high-pressure situations."
  }
}
```

### 3. Identify Strengths and Weaknesses

**POST** `/api/ai/assessments/strengths-weaknesses`

Identify top strengths and development areas from competency scores.

**Request Body:**
```json
{
  "competency_scores": {
    "Leadership": 85,
    "Communication": 78,
    "Strategic Thinking": 92,
    "Emotional Intelligence": 70,
    "Decision Making": 88,
    "Team Collaboration": 82,
    "Innovation": 65
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "strengths": [
      {
        "competency": "Strategic Thinking",
        "score": 92,
        "description": "Exceptional ability to think strategically and plan long-term"
      },
      {
        "competency": "Decision Making",
        "score": 88,
        "description": "Strong decision-making capabilities with data-driven approach"
      },
      {
        "competency": "Leadership",
        "score": 85,
        "description": "Solid leadership foundation with team motivation skills"
      }
    ],
    "development_areas": [
      {
        "competency": "Innovation",
        "score": 65,
        "description": "Opportunity to enhance creative thinking and innovation mindset"
      },
      {
        "competency": "Emotional Intelligence",
        "score": 70,
        "description": "Room for growth in understanding and managing emotions"
      },
      {
        "competency": "Communication",
        "score": 78,
        "description": "Could benefit from advanced communication techniques"
      }
    ]
  }
}
```

### 4. Generate Report Narrative

**POST** `/api/ai/reports/generate-narrative`

Generate a professional narrative for assessment reports.

**Request Body:**
```json
{
  "participant_name": "Sara Al Mansoori",
  "assessment_title": "Leadership Development Assessment",
  "overall_score": 82,
  "competency_scores": {
    "Leadership": 85,
    "Communication": 78,
    "Strategic Thinking": 92
  },
  "strengths": [
    "Strategic thinking",
    "Leadership capabilities",
    "Team motivation"
  ],
  "weaknesses": [
    "Time management",
    "Delegation skills"
  ]
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "narrative": "## Executive Summary\n\nSara Al Mansoori has completed the Leadership Development Assessment with an overall score of 82, demonstrating strong capabilities across multiple competencies...\n\n## Overall Performance Assessment\n\nSara's performance indicates a well-rounded leader with particular strength in strategic thinking...\n\n[Full narrative continues...]"
  }
}
```

### 5. Generate Recommendations

**POST** `/api/ai/recommendations/generate`

Generate personalized development recommendations.

**Request Body:**
```json
{
  "assessment_results": {
    "overall_score": 82,
    "competency_scores": {
      "Leadership": 85,
      "Innovation": 65
    }
  },
  "strengths": ["Strategic thinking", "Team leadership"],
  "development_areas": ["Innovation", "Time management"],
  "role_context": "Senior Manager transitioning to Director role"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "recommendations": [
      "Enroll in advanced innovation and design thinking programs",
      "Practice time management techniques with executive coaching",
      "Shadow current directors to understand strategic priorities"
    ],
    "training": [
      "Executive Leadership Program - Harvard Business School Online",
      "Design Thinking for Innovation - IDEO U",
      "Advanced Time Management for Leaders - LinkedIn Learning"
    ],
    "activities": [
      "Lead a cross-functional innovation project",
      "Mentor junior managers on strategic planning",
      "Participate in quarterly strategic planning sessions"
    ],
    "coaching_focus": [
      "Balancing strategic thinking with tactical execution",
      "Developing innovation mindset",
      "Improving time allocation for high-impact activities"
    ],
    "resources": [
      "Book: 'The Innovator's DNA' by Clayton Christensen",
      "Podcast: 'HBR IdeaCast' for leadership insights",
      "Tool: Time-blocking calendar method"
    ]
  }
}
```

## Usage Examples

### Example 1: Score Assessment After Submission

When a participant completes an assessment, automatically score it:

```php
use App\Services\DeepSeekService;

class SurveyController extends Controller
{
    protected DeepSeekService $deepSeekService;

    public function __construct(DeepSeekService $deepSeekService)
    {
        $this->deepSeekService = $deepSeekService;
    }

    public function submit(Request $request, int $assessmentId)
    {
        // Save participant responses to database
        // ...

        // Prepare data for AI scoring
        $assessmentData = [
            'title' => 'Leadership Assessment',
            'type' => 'psychometric',
            'competencies' => [
                ['name' => 'Leadership', 'weight' => 0.3, 'description' => '...'],
                ['name' => 'Communication', 'weight' => 0.25, 'description' => '...'],
            ],
            'responses' => $request->input('responses'),
        ];

        // Score the assessment using DeepSeek
        $scoringResult = $this->deepSeekService->scoreAssessment($assessmentData);

        // Save scores to database
        DB::table('assessment_participants')
            ->where('assessment_id', $assessmentId)
            ->where('user_id', auth()->id())
            ->update([
                'overall_score' => $scoringResult['overall_score'],
                'ai_scored_at' => now(),
            ]);

        // Save individual competency scores
        foreach ($scoringResult['competency_scores'] as $competencyScore) {
            DB::table('assessor_notes')->insert([
                'assessment_id' => $assessmentId,
                'participant_id' => auth()->id(),
                'competency_id' => $this->getCompetencyId($competencyScore['competency']),
                'score' => $competencyScore['score'],
                'notes' => $competencyScore['rationale'],
                'created_at' => now(),
            ]);
        }

        return redirect()->route('dashboard.participant')
            ->with('success', 'Assessment submitted and scored successfully!');
    }
}
```

### Example 2: Analyze Assessor Notes

When an assessor writes qualitative notes, analyze them:

```php
public function saveAssessorNotes(Request $request)
{
    $notes = $request->input('notes');
    $competency = $request->input('competency');

    // Analyze the qualitative notes
    $analysis = $this->deepSeekService->analyzeQualitativeResponse(
        $notes,
        $competency
    );

    // Save analysis results
    DB::table('assessor_notes')->insert([
        'assessment_id' => $request->input('assessment_id'),
        'participant_id' => $request->input('participant_id'),
        'competency_id' => $request->input('competency_id'),
        'notes' => $notes,
        'ai_analysis' => json_encode($analysis),
        'created_at' => now(),
    ]);

    return response()->json([
        'success' => true,
        'analysis' => $analysis,
    ]);
}
```

### Example 3: Generate Assessment Report

Generate a comprehensive report for a participant:

```php
public function generateReport(int $assessmentId, int $participantId)
{
    // Fetch assessment data
    $participant = DB::table('assessment_participants')
        ->where('assessment_id', $assessmentId)
        ->where('user_id', $participantId)
        ->first();

    // Fetch competency scores
    $competencyScores = DB::table('assessor_notes')
        ->join('competencies', 'assessor_notes.competency_id', '=', 'competencies.id')
        ->where('assessment_id', $assessmentId)
        ->where('participant_id', $participantId)
        ->pluck('score', 'competencies.name')
        ->toArray();

    // Identify strengths and weaknesses
    $analysis = $this->deepSeekService->identifyStrengthsWeaknesses($competencyScores);

    // Generate report narrative
    $reportData = [
        'participant_name' => $participant->name,
        'assessment_title' => $participant->assessment_title,
        'overall_score' => $participant->overall_score,
        'competency_scores' => $competencyScores,
        'strengths' => array_column($analysis['strengths'], 'description'),
        'weaknesses' => array_column($analysis['development_areas'], 'description'),
    ];

    $narrative = $this->deepSeekService->generateReportNarrative($reportData);

    // Generate recommendations
    $recommendations = $this->deepSeekService->generateRecommendations([
        'assessment_results' => ['overall_score' => $participant->overall_score],
        'strengths' => array_column($analysis['strengths'], 'description'),
        'development_areas' => array_column($analysis['development_areas'], 'description'),
        'role_context' => 'Manager seeking promotion',
    ]);

    // Save report
    DB::table('assessment_reports')->insert([
        'assessment_id' => $assessmentId,
        'participant_id' => $participantId,
        'overall_score' => $participant->overall_score,
        'strengths' => json_encode($analysis['strengths']),
        'weaknesses' => json_encode($analysis['development_areas']),
        'recommendations' => json_encode($recommendations),
        'narrative' => $narrative,
        'created_at' => now(),
    ]);

    return view('reports.show', [
        'narrative' => $narrative,
        'recommendations' => $recommendations,
        'analysis' => $analysis,
    ]);
}
```

### Example 4: Using JavaScript/AJAX

Call DeepSeek APIs from your frontend:

```javascript
// Score assessment via AJAX
async function scoreAssessment(assessmentData) {
    try {
        const response = await fetch('/api/ai/assessments/score', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Authorization': `Bearer ${localStorage.getItem('api_token')}`
            },
            body: JSON.stringify(assessmentData)
        });

        const result = await response.json();

        if (result.success) {
            console.log('Assessment scored:', result.data);
            displayScores(result.data);
        } else {
            console.error('Scoring failed:', result.message);
        }
    } catch (error) {
        console.error('Error scoring assessment:', error);
    }
}

// Analyze text in real-time
async function analyzeResponse(text, competency) {
    const response = await fetch('/api/ai/responses/analyze', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Authorization': `Bearer ${localStorage.getItem('api_token')}`
        },
        body: JSON.stringify({
            response: text,
            competency_context: competency
        })
    });

    const result = await response.json();
    return result.data;
}
```

## Integration Points

### 1. Assessment Submission Flow

**Location:** `SurveyController@submit`

When a participant completes an assessment:
1. Save responses to database
2. Call `DeepSeekService::scoreAssessment()`
3. Store AI-generated scores
4. Display results to participant

### 2. Assessor Review Flow

**Location:** `AssessorController@review`

When an assessor reviews responses:
1. Display participant responses
2. Call `DeepSeekService::analyzeQualitativeResponse()` for each response
3. Show AI insights alongside manual scoring
4. Allow assessor to adjust scores

### 3. Report Generation Flow

**Location:** `ManagerController@generateReport`

When generating assessment reports:
1. Fetch all scores and data
2. Call `DeepSeekService::identifyStrengthsWeaknesses()`
3. Call `DeepSeekService::generateReportNarrative()`
4. Call `DeepSeekService::generateRecommendations()`
5. Compile and save complete report

### 4. Development Planning

**Location:** New controller for development plans

When creating development plans:
1. Analyze participant assessment history
2. Call `DeepSeekService::generateRecommendations()`
3. Create personalized development activities
4. Assign to participant

## Best Practices

1. **Error Handling**: Always wrap DeepSeek calls in try-catch blocks
2. **Caching**: Consider caching AI responses to reduce API calls
3. **Rate Limiting**: Monitor API usage to stay within limits
4. **Fallbacks**: Have manual scoring as backup when AI fails
5. **Review**: Have human reviewers validate AI-generated scores
6. **Privacy**: Ensure participant data is handled according to privacy policies
7. **Logging**: Log all AI interactions for audit trails

## Troubleshooting

### API Key Issues

If you see "DeepSeek API key is not configured":
1. Check `.env` file has `DEEPSEEK_API_KEY=...`
2. Run `php artisan config:clear`
3. Restart your server

### Timeout Errors

If requests timeout:
1. Increase `DEEPSEEK_TIMEOUT` in `.env`
2. Check your internet connection
3. Verify DeepSeek service status

### Invalid Responses

If AI returns unexpected data:
1. Check your prompt formatting
2. Validate input data structure
3. Review DeepSeek model version compatibility

## Next Steps

1. Update your `.env` file with your actual DeepSeek API key
2. Test the integration with sample data
3. Integrate into your assessment workflow
4. Monitor usage and adjust as needed
5. Train your team on AI-assisted assessment features

## Support

For issues with:
- **DeepSeek API**: Contact DeepSeek support at support@deepseek.com
- **Integration Code**: Review this documentation or contact your development team
- **Assessment Platform**: Refer to your main application documentation
