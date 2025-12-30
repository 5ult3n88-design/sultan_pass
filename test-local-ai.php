<?php

/**
 * Local DeepSeek AI Test Script
 *
 * Tests if your local Ollama + DeepSeek setup is working
 * Run this from command line: php test-local-ai.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\LocalAIService;

echo "==============================================\n";
echo "Local DeepSeek AI Test (FREE & Offline!)\n";
echo "==============================================\n\n";

// Check if local AI is enabled
$enabled = config('services.local_ai.enabled');

if (!$enabled) {
    echo "❌ Local AI is not enabled in .env\n\n";
    echo "Please add to your .env file:\n";
    echo "LOCAL_AI_ENABLED=true\n";
    echo "LOCAL_AI_BASE_URL=http://localhost:11434\n";
    echo "LOCAL_AI_MODEL=deepseek-r1:7b\n";
    echo "LOCAL_AI_TIMEOUT=120\n\n";
    exit(1);
}

echo "✓ Local AI enabled: Yes\n";
echo "✓ Base URL: " . config('services.local_ai.base_url') . "\n";
echo "✓ Model: " . config('services.local_ai.model') . "\n\n";

// Check if Ollama is running
echo "Checking if Ollama is running...\n";

try {
    $response = @file_get_contents('http://localhost:11434/api/tags');
    if ($response === false) {
        echo "❌ Cannot connect to Ollama!\n\n";
        echo "Please make sure:\n";
        echo "1. Ollama is installed and running\n";
        echo "2. Open Ollama from Applications\n";
        echo "3. Check menu bar for Ollama icon\n\n";
        exit(1);
    }
    echo "✓ Ollama is running!\n\n";

    // Check if model is downloaded
    $models = json_decode($response, true);
    $modelExists = false;
    $targetModel = config('services.local_ai.model');

    if (isset($models['models'])) {
        foreach ($models['models'] as $model) {
            if ($model['name'] === $targetModel) {
                $modelExists = true;
                $size = round($model['size'] / (1024 * 1024 * 1024), 2);
                echo "✓ Model '{$targetModel}' is installed ({$size}GB)\n\n";
                break;
            }
        }
    }

    if (!$modelExists) {
        echo "❌ Model '{$targetModel}' is not installed!\n\n";
        echo "Please run:\n";
        echo "ollama pull {$targetModel}\n\n";
        echo "Available models:\n";
        if (isset($models['models'])) {
            foreach ($models['models'] as $model) {
                echo "  - {$model['name']}\n";
            }
        }
        echo "\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "❌ Error checking Ollama: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "Testing Local AI Service...\n\n";

try {
    $localAI = new LocalAIService();
    echo "✓ LocalAIService initialized successfully\n\n";

    // Test 1: Simple qualitative analysis
    echo "Test 1: Analyzing a qualitative response...\n";
    echo "-------------------------------------------\n";
    echo "(This may take 30-60 seconds for first request)\n\n";

    $testResponse = "The candidate showed excellent problem-solving skills and worked well under pressure. They communicated clearly with the team and delivered results on time.";

    $startTime = microtime(true);

    $analysis = $localAI->analyzeQualitativeResponse(
        $testResponse,
        'Problem Solving and Teamwork'
    );

    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);

    echo "✓ Analysis completed in {$duration} seconds!\n\n";
    echo "Results:\n";
    echo json_encode($analysis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n\n";

    // Test 2: Strengths and Weaknesses
    echo "Test 2: Identifying strengths and weaknesses...\n";
    echo "-----------------------------------------------\n";

    $competencyScores = [
        'Leadership' => 85,
        'Communication' => 72,
        'Problem Solving' => 90,
        'Teamwork' => 78,
    ];

    $startTime = microtime(true);

    $strengthsWeaknesses = $localAI->identifyStrengthsWeaknesses($competencyScores);

    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);

    echo "✓ Analysis completed in {$duration} seconds!\n\n";
    echo "Results:\n";
    echo json_encode($strengthsWeaknesses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n\n";

    echo "==============================================\n";
    echo "✓ All tests passed! Local DeepSeek is working!\n";
    echo "==============================================\n\n";
    echo "💡 Your AI is now running 100% FREE on your computer!\n";
    echo "💡 No internet required, no API costs, complete privacy!\n\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";

    if (strpos($e->getMessage(), 'Connection refused') !== false ||
        strpos($e->getMessage(), 'Failed to connect') !== false) {
        echo "This appears to be a connection issue.\n";
        echo "Please check:\n";
        echo "1. Ollama is running (check menu bar)\n";
        echo "2. The base URL is correct: http://localhost:11434\n";
        echo "3. Try running: curl http://localhost:11434\n\n";
    } elseif (strpos($e->getMessage(), 'timeout') !== false) {
        echo "This appears to be a timeout issue.\n";
        echo "Please check:\n";
        echo "1. Your computer has enough free RAM\n";
        echo "2. Try a smaller model: ollama pull deepseek-r1:1.5b\n";
        echo "3. Increase timeout in .env: LOCAL_AI_TIMEOUT=180\n\n";
    } else {
        echo "Please check the error message above for details.\n\n";
    }

    exit(1);
}
