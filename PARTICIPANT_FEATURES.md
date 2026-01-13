# Participant Dashboard Features

## Navigation Menu
When a participant logs in, they see a navigation bar with three main sections:

### 1. My Dashboard (Default View)
- **Left Column**: "My Assessments" 
  - Shows assessments and tests already assigned to the participant
  - Displays status (invited, in_progress, completed, withdrawn)
  - Shows current score or "Pending"
  - Action buttons: "Start", "Continue", or "View results" based on status

- **Right Column**: "Available Assessments"
  - Lists all new assessments and tests available to start
  - Shows test type (percentile/categorical) and duration
  - "Start" button to begin assessment/test
  - Both regular assessments and tests are unified in this view

### 2. My Tests
- Direct access to all available tests
- Can filter and browse tests
- Quick access to start any available test

### 3. My Performance
- Examinee Performance Dashboard
- Shows overall scores and analytics
- IQ test results
- Performance trends over time
- Category scores breakdown

## Test Taking Interface
When taking a test, participants see:

### Timer (if test has time limit)
- Countdown timer at the top
- Changes color when 5 minutes remaining (orange/red)
- Pulsing animation when 1 minute remaining
- Auto-submits test when time expires

### Progress Indicators
- Progress percentage (e.g., "75% Completed")
- Questions answered counter (e.g., "3/5 Answered")
- Marked for review counter
- **Blue dots**: Questions that have been answered
- **Orange dots**: Questions marked for review
- **Gray dots**: Unanswered questions
- **Larger dot**: Current question being viewed

### Question Navigation
- **Previous/Next buttons**: Navigate between questions
- **Click on dots**: Jump directly to any question
- **Mark for Review button**: Flag questions to revisit later
- All questions visible at once via dot navigation

### Question Display
- **Bilingual support**: Questions shown in both English and Arabic
- **Multiple choice**: Radio buttons with bilingual answer choices
- **Typed answers**: Text area for written responses
- **Marks display**: Shows point value for each question (percentile tests)

### Test Controls
- **Exit Test**: Leave the test without submitting
- **Submit Test**: Submit all answers when complete
- Form validation: Required questions must be answered

## Bilingual Support
- All interface elements available in English and Arabic
- Language switcher in header
- Questions and answers displayed in both languages
- RTL support for Arabic text

## User Features
- Theme toggle (light/dark mode)
- Language preferences saved
- Session management
- Secure logout

## Features Implemented
✅ Timer countdown with color changes and auto-submit
✅ Blue dots for answered questions
✅ Orange color for "mark for review"
✅ Navigate forward/back between questions
✅ Progress percentage and question counters
✅ Bilingual display (English & Arabic)
✅ Click dots to jump to specific questions
✅ Unified assessments and tests interface
✅ Performance dashboard integration
✅ Navigation menu for easy access to all sections
