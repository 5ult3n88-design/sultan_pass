# eAssess — AI‑Assisted Psychometric Selection

AI‑assisted psychometric testing platform that scores candidates and recommends them for missions/tasks based on attributes measured during assessments. This repository currently provides a clean, multilingual database schema (Laravel migrations) and guidance to integrate with a Laravel backend and MySQL.

**Tech Stack**
- Backend: `Laravel` (PHP)
- Frontend: Any (Blade, Inertia, SPA) — integrate with Laravel routes
- Database: `MySQL`
- AI Layer: Pluggable service (HTTP/microservice or in‑process library)

**Key Features**
- AI‑driven candidate scoring and mission/task recommendations
- Psychometric, interview, group exercise, role‑play, and written test types
- Role‑based access: admin, assessor, participant, manager
- Multilingual content: languages and translations for assessments, templates, competencies, and plans
- Weighted scoring by competency, assessor notes and feedback
- Development plans with activities for post‑assessment growth
- Reporting and exports (PDF/Excel/PowerBI), notifications, audit logs

**Database Overview (from `Migration_Folder/`)**
- `languages`: Supported UI/content languages
- `users`, `roles_permissions`: Users and coarse permissions
- `assessments`, `assessment_translations`: Assessment instances and localized titles/descriptions
- `assessment_templates`, `assessment_template_translations`: Reusable templates
- `competencies`, `competency_translations`: Competency catalog and localization
- `assessment_items`: Weighted rubric items per assessment/competency
- `assessment_participants`: Participant enrollment, status, score, feedback
- `assessor_notes`: Per‑competency scoring and qualitative notes
- `development_plans`, `development_plan_translations`: Individual/group development plans
- `development_activities`, `development_activity_translations`: Plan activities and localization
- `assessment_reports`, `report_exports`: Aggregated results and export history
- `notifications`, `audit_logs`: System messaging and traceability

**How AI Fits In**
- Inputs: per‑competency scores, weights, assessor notes, assessment type, role requirements
- Outputs: overall score, strengths/weaknesses, recommended missions/tasks, development suggestions
- Integration options:
  - In‑process PHP service (e.g., `App\Services\CandidateScoringService`)
  - External microservice over HTTP (Python/Node/R) called from Laravel
- Example of use: Prompting AI LLM model about specific test taker. Allowing AI to choose individuals that fit specific mission and roles

Example PHP service interface:
```
interface CandidateScoringService {
    public function score(array $signals): array; // returns [overall, per_competency, recommendations]
}
```

**Getting Started**
1) Create or use a Laravel app
- New: `composer create-project laravel/laravel eassess`

2) Move migrations
- Copy files from `Migration_Folder` into your app’s `database/migrations`

