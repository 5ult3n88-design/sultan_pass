<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    private const TYPES = [
        'psychometric',
        'interview',
        'group_exercise',
        'written_test',
        'role_play',
        'committee_interview',
        'other',
    ];

    private const STATUSES = [
        'draft',
        'active',
        'closed',
    ];

    public function create(Request $request): View
    {
        $languages = Language::query()->orderBy('name')->get(['id', 'name', 'code']);
        $layout = $request->user()->role === 'admin' ? 'layouts.dashboard' : 'layouts.role';

        return view('assessments.create', [
            'languages' => $languages,
            'types' => self::TYPES,
            'statuses' => self::STATUSES,
            'layout' => $layout,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'type' => ['required', Rule::in(self::TYPES)],
            'status' => ['required', Rule::in(self::STATUSES)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'translations' => ['required', 'array'],
            'translations.*.language_id' => ['required', 'exists:languages,id'],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.description' => ['nullable', 'string'],
        ]);

        $translations = collect($validated['translations'])
            ->map(function (array $translation): array {
                return [
                    'language_id' => (int) $translation['language_id'],
                    'title' => trim($translation['title']),
                    'description' => $translation['description'] ?? null,
                ];
            })
            ->filter(fn (array $translation) => $translation['title'] !== '');

        if ($translations->isEmpty()) {
            return back()
                ->withErrors(['translations' => __('Please provide at least one title for the assessment.')])
                ->withInput();
        }

        $assessment = DB::transaction(function () use ($validated, $translations, $user): Assessment {
            $assessment = Assessment::create([
                'type' => $validated['type'],
                'status' => $validated['status'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'created_by' => $user->id,
            ]);

            $translations->each(function (array $translation) use ($assessment): void {
                $assessment->translations()->create($translation);
            });

            return $assessment;
        });

        return redirect()
            ->route($user->role === 'admin' ? 'dashboard.admin' : 'dashboard.manager')
            ->with('status', __('Assessment ":title" created successfully.', [
                'title' => $this->primaryTitle($translations),
            ]));
    }

    private function primaryTitle(Collection $translations): string
    {
        $defaultLocale = app()->getLocale();
        $languages = Language::query()->pluck('id', 'code');
        $preferredLanguageId = $languages[$defaultLocale] ?? null;

        if ($preferredLanguageId) {
            $matched = $translations->firstWhere('language_id', $preferredLanguageId);
            if ($matched) {
                return $matched['title'];
            }
        }

        return $translations->first()['title'] ?? __('New assessment');
    }
}
