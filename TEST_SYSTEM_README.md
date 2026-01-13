# Test Creation System Documentation

## Overview

The PASS application now includes a comprehensive test creation system that allows **assessors, managers, and admins** to create two types of tests:

1. **Percentile Tests** - Scored exams like IQ tests, knowledge assessments (x/100)
2. **Categorical Tests** - Personality assessments, type classifications (MBTI, 12 personalities, etc.)

---

## Features Implemented

### ✅ Database Schema (10 Tables)

- **tests** - Main test table with test_type (percentile/categorical)
- **test_translations** - Multilingual test titles/descriptions
- **test_categories** - Categories for categorical tests
- **test_category_translations** - Multilingual category names
- **test_questions** - Questions with question_type (multiple_choice/typed)
- **test_question_translations** - Multilingual questions
- **test_answer_choices** - Answer options with correct flag or category assignment
- **test_answer_choice_translations** - Multilingual answer choices
- **test_assignments** - Assigns tests to participants
- **test_responses** - Stores participant answers and grading
- **test_results** - Final scores and category results

### ✅ Eloquent Models (7 Models)

- `Test` - With helper methods (isPercentile(), isCategorical())
- `TestQuestion` - With relationships to answers
- `TestAnswerChoice` - Links to categories
- `TestCategory` - For categorical tests
- `TestAssignment` - Tracks assignments
- `TestResponse` - Individual answers
- `TestResult` - Final results

### ✅ Controller & Routes

**TestController** with methods:
- `index()` - List all tests
- `create()` - Show test type selection
- `createType()` - Show test builder
- `store()` - Save test with questions
- `show()` - View test details
- `edit()` - Edit test
- `update()` - Update test
- `destroy()` - Delete test

**Routes** (middleware: auth, role:assessor,manager,admin):
```
GET  /tests                 - List all tests
GET  /tests/create          - Test type selection
POST /tests/create-type     - Load test builder
POST /tests                 - Store new test
GET  /tests/{test}          - View test
GET  /tests/{test}/edit     - Edit test
PUT  /tests/{test}          - Update test
DELETE /tests/{test}        - Delete test
```

### ✅ Views Created

1. **tests/create.blade.php** - Test type selection page with visual cards
2. **tests/index.blade.php** - Tests list with filtering
3. **Sidebar navigation** - Added "Tests" link with count badge

---

## How It Works

### Creating a Percentile Test

1. **Choose Test Type** → Select "Percentile Test"
2. **Set Parameters**:
   - Total marks (e.g., 100)
   - Passing marks (e.g., 60)
   - Duration (e.g., 60 minutes)
3. **Add Questions**:
   - **Multiple Choice**: Add choices, mark the correct one
   - **Typed Answer**: Participant types answer, you grade manually later
4. **Publish** or save as **Draft**

**Workflow**:
```
Create Test → Add Questions → Set Correct Answers → Publish
        ↓
Assign to Participants → They Take Test → Auto-grade MC questions
        ↓
Manually grade typed answers → Calculate percentage → Pass/Fail
```

### Creating a Categorical Test

1. **Choose Test Type** → Select "Categorical Test"
2. **Define Categories**:
   - Example: "Introvert", "Extrovert", "Ambivert"
   - Or: "INTJ", "ENFP", "ISTJ", etc. (16 personalities)
3. **Add Questions**:
   - **Multiple Choice**: Map each choice to a category
   - **Typed Answer**: Manually assign to category after participant answers
4. **Publish**

**Workflow**:
```
Create Test → Define Categories → Add Questions → Map Choices to Categories
        ↓
Assign to Participants → They Take Test → Count category selections
        ↓
Manually categorize typed answers → Determine dominant category
```

---

## Database Relationships

```
Test (1) → (M) TestQuestion
Test (1) → (M) TestCategory
Test (1) → (M) TestAssignment

TestQuestion (1) → (M) TestAnswerChoice
TestAnswerChoice (M) → (1) TestCategory

TestAssignment (1) → (M) TestResponse
TestAssignment (1) → (1) TestResult

TestResponse (M) → (1) TestQuestion
TestResponse (M) → (1) TestAnswerChoice
TestResponse (M) → (1) TestCategory (for typed answers)
```

---

## Test Taking Flow (Not Yet Implemented)

