# Statement of Work (SOW)

**Project Title:** PASS - Psychometric Assessment System for UAE Ministry of Defence
**Date:** January 5, 2026
**Version:** 1.0

---

## 1. Project Overview

The **PASS (Psychometric Assessment System)** is an AI-assisted psychometric testing platform designed to evaluate, score, and recommend candidates for missions and tasks within the UAE Ministry of Defence. The system measures candidate attributes through various assessment types (psychometric tests, interviews, group exercises, role-plays, and written tests) and leverages artificial intelligence to provide data-driven recommendations for personnel selection and development.

**Objectives:**
- Streamline the candidate assessment and selection process
- Provide AI-driven scoring and mission/task recommendations
- Support multilingual content (English and Arabic) for diverse user base
- Enable comprehensive development planning based on assessment results
- Ensure role-based access control and data security
- Facilitate reporting, analytics, and audit trails for compliance

---

## 2. Problem to Solve

### Current State Challenges:

**Opportunities:**
- Manual assessment processes are time-consuming and prone to human bias
- Lack of standardized evaluation criteria across different assessment types
- Limited ability to match candidates to specific missions based on competency profiles
- No centralized system for tracking candidate development over time
- Insufficient multilingual support for bilingual workforce (English/Arabic)

**Issues:**
- Inconsistent scoring methodologies across different assessors
- Difficulty in aggregating and analyzing assessment data
- No automated recommendation system for mission/task assignment
- Limited visibility into candidate strengths and development areas
- Lack of integration between assessment results and development planning

**Risks:**
- Suboptimal personnel assignments leading to mission inefficiency
- Potential bias in manual assessment and selection processes
- Data loss or inconsistency without centralized system
- Security concerns with sensitive personnel data
- Compliance challenges without comprehensive audit trails

**Stakeholder Needs:**
- **MOD Leadership:** Data-driven insights for strategic personnel decisions
- **Managers:** Efficient tools to assess teams and assign candidates to missions
- **Assessors:** Standardized scoring interfaces with AI assistance
- **Participants:** Fair, transparent, and accessible assessment experience
- **HR/Admin:** User management, reporting, and system configuration capabilities

---

## 3. Scope of Work

### 3.1 Solution's Features vs Opportunities

| Solution's Feature | Addressed Opportunity, Issue, Risk, Need or Expectation |
|-------------------|--------------------------------------------------------|
| **1. AI-Assisted Candidate Scoring** | Eliminates bias through ML-driven evaluation; provides consistent, objective scoring across all assessors; generates overall scores with strengths/weaknesses analysis |
| **2. Multi-Assessment Type Support** | Supports psychometric tests, interviews, group exercises, role-plays, and written tests; standardizes evaluation criteria across all assessment types |
| **3. Mission/Task Recommendation Engine** | AI analyzes competency profiles and recommends optimal candidates for specific missions/tasks; improves personnel assignment accuracy |
| **4. Role-Based Access Control** | Four distinct roles (Admin, Manager, Assessor, Participant) with granular permissions; ensures data security and appropriate access levels |
| **5. Multilingual Support (EN/AR)** | Complete English and Arabic UI translations; locale-aware content for assessments, templates, competencies, and development plans; automatic RTL support for Arabic |
| **6. Competency-Based Assessment** | Weighted scoring by competency; customizable competency catalog with translations; maps individual strengths to organizational requirements |
| **7. Development Plan Management** | Post-assessment growth planning; individual and group development plans; activity tracking and localization; links assessment results to actionable development activities |
| **8. Assessment Templates & Reusability** | Reusable assessment templates with translations; reduces setup time for recurring assessments; ensures consistency across similar evaluations |
| **9. Assessor Notes & Qualitative Feedback** | Per-competency scoring with qualitative notes; captures assessor insights beyond numerical scores; provides context for AI recommendations |
| **10. Comprehensive Reporting & Exports** | PDF/Excel/PowerBI export capabilities; aggregated assessment reports; visual analytics dashboards; export history tracking |
| **11. Notification System** | Real-time notifications for assessment assignments, approvals, and status changes; keeps stakeholders informed throughout assessment lifecycle |
| **12. Audit Logs & Compliance** | Complete audit trail of all system actions; user activity tracking; supports compliance and accountability requirements |
| **13. Password Reset Workflow** | Secure admin-approved password reset process; prevents unauthorized access; includes temporary password generation with expiration |
| **14. User Import/Export** | Bulk user creation via CSV import; participant onboarding efficiency; password generation and export for distribution |
| **15. AI Chat Assistant** | Context-aware AI assistant for system guidance; natural language interface for common tasks; reduces training requirements |
| **16. UAE MOD Branding & Theming** | Official UAE MOD visual identity; dual-theme support (dark/light modes); professional amber/gold color scheme; responsive design |
| **17. Assessment Participation Tracking** | Enrollment status management; progress tracking; score aggregation; feedback collection |
| **18. Language Management** | Dynamic language addition; user count per language; centralized locale configuration |

