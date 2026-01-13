<?php

use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\PasswordResetRequestController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AssessorController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\GradingController;
use App\Http\Controllers\AIDemoController;
use App\Http\Controllers\AI\AIAssistantController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->to(match (Auth::user()->role) {
            'admin' => route('dashboard.admin'),
            'manager' => route('dashboard.manager'),
            'assessor' => route('dashboard.assessor'),
            default => route('dashboard.participant'),
        });
    }

    return redirect()->route('login');
});

Route::post('locale/switch', function (Request $request) {
    $validated = $request->validate([
        'locale' => ['required', 'string', 'in:' . implode(',', config('app.available_locales', ['en']))],
        'redirect' => ['nullable', 'string'],
    ]);

    $locale = $validated['locale'];
    session(['locale' => $locale]);

    if ($request->user()) {
        $request->user()->update([
            'language_pref' => optional(\App\Models\Language::where('code', $locale)->first())->id,
        ]);
    }

    $redirect = $validated['redirect'] ?? url()->previous();

    return redirect()->to($redirect ?: url('/'));
})->name('locale.switch');

// AI Demo Routes (Public - No Authentication Required)
Route::get('ai-demo', [AIDemoController::class, 'index'])->name('ai-demo');
Route::post('ai-demo/analyze-qualitative', [AIDemoController::class, 'analyzeQualitative'])->name('ai-demo.analyze-qualitative');
Route::post('ai-demo/analyze-strengths', [AIDemoController::class, 'analyzeStrengths'])->name('ai-demo.analyze-strengths');

// AI Assistant Routes (For Admin, Manager, Assessor only)
Route::middleware(['auth', 'role:admin,manager,assessor'])->group(function () {
    Route::get('ai-assistant', [AIAssistantController::class, 'index'])->name('ai-assistant.index');
    Route::post('ai-assistant/chat', [AIAssistantController::class, 'chat'])->name('ai-assistant.chat');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.perform');

    Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->name('register.perform');

    Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('dashboard/admin', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('dashboard.admin');

    Route::get('dashboard/manager', [DashboardController::class, 'manager'])
        ->middleware('role:manager,admin')
        ->name('dashboard.manager');

    Route::get('dashboard/assessor', [DashboardController::class, 'assessor'])
        ->middleware('role:assessor,admin')
        ->name('dashboard.assessor');

    Route::get('dashboard/participant', [DashboardController::class, 'participant'])
        ->middleware('role:participant,manager,admin,assessor')
        ->name('dashboard.participant');

    Route::get('dashboard/examinee-performance', [DashboardController::class, 'examineePerformance'])
        ->middleware('role:participant,manager,admin,assessor')
        ->name('dashboard.examinee-performance');
    
    Route::get('dashboard/examinee-performance/user/{participant}', [DashboardController::class, 'examineePerformance'])
        ->middleware('role:manager,admin,assessor')
        ->name('dashboard.examinee-performance.user');

    Route::get('assessments/{assessment}/take', [SurveyController::class, 'take'])
        ->middleware('role:participant,manager,admin')
        ->name('assessments.take');

    Route::post('assessments/{assessment}/responses', [SurveyController::class, 'storeResponse'])
        ->middleware('role:participant,manager,admin')
        ->name('assessments.store-response');

    Route::get('assessments/{assessment}/review', [SurveyController::class, 'assessorReport'])
        ->middleware('role:assessor,admin')
        ->name('assessments.review');

    Route::get('assessments/{assessment}/report', [SurveyController::class, 'managerReport'])
        ->middleware('role:manager,admin')
        ->name('assessments.report');

    Route::middleware('role:manager,admin')->group(function () {
        Route::get('assessments/create', [AssessmentController::class, 'create'])->name('assessments.create');
        Route::post('assessments', [AssessmentController::class, 'store'])->name('assessments.store');
    });

    // Grading routes (assessors, managers, and admins)
    Route::middleware('role:assessor,manager,admin')->group(function () {
        Route::get('assessments/{assessment}/grade', [GradingController::class, 'index'])->name('assessments.grade');
        Route::get('assessments/{assessment}/grade/{participant}', [GradingController::class, 'show'])->name('assessments.grade-participant');
        Route::post('assessments/{assessment}/grade/{participant}', [GradingController::class, 'store'])->name('assessments.save-grade');
    });

    // Assessor routes
    Route::prefix('assessor')->name('assessor.')->middleware('role:assessor,admin')->group(function () {
        Route::get('assessments', [AssessorController::class, 'assessments'])->name('assessments');
        Route::get('participants', [AssessorController::class, 'participants'])->name('participants');
    });

    // Manager routes (limited access)
    Route::prefix('manager')->name('manager.')->middleware('role:manager,admin')->group(function () {
        Route::get('assessments', [ManagerController::class, 'assessments'])->name('assessments');
        Route::get('participants', [ManagerController::class, 'participants'])->name('participants');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        // Custom routes must come before resource route to avoid route conflicts
        Route::get('users/participants', [UserController::class, 'participants'])->name('users.participants');
        Route::get('users/create-participant', [UserController::class, 'create'])->name('users.create-participant');
        Route::get('users/import', [UserController::class, 'showImportForm'])->name('users.import');
        Route::get('users/import-participant', [UserController::class, 'showImportForm'])->name('users.import-participant');
        Route::post('users/import', [UserController::class, 'importCsv'])->name('users.import-csv');
        Route::get('users/export', [UserController::class, 'exportCsv'])->name('users.export-csv');
        Route::get('users/imported-passwords', [UserController::class, 'downloadImportedPasswords'])->name('users.imported-passwords');
        
        Route::resource('users', UserController::class)->except(['destroy']);
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('users/{user}/send-reset', [UserController::class, 'sendResetLink'])->name('users.send-reset');
        Route::post('users/{user}/generate-password', [UserController::class, 'regeneratePassword'])->name('users.generate-password');

        Route::resource('languages', LanguageController::class)->except(['destroy']);
        Route::delete('languages/{language}', [LanguageController::class, 'destroy'])->name('languages.destroy');

        Route::resource('password-resets', PasswordResetRequestController::class)
            ->only(['index', 'show'])
            ->parameters(['password-resets' => 'passwordResetRequest']);
        Route::post('password-resets/{passwordResetRequest}/approve', [PasswordResetRequestController::class, 'approve'])->name('password-resets.approve');
        Route::post('password-resets/{passwordResetRequest}/decline', [PasswordResetRequestController::class, 'decline'])->name('password-resets.decline');
    });
});