### Percentile Test:
1. Participant opens test
2. Answers multiple choice → stored in `test_responses.selected_choice_id`
3. Types answers → stored in `test_responses.typed_answer`
4. Submits test → status = 'submitted'
5. Auto-grade MC questions → mark as correct/incorrect
6. Assessor grades typed answers → assign marks
7. Calculate total → store in `test_results`
8. If percentage >= passing_marks → result_status = 'pass', else 'fail'

### Categorical Test:
1. Participant opens test
2. Answers multiple choice → each choice has category_id
3. Types answers → assessor assigns category later
4. Submits test → status = 'submitted'
5. Count answers per category → store in `test_results.category_scores` (JSON)
6. Determine dominant category → store in `test_results.dominant_category_id`

---

## Grading Interface (Not Yet Implemented)

### For Percentile Tests:
- List all submitted tests awaiting grading
- For each typed answer:
  - Show question and participant's answer
  - Input field for marks awarded
  - Textarea for feedback
  - Button to mark as graded
- Auto-calculate total marks and percentage
- Update result_status (pass/fail)

### For Categorical Tests:
- List all submitted tests
- For each typed answer:
  - Show question and participant's answer
  - Dropdown to select category
  - Button to assign category
- Auto-calculate category counts
- Determine and display dominant category

---

## Next Steps to Complete the System

### 1. Test Builder UI (High Priority)
Create **tests/builder.blade.php** with dynamic JavaScript form:
- If percentile: show "Total Marks" and "Passing Marks" fields
- If categorical: show "Add Category" interface
- Dynamic "Add Question" button
- For each question:
  - Question text input
  - Question type selector (multiple choice / typed)
  - If multiple choice:
    - "Add Choice" button
    - For percentile: checkbox "Is Correct"
    - For categorical: dropdown "Assign to Category"
- Submit button to save entire test

### 2. Test Taking Interface
Create **tests/take.blade.php**:
- Display test title, description, duration
- Start timer
- For each question:
  - Show question text
  - If multiple choice: radio buttons for choices
  - If typed: textarea for answer
- Submit button
- Auto-save progress

### 3. Grading Dashboard
Create **tests/grade.blade.php**:
- List tests awaiting grading
- For each test assignment:
  - Show participant name
  - Show submission date
  - List typed answers needing grading
  - Input marks (percentile) or select category (categorical)
  - Save grades button

### 4. Results Display
Create **tests/results.blade.php**:
- For percentile:
  - Show score: X/100
  - Percentage: X%
  - Status: Pass/Fail
  - Breakdown by question
- For categorical:
  - Show dominant category with description
  - Bar chart of category scores
  - Percentage breakdown

### 5. Test Assignment Interface
Create **tests/assign.blade.php**:
- Select test
- Select participants (checkboxes)
- Set due date
- Send notification option
- Assign button

---

## Code Examples

### Creating a Test Programmatically

```php
use App\Models\Test;
use App\Models\TestCategory;
use App\Models\TestQuestion;
use App\Models\TestAnswerChoice;

// Create a categorical test (personality test)
$test = Test::create([
    'title' => '12 Personality Types Assessment',
    'description' => 'Discover your personality type',
    'test_type' => 'categorical',
    'duration_minutes' => 30,
    'status' => 'published',
    'created_by' => auth()->id(),
]);

// Define categories
$categories = [
    'Introvert' => '#3b82f6',
    'Extrovert' => '#ef4444',
    'Ambivert' => '#10b981',
];

$categoryModels = [];
foreach ($categories as $name => $color) {
    $categoryModels[$name] = TestCategory::create([
        'test_id' => $test->id,
        'name' => $name,
        'color' => $color,
        'order' => count($categoryModels),
    ]);
}

// Add a question
$question = TestQuestion::create([
    'test_id' => $test->id,
    'question_text' => 'How do you recharge your energy?',
    'question_type' => 'multiple_choice',
    'marks' => 1,
    'order' => 0,
]);

// Add answer choices mapped to categories
TestAnswerChoice::create([
    'test_question_id' => $question->id,
    'choice_text' => 'By spending time alone',
    'category_id' => $categoryModels['Introvert']->id,
    'order' => 0,
]);

TestAnswerChoice::create([
    'test_question_id' => $question->id,
    'choice_text' => 'By socializing with friends',
    'category_id' => $categoryModels['Extrovert']->id,
    'order' => 1,
]);
```

### Assigning a Test

```php
use App\Models\TestAssignment;

TestAssignment::create([
    'test_id' => $test->id,
    'participant_id' => $user->id,
    'assigned_by' => auth()->id(),
    'assigned_at' => now(),
    'due_date' => now()->addDays(7),
    'status' => 'assigned',
]);
```