---

### 3.2 Deliverables

#### Software Application
**Level of Completion:** **MVP (Minimum Viable Product) - Production Ready**

The system includes:
- ✅ Complete database schema with 20+ tables
- ✅ Authentication and authorization system
- ✅ Four role-based dashboards (Admin, Manager, Assessor, Participant)
- ✅ User management with import/export
- ✅ Assessment creation and management
- ✅ AI integration for scoring and recommendations
- ✅ Multilingual support (English/Arabic)
- ✅ Development plan management
- ✅ Reporting and export functionality
- ✅ Password reset workflow
- ✅ Notification system
- ✅ AI chat assistant
- ✅ UAE MOD branding and dual-theme support
- ✅ Responsive web interface

#### Documentation

1. **README.md** - Installation guide, tech stack, feature overview, getting started instructions
2. **STATEMENT_OF_WORK.md** (this document) - Comprehensive project scope and requirements
3. **Database Schema Documentation** - Entity relationships, migration files, table descriptions
4. **API/Route Documentation** - Available endpoints and controller actions
5. **Code Comments** - Inline documentation for models, controllers, and services
6. **Testing Documentation** - Feature test coverage and test execution instructions

#### Testing and QA

**Functional Testing:**
- ✅ User authentication and authorization flows
- ✅ Role-based dashboard access control
- ✅ Locale switching and multilingual content
- ✅ Assessment creation with translations
- ✅ Password reset request and approval workflow
- ✅ User import/export functionality

**Non-Functional Testing:**
- ✅ Security: Role-based access control, password encryption, secure session management
- ✅ Performance: Database indexing, query optimization
- ✅ Usability: Responsive design, dual-theme support, accessible UI
- ✅ Compatibility: Cross-browser support (Chrome, Firefox, Safari, Edge)

**Testing Framework:** Laravel Feature Tests (PHPUnit)
**Execution:** `php artisan test`

---

### 3.3 Exclusions

The following items are **NOT** included in the current scope:

- **Hardware Procurement** - Servers, networking equipment, and infrastructure
- **Cloud Hosting Setup** - AWS/Azure deployment and configuration (instructions provided, but not deployed)
- **Mobile Applications** - Native iOS/Android apps (web interface is mobile-responsive)
- **Third-Party Integrations** - Integration with existing MOD HR systems, Active Directory, or enterprise software
- **Advanced AI Model Training** - Custom ML model development (system uses OpenAI/Anthropic APIs)
- **Biometric Integration** - Fingerprint, facial recognition, or other biometric authentication
- **Video Assessment Features** - Recording and analysis of video interviews
- **Real-Time Collaboration** - Multi-assessor simultaneous scoring interface
- **Ongoing Maintenance** - Post-deployment support beyond 30-day warranty period
- **Custom Report Builder** - Drag-and-drop report design interface (predefined reports provided)
- **SMS Notifications** - SMS alerts (email notifications included)
- **Backup and Disaster Recovery** - Automated backup solutions (manual backup procedures documented)

---

### 3.4 Preliminary Solution Architecture

#### System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                         PRESENTATION LAYER                          │
├─────────────────────────────────────────────────────────────────────┤
│  Web Browser (Chrome, Firefox, Safari, Edge)                       │
│  - Responsive UI (TailwindCSS v4)                                   │
│  - Dual Theme Support (Dark/Light)                                  │
│  - RTL Support for Arabic                                           │
│  - Vite Asset Bundling                                              │
└────────────────────┬────────────────────────────────────────────────┘
                     │ HTTPS
