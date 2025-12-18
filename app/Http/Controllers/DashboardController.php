<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

    public function participant(): View
    {
        $assignments = $this->participantEvaluations();

        return view('dashboards.participant', compact('assignments'));
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

    protected function participantEvaluations(?string $statusFilter = null): Collection
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
