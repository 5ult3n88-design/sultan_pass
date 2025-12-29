<?php

use App\Http\Controllers\AI\AssessmentScoringController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// AI-powered assessment scoring routes
Route::middleware(['auth:sanctum'])->prefix('ai')->name('ai.')->group(function () {
    // Assessment scoring
    Route::post('assessments/score', [AssessmentScoringController::class, 'scoreAssessment'])
        ->name('assessments.score');

    // Qualitative response analysis
    Route::post('responses/analyze', [AssessmentScoringController::class, 'analyzeQualitativeResponse'])
        ->name('responses.analyze');

    // Strengths and weaknesses identification
    Route::post('assessments/strengths-weaknesses', [AssessmentScoringController::class, 'identifyStrengthsWeaknesses'])
        ->name('assessments.strengths-weaknesses');

    // Report narrative generation
    Route::post('reports/generate-narrative', [AssessmentScoringController::class, 'generateReportNarrative'])
        ->name('reports.generate-narrative');

    // Development recommendations
    Route::post('recommendations/generate', [AssessmentScoringController::class, 'generateRecommendations'])
        ->name('recommendations.generate');
});