3) Configure database
- Set `.env` keys: `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

4) Install and migrate
- `composer install`
- `php artisan key:generate`
- `php artisan migrate --seed` *(creates a Super Admin: `superadmin / Admin@12345`)*

5) Build the frontend assets
- `npm install`
- `npm run build` for production or `npm run dev` for Vite hot reload

6) Run locally
- `php artisan serve` then open the app URL

**Available Dashboards & Tools**
- Super Admin: user provisioning, language management, locale-aware UX
- Manager: team snapshot, upcoming assessments
- Assessor: scoring workspace, quick exports, evaluation notes
- Participant: streamlined assessment experience with autosave cues

**Localization**
- Two languages provided by default: English and Arabic (`Settings → Languages`)
- Locale middleware persists preferences per-user and across sessions
- UI strings live in `/resources/lang/en.json` and `/resources/lang/ar.json`

**Testing**
- Execute `php artisan test` to validate locale switching and role-restricted dashboards
- Feature tests cover admin dashboard access and locale switching workflows

**Next Steps**
- Implement Eloquent models matching the schema
- Add controllers/routes for assessments, participants, scoring, reports
- Wire in the AI scoring service and expose recommendations in the UI
- Add policies/middleware for roles and permissions
- Build exports (PDF/Excel) and notification triggers

**Notes**
- This repo focuses on schema and structure. Business logic, UI, and the AI model/service are intentionally left pluggable so you can implement rules‑based scoring first and upgrade to ML later.

# Steps to implement from scratch:
1. Database Schema
Created migrations:
2025_11_13_093440_create_password_reset_requests_table.php - Table for password reset requests with fields: user_id, email, status (pending/approved/declined), token, approved_by, approved_at, declined_at, temporary_password_encrypted, temporary_password_expires_at, notes
2025_11_13_093446_add_password_reset_enum_to_notifications_table.php - Adds 'password_reset' to the notification_type enum (skips SQLite for testing)
2. Models
Created:
app/Models/PasswordResetRequest.php - With relationships to User (requester and approver), status constants, pending() scope, and accessor for decrypted temporary password
app/Models/Notification.php - Basic model with user_id, message, notification_type, sent_at, is_read
app/Models/Assessment.php - With creator() and translations() relationships
app/Models/AssessmentTranslation.php - With assessment() and language() relationships
Updated:
app/Models/User.php - Added passwordResetRequests() relationship
3. Password Reset Workflow
Updated app/Http/Controllers/Auth/AuthController.php:
Modified sendResetLinkEmail() to:
Block admins from requesting resets
Create a PasswordResetRequest record instead of sending email
Notify all admins via Notification records
Return success message
Created app/Http/Controllers/Admin/PasswordResetRequestController.php:
index() - Lists all requests with pending count
show() - Displays request details and temporary password if approved
approve() - Generates random password, updates user, encrypts and stores password, creates notification
decline() - Marks request as declined with optional notes
4. Assessment Creation Feature
Created app/Http/Controllers/AssessmentController.php:
create() - Shows form with languages, types, and statuses
store() - Validates and creates assessment with translations
primaryTitle() - Helper to get display title
5. Routes
Updated routes/web.php:
Added password reset routes under admin prefix:
GET admin/password-resets → index
GET admin/password-resets/{id} → show
POST admin/password-resets/{id}/approve → approve
POST admin/password-resets/{id}/decline → decline
Added assessment routes (manager/admin only):
GET assessments/create → create
POST assessments → store
6. Views
Created:
resources/views/admin/password-resets/index.blade.php - Table listing all requests with status badges
resources/views/admin/password-resets/show.blade.php - Request details with approve/decline forms and temporary password display
resources/views/assessments/create.blade.php - Form for creating assessments with multilingual translations
Updated:
resources/views/layouts/dashboard.blade.php - Added "Password resets" nav item with pending count badge, added "New assessment" link
resources/views/dashboards/admin.blade.php - Added "Create assessment" section
resources/views/dashboards/manager.blade.php - Added "Create assessment" section
resources/views/layouts/role.blade.php - Added Vite conditional for testing environment
7. Testing Infrastructure
Created:
tests/Feature/PasswordResetRequestTest.php - Tests for request creation and admin approval
tests/Feature/AssessmentCreationTest.php - Tests for manager access and creation
database/factories/LanguageFactory.php - Factory for Language model
Updated:
tests/TestCase.php - Configured array session driver for testing
tests/CreatesApplication.php - Set SQLite database and array session for tests
8. Key Implementation Details
Password reset:
Admins cannot use forgot password (must update in database)
Temporary passwords are encrypted in database
Passwords expire after 1 day
All admins receive notifications when requests are made
Assessment creation:
Both managers and admins can create assessments
Supports multiple language translations
Validates at least one translation title is provided
Stores created_by to track creator
Navigation:
Admin dashboard shows pending password reset count
Both dashboards have quick access to create assessments
Conditional Vite loading to prevent errors in testing

# Terminal Commands Executed
1. Database Migrations
php artisan make:migration create_password_reset_requests_table --create=password_reset_requestsphp artisan make:migration add_password_reset_enum_to_notifications_table --table=notifications
2. Models
php artisan make:model PasswordResetRequestphp artisan make:model Notificationphp artisan make:model Assessmentphp artisan make:model AssessmentTranslation
3. Controllers
php artisan make:controller Admin/PasswordResetRequestControllerphp artisan make:controller AssessmentController
4. Factory
php artisan make:factory LanguageFactory --model=Language
5. Testing & Verification
php artisan testphp artisan test --filter=DashboardAccessTestphp artisan route:list | findstr assessments.createphp artisan route:list | findstr dashboard.admin
6. Final Migration
php artisan migrate
Complete Command Sequence (In Order)

# Terminal Commands Executed
1. Database Migrations
php artisan make:migration create_password_reset_requests_table --create=password_reset_requestsphp artisan make:migration add_password_reset_enum_to_notifications_table --table=notifications
2. Models
php artisan make:model PasswordResetRequestphp artisan make:model Notificationphp artisan make:model Assessmentphp artisan make:model AssessmentTranslation
3. Controllers
php artisan make:controller Admin/PasswordResetRequestControllerphp artisan make:controller AssessmentController
4. Factory
php artisan make:factory LanguageFactory --model=Language
5. Testing & Verification
php artisan testphp artisan test --filter=DashboardAccessTestphp artisan route:list | findstr assessments.createphp artisan route:list | findstr dashboard.admin
6. Final Migration
php artisan migrate
Complete Command Sequence (In Order)

## Work Split

Rashed
1- find frontend template
2- user login, register, forget password .. etc

abdurhman + khlied
slides (Arabic & Enlish) and gant chart and color palette and logos

mohamad + sultan
users and languages model and controller 

## Vendor on Onedrive Folder

https://actvet-my.sharepoint.com/personal/ammar_abasi_adpoly_ac_ae/_layouts/15/onedrive.aspx?id=%2Fpersonal%2Fammar%5Fabasi%5Fadpoly%5Fac%5Fae%2FDocuments%2FPASS&CT=1762944151559&OR=OWA%2DNT%2DMail&CID=ef08ec60%2D9b68%2D02e9%2D9642%2D717d8a0b554c&e=5%3A60869f7ffa9e4499a4230d11db8c7f8e&sharingv2=true&fromShare=true&at=9&FolderCTID=0x012000825386393E04FA4AA61C86C893B8C4A1
