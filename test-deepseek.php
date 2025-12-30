<?php

/**
 * DeepSeek Integration Test Script
 *
 * This script tests if your DeepSeek API integration is working correctly.
 * Run this from command line: php test-deepseek.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\DeepSeekService;

echo "==============================================\n";
echo "DeepSeek Integration Test\n";
echo "==============================================\n\n";

// Check if API key is configured
$apiKey = config('services.deepseek.api_key');

if (empty($apiKey) || $apiKey === 'your_deepseek_api_key_here') {
    echo "❌ ERROR: DeepSeek API key is not configured!\n\n";
    echo "Please follow these steps:\n";
    echo "1. Sign up at https://platform.deepseek.com\n";
    echo "2. Get your API key from the dashboard\n";
    echo "3. Update DEEPSEEK_API_KEY in your .env file\n";
    echo "4. Run: php artisan config:clear\n";
    echo "5. Try this test again\n\n";
    exit(1);
}

echo "✓ API Key configured: " . substr($apiKey, 0, 10) . "...\n";
echo "✓ Base URL: " . config('services.deepseek.base_url') . "\n";
echo "✓ Model: " . config('services.deepseek.model') . "\n\n";

echo "Testing DeepSeek Service...\n\n";

try {
    $deepSeekService = new DeepSeekService();
    echo "✓ DeepSeekService initialized successfully\n\n";

    // Test 1: Qualitative Analysis
    echo "Test 1: Analyzing a qualitative response...\n";
    echo "-------------------------------------------\n";

    $testResponse = "The candidate demonstrated strong analytical skills and attention to detail. They approached the problem methodically and provided a comprehensive solution. However, they could improve their communication clarity when explaining technical concepts.";

    $analysis = $deepSeekService->analyzeQualitativeResponse(
        $testResponse,
        'Problem Solving and Communication'
    );

    echo "✓ Analysis completed successfully!\n";
    echo "\nResults:\n";
    echo json_encode($analysis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n\n";

    // Test 2: Strengths and Weaknesses
    echo "Test 2: Identifying strengths and weaknesses...\n";
    echo "-----------------------------------------------\n";

    $competencyScores = [
        'Leadership' => 85,
        'Communication' => 72,
        'Strategic Thinking' => 90,
        'Team Collaboration' => 78,
        'Innovation' => 68,
    ];

    $strengthsWeaknesses = $deepSeekService->identifyStrengthsWeaknesses($competencyScores);

    echo "✓ Analysis completed successfully!\n";
    echo "\nResults:\n";
    echo json_encode($strengthsWeaknesses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n\n";

    echo "==============================================\n";
    echo "✓ All tests passed! DeepSeek is working correctly.\n";
    echo "==============================================\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";

    if (strpos($e->getMessage(), 'API key') !== false) {
        echo "This appears to be an API key issue.\n";
        echo "Please check:\n";
        echo "1. Your API key is correct in .env\n";
        echo "2. Your DeepSeek account is active\n";
        echo "3. You have API credits/quota available\n\n";
    } elseif (strpos($e->getMessage(), 'timeout') !== false || strpos($e->getMessage(), 'timed out') !== false) {
        echo "This appears to be a timeout issue.\n";
        echo "Please check:\n";
        echo "1. Your internet connection\n";
        echo "2. DeepSeek service status\n";
        echo "3. Increase DEEPSEEK_TIMEOUT in .env if needed\n\n";
    } else {
        echo "Please check the error message above for details.\n\n";
    }

    exit(1);
}
