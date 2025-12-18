-- =============================================
-- Assessment System - Full SQL Schema (MySQL 8)
-- Charset: utf8mb4 / Collation: utf8mb4_unicode_ci
-- =============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------
-- DROP (safe re-run)
-- -------------------------
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS report_exports;
DROP TABLE IF EXISTS assessment_reports;
DROP TABLE IF EXISTS assessor_notes;
DROP TABLE IF EXISTS assessment_participants;
DROP TABLE IF EXISTS user_answers;
DROP TABLE IF EXISTS question_option_translations;
DROP TABLE IF EXISTS question_options;
DROP TABLE IF EXISTS question_media;
DROP TABLE IF EXISTS question_translations;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS assessment_items;
DROP TABLE IF EXISTS competency_translations;
DROP TABLE IF EXISTS competencies;
DROP TABLE IF EXISTS assessment_template_translations;
DROP TABLE IF EXISTS assessment_templates;
DROP TABLE IF EXISTS assessment_translations;
DROP TABLE IF EXISTS assessments;
DROP TABLE IF EXISTS development_activity_translations;
DROP TABLE IF EXISTS development_activities;
DROP TABLE IF EXISTS development_plan_translations;
DROP TABLE IF EXISTS development_plans;
DROP TABLE IF EXISTS roles_permissions;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS languages;

SET FOREIGN_KEY_CHECKS = 1;

-- -------------------------
-- LANGUAGES
-- -------------------------
CREATE TABLE languages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(10) NOT NULL UNIQUE,
  name VARCHAR(50) NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------
