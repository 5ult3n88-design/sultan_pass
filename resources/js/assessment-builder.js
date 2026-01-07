/**
 * Assessment Builder - Dynamic form handling for creating assessments
 */

document.addEventListener('DOMContentLoaded', function() {
    let questionCounter = 0;
    let categoryCounter = 0;
    const languages = window.languages || [];
    
    const scoringModeRadios = document.querySelectorAll('input[name="scoring_mode"]');
    const categoriesSection = document.getElementById('categories-section');
    const categoriesContainer = document.getElementById('categories-container');
    const questionsContainer = document.getElementById('questions-container');
    const addCategoryBtn = document.getElementById('add-category-btn');
    const addQuestionBtn = document.getElementById('add-question-btn');

    // Initialize scoring mode handling
    function updateScoringMode() {
        const selectedMode = document.querySelector('input[name="scoring_mode"]:checked')?.value;
        
        if (selectedMode === 'categorical') {
            categoriesSection.style.display = 'block';
            // Add default categories if none exist
            if (categoriesContainer.children.length === 0) {
                addCategory();
                addCategory();
            }
        } else {
            categoriesSection.style.display = 'none';
        }

        // Update all existing questions
        document.querySelectorAll('.question-item').forEach(updateQuestionScoring);
    }

    scoringModeRadios.forEach(radio => {
        radio.addEventListener('change', updateScoringMode);
    });

    // Initialize on page load
    updateScoringMode();

    // Add Category
    addCategoryBtn?.addEventListener('click', addCategory);

    function addCategory() {
        const index = categoryCounter++;
        let translationsHtml = '<div class="space-y-3 mt-4">';
        translationsHtml += '<p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Translations</p>';
        languages.forEach((lang, langIndex) => {
            translationsHtml += `
                <div class="rounded border border-white/10 bg-slate-800/50 p-3">
                    <p class="text-xs font-medium text-purple-300 mb-2">${lang.name} (${lang.code})</p>
                    <input type="hidden" name="categories[${index}][translations][${langIndex}][language_id]" value="${lang.id}">
                    <input type="text" name="categories[${index}][translations][${langIndex}][name]" 
                        class="w-full rounded border border-white/10 bg-slate-900/60 px-2 py-1.5 text-sm text-slate-200 focus:border-purple-400 focus:outline-none focus:ring-1 focus:ring-purple-400/40"
                        placeholder="Category name in ${lang.name}">
                    <textarea name="categories[${index}][translations][${langIndex}][description]" rows="2"
                        class="mt-2 w-full rounded border border-white/10 bg-slate-900/60 px-2 py-1.5 text-sm text-slate-200 focus:border-purple-400 focus:outline-none focus:ring-1 focus:ring-purple-400/40"
                        placeholder="Description (optional)"></textarea>
                </div>
            `;
        });
        translationsHtml += '</div>';
        
        const categoryHtml = `
            <div class="category-item rounded-xl border border-purple-500/30 bg-slate-900/50 p-4" data-category-index="${index}">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Default Category Name
                                </label>
                                <input type="text" name="categories[${index}][name]" 
                                    class="mt-2 w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-200 focus:border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-400/40"
                                    placeholder="e.g., Introvert" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Color
                                </label>
                                <input type="color" name="categories[${index}][color]" value="#3B82F6"
                                    class="mt-2 h-10 w-full rounded-lg border border-white/10 bg-slate-900/60 cursor-pointer">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Default Description (Optional)
                            </label>
                            <input type="text" name="categories[${index}][description]" 
                                class="mt-2 w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-200 focus:border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-400/40"
                                placeholder="Brief description">
                        </div>
                        ${translationsHtml}
                        <input type="hidden" name="categories[${index}][order]" value="${index + 1}">
                    </div>
                    <button type="button" class="remove-category-btn rounded-lg bg-rose-500/20 p-2 text-rose-300 transition hover:bg-rose-500/30">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        categoriesContainer.insertAdjacentHTML('beforeend', categoryHtml);
        
        // Attach remove handler
        const categoryItem = categoriesContainer.lastElementChild;
        categoryItem.querySelector('.remove-category-btn').addEventListener('click', function() {
            categoryItem.remove();
            // Update all questions to refresh category lists
            updateAllQuestions();
            // Re-validate category count
            validateCategories();
        });
        
        // Validate category count when adding
        validateCategories();
    }

    // Add Question
    addQuestionBtn?.addEventListener('click', addQuestion);

    function addQuestion() {
        const index = questionCounter++;
        const questionHtml = `
            <div class="question-item rounded-xl border border-white/10 bg-slate-900/50 p-5" data-question-index="${index}">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-uae-gold-300/20 text-sm font-semibold text-uae-gold-300">
                            ${index + 1}
                        </span>
                        <h3 class="text-sm font-semibold text-white">Question ${index + 1}</h3>
                    </div>
                    <button type="button" class="remove-question-btn rounded-lg bg-rose-500/20 p-2 text-rose-300 transition hover:bg-rose-500/30">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <input type="hidden" name="questions[${index}][order]" value="${index + 1}">
                    
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Question Type
                        </label>
                        <select name="questions[${index}][question_type]" class="question-type-select mt-2 w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40" required>
                            <option value="mcq">Multiple Choice (MCQ)</option>
                            <option value="written">Written Response</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">
                            Question Text (Default)
                        </label>
                        <textarea name="questions[${index}][question_text]" rows="2" 
                            class="mt-2 w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40"
                            placeholder="Enter your question here..." required></textarea>
                        
                        <div class="mt-3 space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Translations</p>
                            ${languages.map((lang, langIndex) => `
                                <div class="rounded border border-white/10 bg-slate-800/50 p-3">
                                    <p class="text-xs font-medium text-uae-gold-300 mb-2">${lang.name} (${lang.code})</p>
                                    <input type="hidden" name="questions[${index}][translations][${langIndex}][language_id]" value="${lang.id}">
                                    <textarea name="questions[${index}][translations][${langIndex}][question_text]" rows="2"
                                        class="w-full rounded border border-white/10 bg-slate-900/60 px-2 py-1.5 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-1 focus:ring-uae-gold-300/40"
                                        placeholder="Question text in ${lang.name}"></textarea>
                                </div>
                            `).join('')}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Question Image (Optional)
                        </label>
                        <input type="file" name="questions[${index}][question_image]" accept="image/jpeg,image/png,image/webp"
                            class="question-image-input mt-2 w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-200 file:mr-4 file:rounded file:border-0 file:bg-uae-gold-300/20 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-uae-gold-300 hover:file:bg-uae-gold-300/30">
                        <div class="question-image-preview mt-2 hidden">
                            <img src="" alt="Preview" class="max-h-32 rounded-lg border border-white/10">
                        </div>
                    </div>

                    <div class="max-score-container" style="display: none;">
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Maximum Score
                        </label>
                        <input type="number" name="questions[${index}][max_score]" step="0.01" min="0"
                            class="mt-2 w-full rounded-lg border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-2 focus:ring-uae-gold-300/40"
                            placeholder="e.g., 10.00">
                    </div>

                    <div class="mcq-answers-container">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Answer Options
                            </label>
                            <button type="button" class="add-answer-btn rounded-lg bg-green-500/20 px-3 py-1 text-xs font-semibold text-green-300 transition hover:bg-green-500/30">
                                + Add Answer
                            </button>
                        </div>
                        <div class="answers-list space-y-3">
                            <!-- Answers will be added here -->
                        </div>
                    </div>

                    <div class="written-note-container" style="display: none;">
                        <div class="rounded-lg border border-blue-500/30 bg-blue-500/10 p-3">
                            <p class="text-xs text-blue-300">
                                <strong>Note:</strong> This question will be graded manually after submission.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        questionsContainer.insertAdjacentHTML('beforeend', questionHtml);
        
        const questionItem = questionsContainer.lastElementChild;
        
        // Attach event handlers
        questionItem.querySelector('.remove-question-btn').addEventListener('click', function() {
            questionItem.remove();
            updateQuestionNumbers();
        });

        const questionTypeSelect = questionItem.querySelector('.question-type-select');
        questionTypeSelect.addEventListener('change', function() {
            updateQuestionType(questionItem);
        });

        const addAnswerBtn = questionItem.querySelector('.add-answer-btn');
        addAnswerBtn.addEventListener('click', function() {
            addAnswer(questionItem);
        });

        // Image preview
        const imageInput = questionItem.querySelector('.question-image-input');
        const imagePreview = questionItem.querySelector('.question-image-preview');
        imageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.querySelector('img').src = e.target.result;
                    imagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                imagePreview.classList.add('hidden');
            }
        });

        // Initialize question
        updateQuestionType(questionItem);
        updateQuestionScoring(questionItem);
        
        // Add default answers for MCQ
        addAnswer(questionItem);
        addAnswer(questionItem);
    }

    function updateQuestionType(questionItem) {
        const questionType = questionItem.querySelector('.question-type-select').value;
        const mcqContainer = questionItem.querySelector('.mcq-answers-container');
        const writtenContainer = questionItem.querySelector('.written-note-container');
        
        if (questionType === 'mcq') {
            mcqContainer.style.display = 'block';
            writtenContainer.style.display = 'none';
        } else {
            mcqContainer.style.display = 'none';
            writtenContainer.style.display = 'block';
        }
        
        updateQuestionScoring(questionItem);
    }

    function updateQuestionScoring(questionItem) {
        const scoringMode = document.querySelector('input[name="scoring_mode"]:checked')?.value;
        const questionType = questionItem.querySelector('.question-type-select').value;
        const maxScoreContainer = questionItem.querySelector('.max-score-container');
        
        // Show max score for percentile mode
        if (scoringMode === 'percentile') {
            maxScoreContainer.style.display = 'block';
            maxScoreContainer.querySelector('input').required = true;
        } else {
            maxScoreContainer.style.display = 'none';
            maxScoreContainer.querySelector('input').required = false;
        }
        
        // Update all answers in this question
        questionItem.querySelectorAll('.answer-item').forEach(updateAnswerScoring);
    }

    function addAnswer(questionItem) {
        const questionIndex = questionItem.dataset.questionIndex;
        const answersList = questionItem.querySelector('.answers-list');
        const answerIndex = answersList.children.length;
        
        const answerHtml = `
            <div class="answer-item rounded-lg border border-white/10 bg-slate-800/50 p-3" data-answer-index="${answerIndex}">
                <div class="flex gap-3">
                    <div class="flex-1 space-y-3">
                        <input type="hidden" name="questions[${questionIndex}][answers][${answerIndex}][order]" value="${answerIndex + 1}">
                        
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Answer Text (Default)</label>
                            <input type="text" name="questions[${questionIndex}][answers][${answerIndex}][answer_text]"
                                class="mt-1 w-full rounded border border-white/10 bg-slate-900/60 px-2 py-1.5 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-1 focus:ring-uae-gold-300/40"
                                placeholder="Answer option" required>
                            
                            <div class="mt-2 space-y-1.5">
                                <p class="text-xs font-semibold text-slate-400">Translations</p>
                                ${languages.map((lang, langIndex) => `
                                    <div class="rounded border border-white/10 bg-slate-800/40 p-2">
                                        <p class="text-xs font-medium text-uae-gold-300 mb-1">${lang.name}</p>
                                        <input type="hidden" name="questions[${questionIndex}][answers][${answerIndex}][translations][${langIndex}][language_id]" value="${lang.id}">
                                        <input type="text" name="questions[${questionIndex}][answers][${answerIndex}][translations][${langIndex}][answer_text]"
                                            class="w-full rounded border border-white/10 bg-slate-900/60 px-2 py-1 text-xs text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-1 focus:ring-uae-gold-300/40"
                                            placeholder="Answer in ${lang.name}">
                                    </div>
                                `).join('')}
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400">Answer Image (Optional)</label>
                            <input type="file" name="questions[${questionIndex}][answers][${answerIndex}][answer_image]" accept="image/jpeg,image/png,image/webp"
                                class="answer-image-input mt-1 w-full rounded border border-white/10 bg-slate-900/60 px-2 py-1 text-xs text-slate-200 file:mr-2 file:rounded file:border-0 file:bg-uae-gold-300/20 file:px-2 file:py-0.5 file:text-xs file:text-uae-gold-300">
                            <div class="answer-image-preview mt-2 hidden">
                                <img src="" alt="Preview" class="max-h-20 rounded border border-white/10">
                            </div>
                        </div>

                        <div class="scoring-section">
                            <!-- Scoring inputs will be added here based on mode -->
                        </div>
                    </div>
                    
                    <button type="button" class="remove-answer-btn h-8 rounded bg-rose-500/20 px-2 text-rose-300 transition hover:bg-rose-500/30">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        answersList.insertAdjacentHTML('beforeend', answerHtml);
        
        const answerItem = answersList.lastElementChild;
        
        // Attach remove handler
        answerItem.querySelector('.remove-answer-btn').addEventListener('click', function() {
            answerItem.remove();
        });

        // Image preview
        const imageInput = answerItem.querySelector('.answer-image-input');
        const imagePreview = answerItem.querySelector('.answer-image-preview');
        imageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.querySelector('img').src = e.target.result;
                    imagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                imagePreview.classList.add('hidden');
            }
        });

        updateAnswerScoring(answerItem);
    }

    function updateAnswerScoring(answerItem) {
        const scoringMode = document.querySelector('input[name="scoring_mode"]:checked')?.value;
        const scoringSection = answerItem.querySelector('.scoring-section');
        const questionItem = answerItem.closest('.question-item');
        const questionIndex = questionItem.dataset.questionIndex;
        const answerIndex = answerItem.dataset.answerIndex;
        
        if (scoringMode === 'categorical') {
            // Get all categories with their actual form indices
            const categories = Array.from(document.querySelectorAll('.category-item'));
            
            let categoriesHtml = '<label class="block text-xs font-semibold text-slate-400 mb-2">Category Weights</label>';
            categoriesHtml += '<div class="space-y-2">';
            
            categories.forEach((cat) => {
                const catIndex = cat.dataset.categoryIndex;
                const catNameInput = cat.querySelector('input[name*="[name]"]');
                const catName = catNameInput?.value || `Category ${parseInt(catIndex) + 1}`;
                
                categoriesHtml += `
                    <div class="flex items-center gap-2">
                        <input type="checkbox" 
                            id="q${questionIndex}_a${answerIndex}_cat${catIndex}" 
                            class="category-checkbox rounded border-white/10 bg-slate-900/60 text-purple-500 focus:ring-2 focus:ring-purple-500/40"
                            data-category-index="${catIndex}">
                        <label for="q${questionIndex}_a${answerIndex}_cat${catIndex}" class="flex-1 text-xs text-slate-300">${catName}</label>
                        <input type="number" 
                            name="questions[${questionIndex}][answers][${answerIndex}][categories][${catIndex}][weight]" 
                            class="category-weight w-20 rounded border border-white/10 bg-slate-900/60 px-2 py-1 text-xs text-slate-200 disabled:opacity-50" 
                            placeholder="0.0" 
                            step="0.1" 
                            min="0" 
                            max="10"
                            disabled>
                    </div>
                `;
            });
            
            categoriesHtml += '</div>';
            scoringSection.innerHTML = categoriesHtml;
            
            // Attach checkbox handlers
            scoringSection.querySelectorAll('.category-checkbox').forEach(checkbox => {
                const weightInput = checkbox.parentElement.querySelector('.category-weight');
                checkbox.addEventListener('change', function() {
                    weightInput.disabled = !this.checked;
                    if (this.checked && !weightInput.value) {
                        weightInput.value = '1.0';
                    }
                    // Clear value and disable when unchecked
                    if (!this.checked) {
                        weightInput.value = '';
                    }
                });
            });
            
            // Update category names when they change
            categories.forEach((cat) => {
                const catIndex = cat.dataset.categoryIndex;
                const catNameInput = cat.querySelector('input[name*="[name]"]');
                if (catNameInput) {
                    const updateHandler = () => {
                        const label = scoringSection.querySelector(`label[for="q${questionIndex}_a${answerIndex}_cat${catIndex}"]`);
                        if (label) {
                            label.textContent = catNameInput.value || `Category ${parseInt(catIndex) + 1}`;
                        }
                    };
                    catNameInput.addEventListener('input', updateHandler);
                }
            });
            
        } else if (scoringMode === 'percentile') {
            scoringSection.innerHTML = `
                <label class="block text-xs font-semibold text-slate-400">Score Value</label>
                <input type="number" 
                    name="questions[${questionIndex}][answers][${answerIndex}][score_value]" 
                    class="mt-1 w-full rounded border border-white/10 bg-slate-900/60 px-2 py-1.5 text-sm text-slate-200 focus:border-uae-gold-300 focus:outline-none focus:ring-1 focus:ring-uae-gold-300/40" 
                    placeholder="0.00" 
                    step="0.01" 
                    min="0"
                    required>
            `;
        }
    }

    function updateAllQuestions() {
        document.querySelectorAll('.question-item').forEach(updateQuestionScoring);
    }

    function updateQuestionNumbers() {
        document.querySelectorAll('.question-item').forEach((item, index) => {
            const numberBadge = item.querySelector('.rounded-full');
            const heading = item.querySelector('h3');
            if (numberBadge) numberBadge.textContent = index + 1;
            if (heading) heading.textContent = `Question ${index + 1}`;
            item.querySelector('input[name*="[order]"]').value = index + 1;
        });
    }

    function validateCategories() {
        const scoringMode = document.querySelector('input[name="scoring_mode"]:checked')?.value;
        if (scoringMode === 'categorical') {
            const categoryCount = document.querySelectorAll('.category-item').length;
            const categoriesSection = document.getElementById('categories-section');
            if (categoryCount < 2) {
                categoriesSection?.classList.add('border-rose-500/50');
            } else {
                categoriesSection?.classList.remove('border-rose-500/50');
            }
        }
    }

    // Form validation
    const form = document.getElementById('assessment-form');
    form?.addEventListener('submit', function(e) {
        const scoringMode = document.querySelector('input[name="scoring_mode"]:checked')?.value;
        
        if (scoringMode === 'categorical') {
            const categoryCount = document.querySelectorAll('.category-item').length;
            if (categoryCount < 2) {
                e.preventDefault();
                alert('Please add at least 2 categories for a categorical assessment.');
                return false;
            }
        }
        
        const questionCount = document.querySelectorAll('.question-item').length;
        if (questionCount < 1) {
            e.preventDefault();
            alert('Please add at least 1 question to the assessment.');
            return false;
        }
        
        // Validate that MCQ questions have at least 2 answers
        let hasInvalidQuestion = false;
        document.querySelectorAll('.question-item').forEach(questionItem => {
            const questionType = questionItem.querySelector('.question-type-select').value;
            if (questionType === 'mcq') {
                const answerCount = questionItem.querySelectorAll('.answer-item').length;
                if (answerCount < 2) {
                    hasInvalidQuestion = true;
                }
            }
        });
        
        if (hasInvalidQuestion) {
            e.preventDefault();
            alert('Each MCQ question must have at least 2 answer options.');
            return false;
        }
        
        return true;
    });
});