┌────────────────────▼────────────────────────────────────────────────┐
│                      APPLICATION LAYER                              │
├─────────────────────────────────────────────────────────────────────┤
│  Laravel 12 Framework (PHP 8.3+)                                    │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │ CONTROLLERS                                                  │   │
│  │ - AuthController: Authentication & password reset            │   │
│  │ - DashboardController: Role-based dashboards                 │   │
│  │ - AssessmentController: Assessment CRUD                      │   │
│  │ - UserController: User management & import                   │   │
│  │ - LanguageController: Locale management                      │   │
│  │ - PasswordResetRequestController: Reset approvals            │   │
│  │ - AssessorController: Scoring interface                      │   │
│  │ - ManagerController: Team oversight                          │   │
│  │ - SurveyController: Assessment participation                 │   │
│  │ - AIAssistantController: Chat interface                      │   │
│  │ - AssessmentScoringController: AI scoring integration        │   │
│  └─────────────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │ MIDDLEWARE                                                   │   │
│  │ - Authentication (Sanctum)                                   │   │
│  │ - Role-based Authorization                                   │   │
│  │ - Locale Switching (Session-based)                           │   │
│  │ - CSRF Protection                                            │   │
│  └─────────────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │ MODELS (Eloquent ORM)                                        │   │
│  │ - User, PasswordResetRequest                                 │   │
│  │ - Assessment, AssessmentTranslation                          │   │
│  │ - Competency, Language                                       │   │
│  │ - Notification                                               │   │
│  └─────────────────────────────────────────────────────────────┘   │
└────────────────────┬────────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────────┐
│                      BUSINESS LOGIC LAYER                           │
├─────────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │ AI SERVICES                                                  │   │
│  │ - CandidateScoringService: ML-based candidate evaluation     │   │
│  │ - RecommendationEngine: Mission/task matching                │   │
│  │ - QualitativeAnalysis: Text analysis of assessor notes       │   │
│  │ - StrengthsWeaknessesAnalyzer: Competency profiling          │   │
│  └─────────────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │ CORE SERVICES                                                │   │
│  │ - AssessmentService: Assessment lifecycle management         │   │
│  │ - UserImportService: CSV parsing and validation              │   │
│  │ - NotificationService: Multi-channel notifications           │   │
│  │ - ReportingService: Data aggregation and export              │   │
│  │ - TranslationService: Locale content management              │   │
│  └─────────────────────────────────────────────────────────────┘   │
└────────────────────┬────────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────────┐
│                         DATA LAYER                                  │
├─────────────────────────────────────────────────────────────────────┤
│  MySQL Database 8.0+                                                │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │ CORE TABLES                                                  │   │
│  │ - languages: Supported locales                               │   │
│  │ - users, roles_permissions: Authentication & authorization   │   │
│  │ - password_reset_requests: Secure password workflow          │   │
│  └─────────────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │ ASSESSMENT TABLES                                            │   │
│  │ - assessments, assessment_translations                       │   │
│  │ - assessment_templates, assessment_template_translations     │   │
│  │ - assessment_items: Weighted rubric items                    │   │
│  │ - assessment_participants: Enrollment & scoring              │   │
│  │ - assessor_notes: Qualitative feedback                       │   │
│  └─────────────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │ COMPETENCY TABLES                                            │   │
│  │ - competencies, competency_translations                      │   │
│  └─────────────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │ DEVELOPMENT TABLES                                           │   │
│  │ - development_plans, development_plan_translations           │   │
│  │ - development_activities, development_activity_translations  │   │
│  └─────────────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │ REPORTING TABLES                                             │   │
│  │ - assessment_reports: Aggregated results                     │   │
│  │ - report_exports: Export history                             │   │
│  │ - notifications: System messaging                            │   │
│  │ - audit_logs: Activity tracking                              │   │
│  └─────────────────────────────────────────────────────────────┘   │
└────────────────────┬────────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────────┐
│                    EXTERNAL INTEGRATIONS                            │
├─────────────────────────────────────────────────────────────────────┤
│  - OpenAI API / Anthropic Claude API (AI Services)                  │
│  - SMTP Server (Email Notifications)                                │
│  - File Storage (Local/S3) for exports and uploads                  │
└─────────────────────────────────────────────────────────────────────┘
```

#### Component-to-Feature Mapping

| Component | Implements Features |
|-----------|-------------------|
| **AuthController** | #13 Password Reset Workflow |
| **AssessmentController** | #2 Multi-Assessment Type Support, #8 Assessment Templates |
| **AIAssistantController** | #15 AI Chat Assistant |
| **AssessmentScoringController** | #1 AI-Assisted Candidate Scoring, #3 Mission/Task Recommendation |
| **UserController** | #14 User Import/Export, #4 Role-Based Access Control |
| **LanguageController** | #5 Multilingual Support, #18 Language Management |
| **PasswordResetRequestController** | #13 Password Reset Workflow |
| **DashboardController** | #4 Role-Based Access Control |
| **AssessorController** | #6 Competency-Based Assessment, #9 Assessor Notes |
| **ManagerController** | #17 Assessment Participation Tracking |
| **SurveyController** | #17 Assessment Participation Tracking |
| **NotificationService** | #11 Notification System |
| **ReportingService** | #10 Comprehensive Reporting & Exports |
| **AI Services (External)** | #1 AI-Assisted Scoring, #3 Recommendations |
| **Database Layer** | #12 Audit Logs, #6 Competency-Based Assessment, #7 Development Plans |
| **TailwindCSS Theme System** | #16 UAE MOD Branding & Theming |

---

## 4. Timeline & Milestones

| Milestone | Target Date | Status |
|-----------|------------|--------|
| **Requirements Gathering** | November 1-15, 2025 | ✅ Completed |
| **Database Schema Design** | November 16-30, 2025 | ✅ Completed |
| **Core Authentication & User Management** | December 1-7, 2025 | ✅ Completed |
| **Assessment Module Development** | December 8-14, 2025 | ✅ Completed |
| **Multilingual Support Implementation** | December 15-18, 2025 | ✅ Completed |
| **AI Integration (Scoring & Assistant)** | December 19-24, 2025 | ✅ Completed |
| **Password Reset Workflow** | December 25-26, 2025 | ✅ Completed |
| **Development Plans Module** | December 27-29, 2025 | ✅ Completed |
| **UAE MOD Branding & Theming** | December 30, 2025 - January 4, 2026 | ✅ Completed |
| **Testing & QA (Functional)** | January 5-8, 2026 | 🔄 In Progress |
| **Bug Fixes & Refinements** | January 9-12, 2026 | 📅 Scheduled |
| **User Acceptance Testing (UAT)** | January 13-20, 2026 | 📅 Scheduled |
| **Documentation Finalization** | January 21-23, 2026 | 📅 Scheduled |
| **Deployment to Production** | January 24-25, 2026 | 📅 Scheduled |
| **Training & Handover** | January 26-30, 2026 | 📅 Scheduled |

**Total Project Duration:** 12 weeks (November 1, 2025 - January 30, 2026)

---

## 5. Roles & Responsibilities

### Client (UAE Ministry of Defence)
- Provide detailed requirements and use case scenarios
- Review and approve UI/UX designs and branding elements
- Participate in UAT and provide timely feedback
- Approve milestone deliverables
- Provide access to testing environments (if required)
- Designate subject matter experts for domain clarification
- Final sign-off on project completion

### Development Team

**Team Lead / Senior Developer:**
- Overall system architecture and design
- AI integration and services implementation
- Code review and quality assurance
- Technical decision-making

**Backend Developers (Mohammed + Sultan):**
- User and language model/controller development
- Database migrations and Eloquent models
- API endpoint implementation
- Business logic services

**Frontend Developer (Rashed):**
- UI template selection and customization
- Authentication views (login, register, password reset)
- Responsive design implementation
- TailwindCSS theming and dual-mode support

**Documentation Team (Abdurhman + Khlied):**
- Project presentations (Arabic & English)
- Gantt chart creation and maintenance
- Color palette and brand guidelines
- Logo integration and visual assets

### Project Manager
- Sprint planning and progress tracking
- Stakeholder communication
- Risk identification and mitigation
- Timeline and budget management

### QA Engineer
- Test case development
- Functional and non-functional testing
- Bug tracking and verification
- UAT coordination

---

## 6. Assumptions & Constraints

### Assumptions

1. **Client provides timely feedback** on deliverables within 3 business days
2. **UAE MOD branding assets** (logos, color codes) are provided by client
3. **Test data** for UAT will be provided by client or generated using seeders
4. **OpenAI/Anthropic API access** is available with sufficient quota
5. **MySQL database server** is available for development and production
6. **SMTP server** is configured for email notifications
7. **Client has designated stakeholders** available for requirement clarification
8. **Browser compatibility** targets modern browsers (last 2 versions)
9. **User training materials** will be provided in both English and Arabic
10. **Development team has necessary access** to all required tools and environments

### Constraints

**Technology Stack:**
- Backend: Laravel 12 (PHP 8.3+)
- Database: MySQL 8.0+
- Frontend: Blade templates with TailwindCSS v4
- AI: OpenAI GPT-4 or Anthropic Claude API

**Budget Constraints:**
- No budget for proprietary third-party libraries
- AI API costs estimated at $200/month for MVP usage
- Open-source solutions preferred where applicable

**Timeline Constraints:**
- Hard deadline: January 30, 2026 for deployment
- UAT window: 1 week (cannot be extended)
- Development freeze 48 hours before UAT begins

**Resource Constraints:**
- 5-person development team
- Limited to 40 hours/week per developer
- No dedicated DevOps engineer (deployment handled by team)

**Security Constraints:**
- Must comply with UAE data protection regulations
- Role-based access control mandatory
- Password encryption using bcrypt (Laravel default)
- No sensitive data in version control

**Performance Constraints:**
- Page load time < 3 seconds on standard broadband
- Support up to 500 concurrent users (MVP phase)
- Database query optimization for tables with 10,000+ records

**Compatibility Constraints:**
- Desktop browsers: Chrome, Firefox, Safari, Edge (last 2 versions)
- Mobile responsive design (not native apps)
- RTL support for Arabic language
- No Internet Explorer support

---

## 7. Acceptance Criteria

The project deliverables will be accepted upon successful completion of the following criteria:

### Functional Acceptance Criteria

**1. User Authentication & Authorization**
- ✅ Users can register, login, and logout
- ✅ Four distinct roles (Admin, Manager, Assessor, Participant) with appropriate permissions
- ✅ Password reset workflow requires admin approval
- ✅ Temporary passwords expire after 24 hours

**2. Assessment Management**
- ✅ Managers and Admins can create assessments with multilingual translations
- ✅ Assessments support 5 types (psychometric, interview, group exercise, role-play, written)
- ✅ Assessment templates can be reused
- ✅ Participants can view and complete assigned assessments

**3. AI Integration**
- ✅ AI scoring service analyzes competency-based assessments
- ✅ AI generates strengths/weaknesses reports
- ✅ AI assistant responds to user queries
- ✅ Mission/task recommendations based on candidate profiles

**4. Multilingual Support**
- ✅ Complete UI available in English and Arabic
- ✅ Locale switching persists across sessions
- ✅ RTL layout for Arabic content
- ✅ Assessment content supports translations

**5. User Management**
- ✅ Admin can create, edit, and delete users
- ✅ Bulk user import via CSV
- ✅ Password generation and export for participants
- ✅ User list with filtering and search

**6. Development Plans**
- ✅ Development plans linked to assessment results
- ✅ Activities trackable with progress status
- ✅ Multilingual plan and activity translations

**7. Notifications**
- ✅ Real-time notifications for password reset requests
- ✅ Notification system displays unread count
- ✅ Notifications mark as read functionality

**8. Reporting**
- ✅ Dashboard displays key statistics (users, assessments, plans, notifications)
- ✅ Assessment reports accessible by role
- ✅ Export functionality for user data (CSV)

**9. Theming**
- ✅ UAE MOD branding (logo, colors) applied
- ✅ Dual-theme support (dark/light modes)
- ✅ Theme preference persists across sessions
- ✅ Consistent styling across all pages

### Non-Functional Acceptance Criteria

**Performance:**
- ✅ Dashboard loads in < 2 seconds
- ✅ Database queries optimized with indexes
- ✅ Asset bundling and minification via Vite

**Security:**
- ✅ All passwords encrypted with bcrypt
- ✅ CSRF protection enabled on forms
- ✅ SQL injection prevention via Eloquent ORM
- ✅ Role-based middleware on protected routes

**Usability:**
- ✅ Responsive design works on mobile, tablet, desktop
- ✅ Form validation with clear error messages
- ✅ Accessible navigation and keyboard support
- ✅ Consistent UI patterns across all pages

**Maintainability:**
- ✅ Code follows Laravel best practices
- ✅ Models use Eloquent relationships
- ✅ Controllers follow single responsibility principle
- ✅ Migrations version-controlled and reversible

**Testing:**
- ✅ All feature tests pass (`php artisan test`)
- ✅ Authentication flows tested
- ✅ Role-based access control tested
- ✅ Assessment creation tested

### User Acceptance Testing (UAT) Success Criteria

**UAT will be considered successful when:**
1. All critical user journeys can be completed without errors
2. Client stakeholders approve UI/UX design and branding
3. Multilingual content displays correctly in both languages
4. AI recommendations demonstrate reasonable accuracy (validated by SMEs)
5. No critical or high-severity bugs remain unresolved
6. System performance meets specified constraints
7. All role-based permissions function as intended
8. Client signs UAT approval document

**UAT Test Scenarios (Minimum):**
- Admin creates users and assigns roles
- Manager creates multilingual assessment and assigns participants
- Assessor scores participant and adds qualitative notes
- Participant completes assessment
- AI generates scoring report with recommendations
- Admin approves password reset request
- Language switching works across all pages
- Reports export successfully
- Development plan created based on assessment results

---

## 8. Signatures

### Client Approval

| Name | Title | Signature | Date |
|------|-------|-----------|------|
| __________________ | UAE MOD Representative | __________________ | __________ |
| __________________ | Project Sponsor | __________________ | __________ |

### Development Team Acceptance

| Name | Title | Signature | Date |
|------|-------|-----------|------|
| __________________ | Project Manager | __________________ | __________ |
| __________________ | Technical Lead | __________________ | __________ |

---

## Appendix A: Technology Stack Details

**Backend Framework:** Laravel 12.0
**PHP Version:** 8.3+
**Database:** MySQL 8.0+
**Frontend:** Blade Templates + TailwindCSS v4
**Asset Bundler:** Vite 7.2.2
**AI Integration:** OpenAI API / Anthropic Claude API
**Authentication:** Laravel Sanctum
**Session Driver:** Database
**Cache Driver:** File/Redis (configurable)
**Queue Driver:** Database/Redis (configurable)

**Development Tools:**
- Composer (PHP dependency management)
- NPM (JavaScript dependency management)
- Git (version control)
- PHPUnit (testing framework)

---

## Appendix B: Database Schema Summary

**Total Tables:** 20+

**Core Tables:**
- languages (2 records: en, ar)
- users (roles: super_admin, admin, manager, assessor, participant)
- roles_permissions
- sessions
- cache
- password_reset_requests

**Assessment Tables:**
- assessments
- assessment_translations
- assessment_templates
- assessment_template_translations
- assessment_items
- assessment_participants
- assessor_notes

**Competency Tables:**
- competencies
- competency_translations

**Development Tables:**
- development_plans
- development_plan_translations
- development_activities
- development_activity_translations

**Reporting Tables:**
- assessment_reports
- report_exports
- notifications
- audit_logs

---

## Appendix C: API Endpoints Summary

**Authentication:**
- POST /login
- POST /register
- POST /logout
- POST /forgot-password
- POST /reset-password/{token}

**Dashboards:**
- GET /dashboard/admin
- GET /dashboard/manager
- GET /dashboard/assessor
- GET /dashboard/participant

**User Management:**
- GET /admin/users
- POST /admin/users
- GET /admin/users/{id}
- PUT /admin/users/{id}
- DELETE /admin/users/{id}
- POST /admin/users/import
- GET /admin/users/export

**Assessments:**
- GET /assessments/create
- POST /assessments
- GET /assessments/{id}/take
- GET /assessments/{id}/review
- GET /assessments/{id}/report

**Languages:**
- GET /admin/languages
- POST /admin/languages
- PUT /admin/languages/{id}
- DELETE /admin/languages/{id}

**Password Resets:**
- GET /admin/password-resets
- POST /admin/password-resets/{id}/approve
- POST /admin/password-resets/{id}/decline

**AI Services:**
- POST /ai-assistant/chat
- POST /ai-demo/analyze-strengths
- POST /ai-demo/analyze-qualitative

**Locale:**
- POST /locale/switch

---

## Appendix D: Default Credentials

**Super Admin:**
- Email: `superadmin@example.com`
- Password: `Admin@12345`

*(Change immediately after first login in production)*

---

## Revision History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | January 5, 2026 | Development Team | Initial SOW document |

---

**Document End**
