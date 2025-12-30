<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Local AI Demo - DeepSeek R1</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Local DeepSeek AI Demo</h1>
                <p class="text-gray-600">100% FREE - Running on your computer with Ollama</p>
                <div class="mt-4 flex items-center space-x-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Offline & Free
                    </span>
                    <span class="text-sm text-gray-600">Model: deepseek-r1:1.5b</span>
                </div>
            </div>

            <!-- Test 1: Qualitative Response Analysis -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Test 1: Qualitative Response Analysis</h2>
                <p class="text-gray-600 mb-4">Analyze candidate responses and extract behavioral indicators, strengths, and development areas.</p>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Candidate Response:</label>
                    <textarea
                        id="qualitative-text"
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter candidate's response here..."
                    >The candidate showed excellent problem-solving skills and worked well under pressure. They communicated clearly with the team and delivered results on time.</textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Competency Context:</label>
                    <input
                        type="text"
                        id="competency-context"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="e.g., Problem Solving and Teamwork"
                        value="Problem Solving and Teamwork"
                    >
                </div>

                <button
                    onclick="analyzeQualitative()"
                    class="bg-blue-600 hover:bg-blue-700 text-gray-900 font-bold py-2 px-6 rounded-lg transition duration-200"
                >
                    Analyze Response
                </button>

                <div id="qualitative-loading" class="hidden mt-4">
                    <div class="flex items-center text-blue-600">
                        <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Analyzing... This may take 30-60 seconds</span>
                    </div>
                </div>

                <div id="qualitative-result" class="hidden mt-4 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-bold text-lg mb-2">Analysis Results:</h3>
                    <pre class="text-sm text-gray-800 whitespace-pre-wrap"></pre>
                </div>
            </div>

            <!-- Test 2: Strengths & Weaknesses -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Test 2: Identify Strengths & Weaknesses</h2>
                <p class="text-gray-600 mb-4">Analyze competency scores and provide detailed insights.</p>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Competency Scores:</label>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-600">Leadership:</label>
                            <input type="number" id="score-leadership" value="85" min="0" max="100" class="ml-2 px-3 py-1 border border-gray-300 rounded">
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Communication:</label>
                            <input type="number" id="score-communication" value="72" min="0" max="100" class="ml-2 px-3 py-1 border border-gray-300 rounded">
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Problem Solving:</label>
                            <input type="number" id="score-problem-solving" value="90" min="0" max="100" class="ml-2 px-3 py-1 border border-gray-300 rounded">
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Teamwork:</label>
                            <input type="number" id="score-teamwork" value="78" min="0" max="100" class="ml-2 px-3 py-1 border border-gray-300 rounded">
                        </div>
                    </div>
                </div>

                <button
                    onclick="analyzeStrengths()"
                    class="bg-green-600 hover:bg-green-700 text-gray-900 font-bold py-2 px-6 rounded-lg transition duration-200"
                >
                    Identify Strengths & Weaknesses
                </button>

                <div id="strengths-loading" class="hidden mt-4">
                    <div class="flex items-center text-green-600">
                        <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Analyzing... This may take 30-60 seconds</span>
                    </div>
                </div>

                <div id="strengths-result" class="hidden mt-4 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-bold text-lg mb-2">Analysis Results:</h3>
                    <pre class="text-sm text-gray-800 whitespace-pre-wrap"></pre>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center text-gray-600 text-sm">
                <p>Powered by DeepSeek R1 1.5B running locally via Ollama</p>
                <p class="mt-1">No API costs • Complete privacy • Works offline</p>
            </div>
        </div>
    </div>

    <script>
        async function analyzeQualitative() {
            const text = document.getElementById('qualitative-text').value;
            const context = document.getElementById('competency-context').value;
            const loadingDiv = document.getElementById('qualitative-loading');
            const resultDiv = document.getElementById('qualitative-result');
            const resultPre = resultDiv.querySelector('pre');

            // Show loading
            loadingDiv.classList.remove('hidden');
            resultDiv.classList.add('hidden');

            try {
                const response = await fetch('{{ route("ai-demo.analyze-qualitative") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        response: text,
                        competency_context: context
                    })
                });

                const data = await response.json();

                // Hide loading
                loadingDiv.classList.add('hidden');

                if (data.success) {
                    // Format the analysis nicely
                    let html = '';
                    const analysis = data.analysis;

                    if (analysis.key_themes) {
                        html += '<div class="mb-4"><h4 class="font-bold text-lg text-gray-800 mb-2">Key Themes:</h4><ul class="list-disc list-inside space-y-1">';
                        analysis.key_themes.forEach(theme => {
                            html += `<li class="text-gray-700">${theme}</li>`;
                        });
                        html += '</ul></div>';
                    }

                    if (analysis.behavioral_indicators) {
                        html += '<div class="mb-4"><h4 class="font-bold text-lg text-gray-800 mb-2">Behavioral Indicators:</h4><ul class="list-disc list-inside space-y-1">';
                        analysis.behavioral_indicators.forEach(indicator => {
                            html += `<li class="text-gray-700">${indicator}</li>`;
                        });
                        html += '</ul></div>';
                    }

                    if (analysis.strengths) {
                        html += '<div class="mb-4"><h4 class="font-bold text-lg text-green-700 mb-2">✓ Strengths:</h4><ul class="list-disc list-inside space-y-1">';
                        analysis.strengths.forEach(strength => {
                            html += `<li class="text-gray-700">${strength}</li>`;
                        });
                        html += '</ul></div>';
                    }

                    if (analysis.development_areas) {
                        html += '<div class="mb-4"><h4 class="font-bold text-lg text-orange-700 mb-2">→ Development Areas:</h4><ul class="list-disc list-inside space-y-1">';
                        analysis.development_areas.forEach(area => {
                            html += `<li class="text-gray-700">${area}</li>`;
                        });
                        html += '</ul></div>';
                    }

                    if (analysis.overall_assessment) {
                        html += `<div class="mt-4 p-3 bg-blue-50 rounded border border-blue-200"><h4 class="font-bold text-gray-800 mb-1">Overall Assessment:</h4><p class="text-gray-700">${analysis.overall_assessment}</p></div>`;
                    }

                    resultPre.innerHTML = html;
                    resultDiv.classList.remove('hidden');
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                loadingDiv.classList.add('hidden');
                alert('Error: ' + error.message);
            }
        }

        async function analyzeStrengths() {
            const scores = {
                'Leadership': parseInt(document.getElementById('score-leadership').value),
                'Communication': parseInt(document.getElementById('score-communication').value),
                'Problem Solving': parseInt(document.getElementById('score-problem-solving').value),
                'Teamwork': parseInt(document.getElementById('score-teamwork').value)
            };

            const loadingDiv = document.getElementById('strengths-loading');
            const resultDiv = document.getElementById('strengths-result');
            const resultPre = resultDiv.querySelector('pre');

            // Show loading
            loadingDiv.classList.remove('hidden');
            resultDiv.classList.add('hidden');

            try {
                const response = await fetch('{{ route("ai-demo.analyze-strengths") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        competency_scores: scores
                    })
                });

                const data = await response.json();

                // Hide loading
                loadingDiv.classList.add('hidden');

                if (data.success) {
                    // Format the analysis nicely
                    let html = '';
                    const analysis = data.analysis;

                    if (analysis.strengths || analysis.Strengths) {
                        const strengths = analysis.strengths || analysis.Strengths;
                        html += '<div class="mb-6"><h4 class="font-bold text-xl text-green-700 mb-3">✓ Top Strengths</h4>';
                        strengths.forEach(item => {
                            const name = item.name || item.Competency || item.competency;
                            const score = item.score || item.Score;
                            const desc = item.description || item.Description;
                            html += `<div class="mb-3 p-3 bg-green-50 rounded border border-green-200">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-semibold text-gray-800">${name}</span>
                                    <span class="text-sm font-bold text-green-700">${score}/100</span>
                                </div>
                                <p class="text-sm text-gray-700">${desc}</p>
                            </div>`;
                        });
                        html += '</div>';
                    }

                    if (analysis.development_areas || analysis.DevelopmentAreas) {
                        const devAreas = analysis.development_areas || analysis.DevelopmentAreas;
                        html += '<div class="mb-6"><h4 class="font-bold text-xl text-orange-700 mb-3">→ Development Areas</h4>';
                        devAreas.forEach(item => {
                            const name = item.name || item.Competency || item.competency;
                            const score = item.score || item.Score;
                            const desc = item.description || item.Description;
                            html += `<div class="mb-3 p-3 bg-orange-50 rounded border border-orange-200">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-semibold text-gray-800">${name}</span>
                                    <span class="text-sm font-bold text-orange-700">${score}/100</span>
                                </div>
                                <p class="text-sm text-gray-700">${desc}</p>
                            </div>`;
                        });
                        html += '</div>';
                    }

                    resultPre.innerHTML = html;
                    resultDiv.classList.remove('hidden');
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                loadingDiv.classList.add('hidden');
                alert('Error: ' + error.message);
            }
        }
    </script>
</body>
</html>
