<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Test;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function admin(): View
    {
        $metrics = [
            'users' => User::count(),
            'assessments' => $this->metricCount('assessments'),
            'active_plans' => $this->metricCount('development_plans', ['status' => 'active']),
            'notifications' => $this->metricCount('notifications'),
        ];

        $recentUsers = User::query()
            ->latest()
            ->limit(5)
            ->get(['id', 'username', 'email', 'role', 'status', 'created_at']);

        $languages = Language::withCount('users')
            ->orderByDesc('users_count')
            ->get(['id', 'code', 'name']);

        $dashboardStats = [
            'participants_active' => User::query()
                ->where('role', 'participant')
                ->where('status', 'active')
                ->count(),
            'live_assessments' => $this->liveAssessmentsCount(),
            'pending_resets' => $this->pendingPasswordResets(),
            'next_assessment' => $this->nextAssessment(),
        ];

        $assessmentStatusStats = $this->assessmentsByStatus();
        $dailyCompletionSeries = $this->dailyCompletions();
        $topPerformers = $this->topPerformers();
        $userRolesChart = $this->userRolesChartData();
        $assessmentsBarChart = $this->assessmentsBarChartData();

        return view('dashboards.admin', compact(
            'metrics',
            'recentUsers',
            'languages',
            'dashboardStats',
            'assessmentStatusStats',
            'dailyCompletionSeries',
            'topPerformers',
            'userRolesChart',
            'assessmentsBarChart'
        ));
    }

    public function manager(): View
    {
        $teamMembers = User::query()
            ->where('role', 'participant')
            ->orderBy('full_name')
            ->limit(8)
            ->get(['id', 'full_name', 'department', 'status']);

        $upcomingAssessments = $this->assessmentsForRole('manager');

        return view('dashboards.manager', compact('teamMembers', 'upcomingAssessments'));
    }

    public function assessor(): View
    {
        $assignedAssessments = $this->assessmentsForRole('assessor');
        $pendingEvaluations = $this->participantEvaluations('in_progress');

        return view('dashboards.assessor', compact('assignedAssessments', 'pendingEvaluations'));
    }

    public function participant(Request $request): View
    {
        $user = $request->user();

        // Assessments where this user is already a participant
        $assignments = $this->participantEvaluations(statusFilter: null, participantId: $user->id);

        // Published / active assessments that this user has not yet started
        $availableAssessments = $this->availableAssessmentsForParticipant($user);

        // Published tests available for participants
        $availableTests = Schema::hasTable('tests')
            ? Test::query()->where('status', 'published')->latest()->limit(5)->get(['id', 'title', 'test_type', 'duration_minutes'])
            : collect();

        return view('dashboards.participant', compact('assignments', 'availableAssessments', 'availableTests'));
    }

    public function examineePerformance(Request $request, ?User $participant = null): View
    {
        $currentUser = $request->user();
        $canViewOthers = in_array($currentUser->role, ['admin', 'manager', 'assessor'], true);

        // If a participant is passed via route, respect it only for privileged roles
        if ($participant && ! $canViewOthers) {
            $participant = $currentUser;
        }

        // If no participant from route, optionally pick from query param (for dropdown)
        if (! $participant) {
            $participantId = (int) $request->query('participant_id', 0);

            if ($canViewOthers && $participantId > 0) {
                $participant = User::query()
                    ->where('role', 'participant')
                    ->find($participantId);
            }

            if (! $participant) {
                $participant = $currentUser;
            }
        }

        // Build participant list (for selector) for privileged roles
        $participantsList = collect();
        if ($canViewOthers) {
            $participantsList = User::query()
                ->where('role', 'participant')
                ->orderByRaw('COALESCE(full_name, username)')
                ->get(['id', 'full_name', 'username', 'department']);
        }

        // Get overall evaluation score (0-100%)
        $overallScore = $this->calculateOverallScore($participant);

        // Get psychometric test categories with scores (for pie chart)
        $categoryScores = $this->getCategoryScores($participant);

        // Get IQ test results (for bar chart)
        $iqTestResults = $this->getIQTestResults($participant);

        // Get performance trends over time (for bottom-right chart)
        $performanceTrends = $this->getPerformanceTrends($participant);

        return view('dashboards.examinee-performance', compact(
            'participant',
            'participantsList',
            'overallScore',
            'categoryScores',
            'iqTestResults',
            'performanceTrends'
        ));
    }

    protected function calculateOverallScore(User $participant): float
    {
        if (!Schema::hasTable('assessment_participants')) {
            return 0.0;
        }

        $scores = DB::table('assessment_participants')
            ->where('participant_id', $participant->id)
            ->where('status', 'completed')
            ->whereNotNull('score')
            ->pluck('score')
            ->map(function ($score) {
                // Convert old 0-10 scale scores to 0-100 scale
                // If score is less than 20, assume it's on old 0-10 scale
                if ($score < 20) {
                    return (float) $score * 10;
                }
                return (float) $score;
            });

        if ($scores->isEmpty()) {
            return 0.0;
        }

        $avgScore = $scores->avg();

        // Normalize to 0-100
        return min(100, max(0, round($avgScore, 2)));
    }

    protected function getCategoryScores(User $participant): array
    {
        if (!Schema::hasTable('participant_responses') || !Schema::hasTable('assessment_categories')) {
            return ['labels' => [], 'values' => [], 'colors' => []];
        }

        // Get all participant responses (MCQ + written) that have either
        // selected answers or graded category weights.
        $responses = DB::table('participant_responses')
            ->where('participant_id', $participant->id)
            ->where(function ($q) {
                $q->whereNotNull('selected_answer_ids')
                    ->orWhereNotNull('graded_categories');
            })
            ->get();

        if ($responses->isEmpty()) {
            return ['labels' => [], 'values' => [], 'colors' => []];
        }

        // Calculate category scores from answer weights and graded categories
        $categoryTotals = [];
        $categoryMax = [];
        $categoryInfo = [];

        foreach ($responses as $response) {
            // 1) MCQ questions: infer categories from selected answers
            $selectedAnswerIds = json_decode($response->selected_answer_ids ?? '[]', true);
            if (is_array($selectedAnswerIds) && !empty($selectedAnswerIds)) {
                $weights = DB::table('answer_category_weights as acw')
                    ->join('assessment_categories as ac', 'ac.id', '=', 'acw.category_id')
                    ->whereIn('acw.answer_id', $selectedAnswerIds)
                    ->select('ac.id', 'ac.name', 'ac.color', 'acw.weight')
                    ->get();

                foreach ($weights as $weight) {
                    $catId = $weight->id;
                    if (!isset($categoryTotals[$catId])) {
                        $categoryTotals[$catId] = 0;
                        $categoryMax[$catId] = 0;
                        $categoryInfo[$catId] = [
                            'name' => $weight->name,
                            'color' => $weight->color ?: '#B68A35',
                        ];
                    }

                    $categoryTotals[$catId] += (float) $weight->weight;
                    // Assume max weight of 3.0 per answer by default
                    $categoryMax[$catId] += 3.0;
                }
            }

            // 2) Written questions graded by assessor: use graded_categories JSON
            if (!empty($response->graded_categories)) {
                $gradedCategories = json_decode($response->graded_categories, true);
                if (is_array($gradedCategories)) {
                    foreach ($gradedCategories as $catId => $weight) {
                        if (!isset($categoryTotals[$catId])) {
                            // Look up category info once
                            $category = DB::table('assessment_categories')->where('id', $catId)->first();
                            $categoryInfo[$catId] = [
                                'name' => $category->name ?? __('Category :id', ['id' => $catId]),
                                'color' => $category->color ?? '#B68A35',
                            ];
                            $categoryTotals[$catId] = 0;
                            $categoryMax[$catId] = 0;
                        }

                        $categoryTotals[$catId] += (float) $weight;
                        // Assume max weight of 3.0 per graded category value
                        $categoryMax[$catId] += 3.0;
                    }
                }
            }
        }

        if (empty($categoryTotals)) {
            return ['labels' => [], 'values' => [], 'colors' => []];
        }

        // Normalize each category total to 0-100 based on its max weight
        $labels = [];
        $values = [];
        $colors = [];

        foreach ($categoryTotals as $catId => $total) {
            $max = max($categoryMax[$catId] ?? 0, 1);
            $normalizedScore = min(100, max(0, ($total / $max) * 100));

            $labels[] = $categoryInfo[$catId]['name'] ?? __('Category :id', ['id' => $catId]);
            $values[] = round($normalizedScore, 1);
            $colors[] = $categoryInfo[$catId]['color'] ?? '#B68A35';
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors,
        ];
    }

    protected function getIQTestResults(User $participant): array
    {
        if (!Schema::hasTable('assessment_participants') || !Schema::hasTable('assessments')) {
            return ['score' => null, 'test_name' => null, 'test_date' => null];
        }

        // Get the most recent IQ test result (assessments whose title contains 'IQ')
        $iqResult = DB::table('assessment_participants as ap')
            ->join('assessments as a', 'a.id', '=', 'ap.assessment_id')
            ->join('assessment_translations as at', 'at.assessment_id', '=', 'a.id')
            ->where('ap.participant_id', $participant->id)
            ->where('at.title', 'like', '%IQ%')
            ->where('ap.status', 'completed')
            ->whereNotNull('ap.score')
            ->select(
                'at.title',
                'ap.score',
                'ap.updated_at'
            )
            ->orderBy('ap.updated_at', 'desc')
            ->first();

        if (!$iqResult) {
            return ['score' => null, 'test_name' => null, 'test_date' => null];
        }

        // Normalize score to 0-100 if needed (assuming score is already 0-100)
        $score = min(100, max(0, (float) $iqResult->score));
        
        return [
            'score' => $score,
            'test_name' => $iqResult->title,
            'test_date' => \Carbon\Carbon::parse($iqResult->updated_at)->translatedFormat('F Y'),
        ];
    }

    protected function getPerformanceTrends(User $participant): array
    {
        if (!Schema::hasTable('assessment_participants')) {
            return ['labels' => [], 'values' => []];
        }

        // Get performance over last 6 months
        $trends = DB::table('assessment_participants as ap')
            ->where('ap.participant_id', $participant->id)
            ->where('ap.status', 'completed')
            ->whereNotNull('ap.score')
            ->where('ap.updated_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(ap.updated_at, "%Y-%m") as month'),
                DB::raw('AVG(ap.score) as avg_score'),
                DB::raw('COUNT(*) as test_count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = [];
        $values = [];

        foreach ($trends as $trend) {
            $labels[] = \Carbon\Carbon::createFromFormat('Y-m', $trend->month)->translatedFormat('M Y');
            $score = (float) $trend->avg_score;
            // Convert old 0-10 scale scores to 0-100 scale
            if ($score < 20) {
                $score = $score * 10;
            }
            $values[] = round($score, 2);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    protected function metricCount(string $table, array $where = []): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        $query = DB::table($table);

        foreach ($where as $column => $value) {
            $query->where($column, $value);
        }

        return (int) $query->count();
    }

    protected function assessmentsForRole(string $role): Collection
    {
        if (! Schema::hasTable('assessments')) {
            return collect();
        }

        return DB::table('assessments')
            ->select('id', 'type', 'start_date', 'end_date', 'status')
            ->orderBy('start_date')
            ->limit(6)
            ->get()
            ->map(function ($assessment) use ($role) {
                $assessment->start_date = $this->toCarbon($assessment->start_date);
                $assessment->end_date = $this->toCarbon($assessment->end_date);
                $assessment->role = $role;
                return $assessment;
            });
    }

    /**
     * List active / published assessments that the given participant
     * has not yet started. These are shown on the participant dashboard
     * as \"Available assessments\".
     */
    protected function availableAssessmentsForParticipant(User $participant): Collection
    {
        if (! Schema::hasTable('assessments')) {
            return collect();
        }

        $now = Carbon::now();

        // Resolve current language (if available) to pick the right title
        $languageId = null;
        if (Schema::hasTable('languages')) {
            $languageId = DB::table('languages')
                ->where('code', app()->getLocale())
                ->value('id');
        }

        $query = DB::table('assessments as a')
            ->leftJoin('assessment_participants as ap', function ($join) use ($participant) {
                $join->on('ap.assessment_id', '=', 'a.id')
                    ->where('ap.participant_id', '=', $participant->id);
            })
            ->whereNull('ap.id')
            ->where('a.status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereNull('a.start_date')->orWhere('a.start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('a.end_date')->orWhere('a.end_date', '>=', $now);
            })
            ->orderBy('a.start_date')
            ->select([
                'a.id',
                'a.type',
                'a.start_date',
                'a.end_date',
                'a.status',
            ])
            ->limit(10);

        $rows = $query->get();

        // Attach a localized title if translations table is available
        if (Schema::hasTable('assessment_translations') && $languageId) {
            $titles = DB::table('assessment_translations')
                ->whereIn('assessment_id', $rows->pluck('id')->all())
                ->where('language_id', $languageId)
                ->pluck('title', 'assessment_id');

            $rows->transform(function ($row) use ($titles) {
                $row->title = $titles[$row->id] ?? null;
                $row->start_date = $this->toCarbon($row->start_date);
                $row->end_date = $this->toCarbon($row->end_date);
                return $row;
            });
        } else {
            $rows->transform(function ($row) {
                $row->start_date = $this->toCarbon($row->start_date);
                $row->end_date = $this->toCarbon($row->end_date);
                return $row;
            });
        }

        return $rows;
    }

    protected function participantEvaluations(?string $statusFilter = null, ?int $participantId = null): Collection
    {
        if (! Schema::hasTable('assessment_participants')) {
            return collect();
        }

        $query = DB::table('assessment_participants as ap')
            ->select([
                'ap.id',
                'ap.status',
                'ap.score',
                'ap.assessment_id',
                'ap.created_at',
            ])
            ->orderByDesc('ap.created_at')
            ->limit(10);

        if ($statusFilter) {
            $query->where('ap.status', $statusFilter);
        }

        if ($participantId) {
            $query->where('ap.participant_id', $participantId);
        }

        $items = $query->get();

        if (Schema::hasTable('assessments')) {
            $assessmentTitles = DB::table('assessments')
                ->select('id', 'type', 'status')
                ->pluck('type', 'id');

            $items->transform(function ($item) use ($assessmentTitles) {
                $item->type = $assessmentTitles[$item->assessment_id] ?? 'assessment';
                $item->created_at = $this->toCarbon($item->created_at);
                return $item;
            });
        }

        return $items;
    }

    protected function liveAssessmentsCount(): int
    {
        if (! Schema::hasTable('assessments')) {
            return 0;
        }

        $now = Carbon::now();

        return (int) DB::table('assessments')
            ->where('status', 'active')
            ->where('start_date', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })
            ->count();
    }

    protected function pendingPasswordResets(): int
    {
        if (! Schema::hasTable('password_reset_requests')) {
            return 0;
        }

        return (int) DB::table('password_reset_requests')->where('status', 'pending')->count();
    }

    protected function nextAssessment(): ?object
    {
        if (! Schema::hasTable('assessments')) {
            return null;
        }

        $record = DB::table('assessments')
            ->select('id', 'type', 'status', 'start_date')
            ->whereNotNull('start_date')
            ->where('start_date', '>', Carbon::now())
            ->orderBy('start_date')
            ->first();

        if ($record) {
            $record->start_date = $this->toCarbon($record->start_date);
        }

        return $record;
    }

    protected function assessmentsByStatus(): Collection
    {
        if (! Schema::hasTable('assessments')) {
            return collect();
        }

        $rows = DB::table('assessments')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $grandTotal = max($rows->sum('total'), 1);

        return $rows->map(function ($row) use ($grandTotal) {
            return [
                'status' => $row->status ?? 'unknown',
                'total' => (int) $row->total,
                'percentage' => round(($row->total / $grandTotal) * 100),
            ];
        });
    }

    protected function dailyCompletions(int $days = 7): Collection
    {
        if (! Schema::hasTable('assessment_participants')) {
            return collect();
        }

        $start = Carbon::now()->subDays($days - 1)->startOfDay();

        $raw = DB::table('assessment_participants')
            ->selectRaw('DATE(updated_at) as day, COUNT(*) as total')
            ->where('status', 'completed')
            ->where('updated_at', '>=', $start)
            ->groupBy('day')
            ->pluck('total', 'day');

        $series = collect();

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->startOfDay();
            $key = $date->toDateString();
            $count = (int) ($raw[$key] ?? 0);

            $series->push([
                'label' => $date->translatedFormat('D'),
                'date' => $date,
                'total' => $count,
            ]);
        }

        $max = max($series->max('total'), 1);

        return $series->map(function ($item) use ($max) {
            $item['percentage'] = $max === 0 ? 0 : ($item['total'] / $max) * 100;
            return $item;
        });
    }

    protected function topPerformers(int $limit = 3): Collection
    {
        if (! Schema::hasTable('assessment_participants') || ! Schema::hasTable('users')) {
            return collect();
        }

        $select = [
            'ap.id',
            'ap.score',
            'ap.updated_at',
            'users.full_name',
            'users.username',
        ];

        $query = DB::table('assessment_participants as ap')
            ->select($select)
            ->join('users', 'users.id', '=', 'ap.participant_id')
            ->whereNotNull('ap.score')
            ->where('ap.status', 'completed')
            ->orderByDesc('ap.score')
            ->limit($limit);

        if (Schema::hasTable('assessments')) {
            $query->addSelect('assessments.type as assessment_type')
                ->leftJoin('assessments', 'assessments.id', '=', 'ap.assessment_id');
        } else {
            $query->addSelect(DB::raw('NULL as assessment_type'));
        }

        $items = $query->get();

        return $items->map(function ($item) {
            $item->updated_at = $this->toCarbon($item->updated_at);
            return $item;
        });
    }

    protected function toCarbon($value)
    {
        if (! $value) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function userRolesChartData(): array
    {
        $roles = User::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        $labels = $roles->keys()->map(fn($role) => ucfirst($role))->toArray();
        $values = $roles->values()->toArray();

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    protected function assessmentsBarChartData(): array
    {
        if (! Schema::hasTable('assessments')) {
            return ['labels' => [], 'values' => []];
        }

        $stats = DB::table('assessments')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->pluck('total', 'status');

        $labels = $stats->keys()->map(fn($status) => ucfirst($status))->toArray();
        $values = $stats->values()->toArray();

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }
}