-- USERS (Laravel-auth ready)
-- -------------------------
CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  email_verified_at TIMESTAMP NULL,
  password VARCHAR(255) NOT NULL,
  remember_token VARCHAR(100) NULL,
  full_name VARCHAR(150) NULL,
  rank VARCHAR(100) NULL,
  department VARCHAR(100) NULL,
  role ENUM('admin','assessor','participant','manager') NOT NULL,
  language_pref BIGINT UNSIGNED NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_users_language FOREIGN KEY (language_pref)
    REFERENCES languages(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_reset_tokens (
  email VARCHAR(150) PRIMARY KEY,
  token VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sessions (
  id VARCHAR(255) PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  payload LONGTEXT NOT NULL,
  last_activity INT NOT NULL,
  INDEX idx_sessions_user_id (user_id),
  CONSTRAINT fk_sessions_user FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE roles_permissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role ENUM('admin','assessor','participant','manager') NOT NULL,
  module_name VARCHAR(100) NOT NULL,
  can_view BOOLEAN DEFAULT FALSE,
  can_edit BOOLEAN DEFAULT FALSE,
  can_delete BOOLEAN DEFAULT FALSE,
  can_export BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX idx_role_module (role, module_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------
-- COMPETENCIES (must come before assessment_items)
-- -------------------------
CREATE TABLE competencies (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category ENUM('leadership','behavioral','technical') NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE competency_translations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  competency_id BIGINT UNSIGNED NOT NULL,
  language_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_competency_language (competency_id, language_id),
  CONSTRAINT fk_ct_competency FOREIGN KEY (competency_id)
    REFERENCES competencies(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_ct_language FOREIGN KEY (language_id)
    REFERENCES languages(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------
-- ASSESSMENTS
-- -------------------------
CREATE TABLE assessments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('psychometric','interview','group_exercise','written_test','role_play','committee_interview','other') NOT NULL,
  created_by BIGINT UNSIGNED NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  status ENUM('draft','active','closed') DEFAULT 'draft',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_assessments_created_by FOREIGN KEY (created_by)
    REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE assessment_translations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  assessment_id BIGINT UNSIGNED NOT NULL,
  language_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NULL,
  description TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_assessment_language (assessment_id, language_id),
  CONSTRAINT fk_at_assessment FOREIGN KEY (assessment_id)
    REFERENCES assessments(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_at_language FOREIGN KEY (language_id)
    REFERENCES languages(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE assessment_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  assessment_id BIGINT UNSIGNED NOT NULL,
  competency_id BIGINT UNSIGNED NOT NULL,
  max_score DECIMAL(8,2) DEFAULT 5.00,
  weight DECIMAL(6,4) DEFAULT 1.0000,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_assessment_competency (assessment_id, competency_id),
  CONSTRAINT fk_ai_assessment FOREIGN KEY (assessment_id)
    REFERENCES assessments(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_ai_competency FOREIGN KEY (competency_id)
    REFERENCES competencies(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------
-- ASSESSMENT TEMPLATES
-- -------------------------
CREATE TABLE assessment_templates (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('psychometric','interview','group_exercise','other') NOT NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_templates_user FOREIGN KEY (created_by)
    REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE assessment_template_translations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  template_id BIGINT UNSIGNED NOT NULL,
  language_id BIGINT UNSIGNED NOT NULL,
  template_name VARCHAR(255) NULL,
  description TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_template_language (template_id, language_id),
  CONSTRAINT fk_att_template FOREIGN KEY (template_id)
    REFERENCES assessment_templates(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_att_language FOREIGN KEY (language_id)
    REFERENCES languages(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------
-- QUESTIONS
-- -------------------------
CREATE TABLE questions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  template_id BIGINT UNSIGNED NOT NULL,
  competency_id BIGINT UNSIGNED NULL,
  question_type ENUM('single_choice','multiple_choice','scale','essay','numeric','file_upload','audio_response') DEFAULT 'single_choice',
  `order` INT DEFAULT 1,
  is_required BOOLEAN DEFAULT TRUE,
  max_score DECIMAL(8,2) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX idx_q_template (template_id),
  INDEX idx_q_competency (competency_id),
  CONSTRAINT fk_q_template FOREIGN KEY (template_id)
    REFERENCES assessment_templates(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_q_competency FOREIGN KEY (competency_id)
    REFERENCES competencies(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE question_translations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question_id BIGINT UNSIGNED NOT NULL,
  language_id BIGINT UNSIGNED NOT NULL,
  question_text TEXT NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_question_language (question_id, language_id),
  CONSTRAINT fk_qt_question FOREIGN KEY (question_id)
    REFERENCES questions(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_qt_language FOREIGN KEY (language_id)
    REFERENCES languages(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE question_media (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question_id BIGINT UNSIGNED NOT NULL,
  media_type ENUM('image','video','audio') NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  caption VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX idx_qm_question (question_id),
  CONSTRAINT fk_qm_question FOREIGN KEY (question_id)
    REFERENCES questions(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE question_options (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question_id BIGINT UNSIGNED NOT NULL,
  option_value DECIMAL(8,2) NULL,
  option_weight DECIMAL(6,4) NULL,
  option_percentage DECIMAL(6,2) NULL,
  is_correct BOOLEAN DEFAULT FALSE,
  `order` INT DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX idx_qo_question (question_id),
  CONSTRAINT fk_qo_question FOREIGN KEY (question_id)
    REFERENCES questions(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE question_option_translations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  option_id BIGINT UNSIGNED NOT NULL,
  language_id BIGINT UNSIGNED NOT NULL,
  option_text VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_option_language (option_id, language_id),
  CONSTRAINT fk_qot_option FOREIGN KEY (option_id)
    REFERENCES question_options(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_qot_language FOREIGN KEY (language_id)
    REFERENCES languages(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------
-- PARTICIPANTS, NOTES, ANSWERS & REPORTS
-- -------------------------
CREATE TABLE assessment_participants (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  assessment_id BIGINT UNSIGNED NOT NULL,
  participant_id BIGINT UNSIGNED NOT NULL,
  status ENUM('invited','in_progress','completed','withdrawn') DEFAULT 'invited',
  score DECIMAL(8,2) NULL,
  feedback TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_assessment_participant (assessment_id, participant_id),
  CONSTRAINT fk_ap_assessment FOREIGN KEY (assessment_id)
    REFERENCES assessments(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_ap_user FOREIGN KEY (participant_id)
    REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_answers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  assessment_id BIGINT UNSIGNED NOT NULL,
  question_id BIGINT UNSIGNED NOT NULL,
  selected_option_ids JSON NULL,
  answer_text TEXT NULL,
  file_path VARCHAR(255) NULL,
  score_value DECIMAL(8,2) NULL,
  score_weighted DECIMAL(8,2) NULL,
  score_percentage DECIMAL(6,2) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX idx_ua_user (user_id),
  INDEX idx_ua_assessment (assessment_id),
  INDEX idx_ua_question (question_id),
  CONSTRAINT fk_ua_user FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_ua_assessment FOREIGN KEY (assessment_id)
    REFERENCES assessments(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_ua_question FOREIGN KEY (question_id)
    REFERENCES questions(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE assessment_reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  participant_id BIGINT UNSIGNED NOT NULL,
  assessment_id BIGINT UNSIGNED NOT NULL,
  overall_score DECIMAL(8,2) NULL,
  strengths TEXT NULL,
  weaknesses TEXT NULL,
  recommendations TEXT NULL,
  generated_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX idx_ar_participant (participant_id),
  INDEX idx_ar_assessment (assessment_id),
  CONSTRAINT fk_ar_participant FOREIGN KEY (participant_id)
    REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_ar_assessment FOREIGN KEY (assessment_id)
    REFERENCES assessments(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_ar_generator FOREIGN KEY (generated_by)
    REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE report_exports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  report_id BIGINT UNSIGNED NOT NULL,
  format ENUM('pdf','xlsx','csv','docx') NOT NULL,
  exported_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX idx_re_report (report_id),
  CONSTRAINT fk_re_report FOREIGN KEY (report_id)
    REFERENCES assessment_reports(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_re_user FOREIGN KEY (exported_by)
    REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------
-- DEVELOPMENT PLANS & ACTIVITIES
-- -------------------------
CREATE TABLE development_plans (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  participant_id BIGINT UNSIGNED NOT NULL,
  plan_type ENUM('idp','training','coaching','other') DEFAULT 'idp',
  start_date DATE NULL,
  end_date DATE NULL,
  status ENUM('draft','active','completed','on_hold') DEFAULT 'draft',
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX idx_dp_participant (participant_id),
  CONSTRAINT fk_dp_participant FOREIGN KEY (participant_id)
    REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_dp_creator FOREIGN KEY (created_by)
    REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE development_plan_translations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  plan_id BIGINT UNSIGNED NOT NULL,
  language_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_dp_language (plan_id, language_id),
  CONSTRAINT fk_dpt_plan FOREIGN KEY (plan_id)
    REFERENCES development_plans(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_dpt_language FOREIGN KEY (language_id)
    REFERENCES languages(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE development_activities (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  plan_id BIGINT UNSIGNED NOT NULL,
  activity_type ENUM('course','workshop','project','coaching','reading','other') DEFAULT 'other',
  completion_status ENUM('not_started','in_progress','completed','blocked') DEFAULT 'not_started',
  start_date DATE NULL,
  end_date DATE NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX idx_da_plan (plan_id),
  CONSTRAINT fk_da_plan FOREIGN KEY (plan_id)
    REFERENCES development_plans(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE development_activity_translations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  activity_id BIGINT UNSIGNED NOT NULL,
  language_id BIGINT UNSIGNED NOT NULL,
  activity_name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_dat_language (activity_id, language_id),
  CONSTRAINT fk_dat_activity FOREIGN KEY (activity_id)
    REFERENCES development_activities(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_dat_language FOREIGN KEY (language_id)
    REFERENCES languages(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------
-- NOTIFICATIONS & AUDIT LOGS
-- -------------------------
CREATE TABLE notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  message TEXT NOT NULL,
  notification_type ENUM('system','assessment','reminder','report') DEFAULT 'system',
  sent_at TIMESTAMP NULL,
  is_read BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX idx_notif_user (user_id),
  CONSTRAINT fk_notif_user FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  action VARCHAR(100) NOT NULL,
  module VARCHAR(100) NOT NULL,
  details JSON NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX idx_audit_user (user_id),
  INDEX idx_audit_module (module),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