### Storing Participant Responses

```php
use App\Models\TestResponse;

// Multiple choice answer
TestResponse::create([
    'test_assignment_id' => $assignment->id,
    'test_question_id' => $question->id,
    'selected_choice_id' => $choiceId,
]);

// Typed answer
TestResponse::create([
    'test_assignment_id' => $assignment->id,
    'test_question_id' => $question->id,
    'typed_answer' => 'My answer here...',
    'is_graded' => false,
]);
```

### Grading Typed Answers

```php
// For percentile test
$response->update([
    'is_graded' => true,
    'is_correct' => true, // or false
    'marks_awarded' => 5,
    'assessor_feedback' => 'Good answer!',
    'graded_by' => auth()->id(),
    'graded_at' => now(),
]);

// For categorical test
$response->update([
    'is_graded' => true,
    'assigned_category_id' => $categoryId,
    'assessor_feedback' => 'Categorized as Introvert',
    'graded_by' => auth()->id(),
    'graded_at' => now(),
]);
```

### Calculating Results

```php
use App\Models\TestResult;

// For percentile test
$totalMarks = $assignment->test->total_marks;
$obtainedMarks = $assignment->responses()
    ->where('is_graded', true)
    ->sum('marks_awarded');
$percentage = ($obtainedMarks / $totalMarks) * 100;
$passed = $percentage >= $assignment->test->passing_marks;

TestResult::create([
    'test_assignment_id' => $assignment->id,
    'total_marks_obtained' => $obtainedMarks,
    'percentage' => $percentage,
    'result_status' => $passed ? 'pass' : 'fail',
    'completed_at' => now(),
]);

// For categorical test
$categoryCounts = $assignment->responses()
    ->whereNotNull('selected_choice_id')
    ->with('selectedChoice.category')
    ->get()
    ->groupBy('selectedChoice.category_id')
    ->map->count();

$dominantCategoryId = $categoryCounts->sortDesc()->keys()->first();

TestResult::create([
    'test_assignment_id' => $assignment->id,
    'dominant_category_id' => $dominantCategoryId,
    'category_scores' => $categoryCounts->toArray(),
    'completed_at' => now(),
]);
```

---

## UI Access

1. **Login** as admin/manager/assessor
2. **Sidebar** → Click "Tests" (shows count of all tests)
3. **Tests page** → Click "Create New Test"
4. **Select Type** → Choose Percentile or Categorical
5. **Build Test** → (Next step: need to implement builder UI)

---

## Translation Support

All test components support multilingual content:
- Test titles and descriptions
- Category names and descriptions
- Question text
- Answer choice text

To add translations, create records in:
- `test_translations`
- `test_category_translations`
- `test_question_translations`
- `test_answer_choice_translations`

---

## Technical Notes

### Database Constraints
- Unique constraint on test assignments per participant
- Unique constraint on responses per question per assignment
- Soft deletes enabled on tests table
- Foreign key cascades on delete

### Validation Rules
- Test title: required, max 255 chars
- Test type: required, enum (percentile, categorical)
- Questions: minimum 1 required
- Multiple choice: minimum 2 choices required
- Categories: required for categorical tests

### Performance Considerations
- Eager load relationships when displaying lists
- Index on test_type, status, created_by
- Paginate test lists (15 per page)

---

## Current Status

### ✅ Completed
- Database schema and migrations
- Eloquent models with relationships
- Controller CRUD operations
- Routes with role-based middleware
- Test type selection UI
- Tests listing UI
- Sidebar navigation integration

### 🔨 In Progress
- Test builder interface (JavaScript-based dynamic form)

### 📅 Pending
- Test taking interface for participants
- Manual grading dashboard
- Auto-grading system for multiple choice
- Results calculation and display
- Test assignment interface
- Notifications for test assignments
- Export results to PDF/Excel

---

## Estimated Completion

- **Test Builder UI**: 2-3 hours
- **Test Taking Interface**: 2-3 hours
- **Grading Dashboard**: 3-4 hours
- **Results Display**: 2 hours
- **Test Assignment**: 1-2 hours
- **Auto-grading Logic**: 1-2 hours

**Total remaining**: 11-16 hours of development

---

## Support

For questions or issues:
1. Check this documentation
2. Review the database schema in migrations
3. Examine model relationships
4. Test with sample data using tinker

---

**Last Updated**: January 6, 2026
**Version**: 1.0 (Foundation Complete)
