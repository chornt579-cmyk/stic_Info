# STIC INFO — AI AGENT & DEVELOPER GOVERNANCE MANUAL

## 1. PROJECT CHARTER & SCOPE
- **Project Name:** STIC Info — School Information and Timetable Management System
- **Institution:** Techo Sen Regional Polytechnic Institute of Siem Reap (STIC)
- **Primary Scope:** **WEB APPLICATION ONLY**. Desktop (1440px), Laptop (1280px), Tablet (768px), and Mobile (375px) responsive browser support.
  - Zero horizontal overflow allowed on mobile viewports (375px).
- **Strict Boundary:** Do NOT build Android, iOS, or Flutter code. Keep backend APIs ready via Laravel Sanctum for future mobile consumption, but do not build the mobile app now.
- **Framework & Version Lifecycle:**
  - Standardized on **Laravel 13** (PHP 8.2+).
  - Note: Laravel 11 reached End of Security Support (March 12, 2026). Upgrading to/maintaining Laravel 13 is mandatory for LTS security.

---

## 2. STRICT GIT WORKFLOW
1. **Branching per Phase:**
   - Every phase MUST have its own dedicated branch (`phase-1-foundation`, `phase-2-auth`, `phase-3-admin`, etc.).
   - Never develop directly on `main`.
   - Never merge into `main` without explicit user sign-off and approval.
2. **Commit Hygiene & Pre-flight Inspection:**
   - Small, atomic, meaningful commits (e.g., `feat(auth): add rate limiting to login endpoint`).
   - Always run and review `git status` before committing.
   - Never use `git push --force`.
3. **Zero Secret Exposure:**
   - Never commit `.env`, credentials, or database backups.
   - Verify `.gitignore` before every commit.
   - Run secret checks before committing.

---

## 3. DATABASE SAFETY, CONCURRENCY & MIGRATION RULES
1. **Pre-Migration Backup Protocol:**
   - Prior to executing migrations on environments with existing data, a physical/logical backup must be performed outside the git repository:
     ```bash
     # Example for PostgreSQL (stored outside repository, e.g. /var/backups/stic/):
     pg_dump -Fc -h $DB_HOST -U $DB_USERNAME $DB_DATABASE > /var/backups/stic/pre_migration_$(date +%Y%m%d_%H%M%S).dump
     ```
   - **STRICT FORBIDDEN:** Never store database backups inside the git repository.
2. **Forbidden Destructive Commands:**
   - **NEVER** run `php artisan migrate:fresh`, `php artisan migrate:refresh`, or `php artisan db:wipe` on any database containing user or production data.
3. **Additive & Backward-Compatible Migrations:**
   - Write safe migrations with proper `down` rollback methods.
4. **Soft Deletes & Unique Email Conflict Prevention:**
   - Apply `SoftDeletes` on core entities (`users`, `departments`, `classes`, `schedules`, `announcements`).
   - **Crucial Rule on User Email Unique Constraint:** Standard unique indexes cause collisions when a soft-deleted user's email is re-registered.
     - For PostgreSQL: Enforce partial unique index:
       ```sql
       CREATE UNIQUE INDEX users_email_unique ON users (email) WHERE deleted_at IS NULL;
       ```
     - For SQLite: Validate uniqueness with `Rule::unique('users')->whereNull('deleted_at')` in Form Requests.
   - Soft deletes must **NEVER** be applied to `audit_logs` (logs are permanent and immutable).
5. **Timetable Architecture & Conflict Prevention:**
   - **Full Week Coverage (Monday to Sunday):** Timetable scheduling MUST fully support all 7 days of the week (`monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday`) to cover both standard weekday curriculum and weekend technical courses.
   - **Room Entity:** Use `room_id` (foreign key to `rooms` table) instead of a plain string.
   - **Conflict Lookup Composite Indexes:**
     - `schedules (teacher_id, day_of_week)`
     - `schedules (class_id, day_of_week)`
     - `schedules (room_id, day_of_week)`
   - **Mathematical Overlap Rule:**
     $$\text{new\_start} < \text{existing\_end} \quad \text{AND} \quad \text{new\_end} > \text{existing\_start}$$
     Adjacent time slots (e.g. 08:00–10:00 and 10:00–12:00) MUST be permitted.
   - **PostgreSQL GiST Exclusion Constraint:**
     - For PostgreSQL production, enforce database-level exclusion constraint using `btree_gist`:
       ```sql
       CREATE EXTENSION IF NOT EXISTS btree_gist;
       ALTER TABLE schedules ADD CONSTRAINT no_teacher_overlap EXCLUDE USING gist (
           teacher_id WITH =,
           day_of_week WITH =,
           tsrange(start_time, end_time) WITH &&
       );
       ```
     - Production conflict tests must run against real PostgreSQL.
6. **Concurrency Protection:**
   - Teacher approval/rejection and schedule reservations MUST be wrapped in a database transaction (`DB::transaction`) and use pessimistic row locking (`lockForUpdate()`) to prevent race conditions.

---

## 4. AUTHENTICATION, SEEDERS & APPLICATION SECURITY
1. **Seeder Environment Boundary:**
   - Default seeders creating test accounts with known passwords (e.g. `password123`) MUST be gated with:
     ```php
     if (!app()->environment(['local', 'testing'])) {
         $this->command->warn('Test seeders restricted to local/testing environments.');
         return;
     }
     ```
   - For Production, use the dedicated command: `php artisan stic:create-admin` which prompts securely for credentials.
2. **Mass-Assignment Guard:**
   - **Strictly FORBID:** `$guarded = []`.
   - Every model MUST have an explicit `$fillable` whitelist.
   - Sensitive fields (`role`, `status`, `approved_at`, `is_admin`, `email_verified_at`) must NEVER be in `$fillable`. Assign them explicitly or via dedicated state methods (`$user->approve()`, `$user->reject()`).
3. **Account Status Verification:**
   - Supported statuses: `active`, `pending`, `rejected`, `suspended`.
   - Middleware MUST strictly block `pending`, `rejected`, and `suspended` accounts from accessing authenticated application resources.
4. **Admin 2FA Readiness:**
   - Prepare architectural hooks for Two-Factor Authentication (TOTP / OTP) for Administrator accounts.
5. **Session & Password Security:**
   - When a user changes their password:
     - Invalidate all other active sessions: `Auth::logoutOtherDevices($newPassword);`
     - Revoke all personal access tokens: `$user->tokens()->delete();`
6. **Rate Limiting (Throttling):**
   - Enforce strict throttling on sensitive endpoints: login (5 attempts per minute), registration, password reset, and announcement broadcasts (max 10 per hour).
7. **Telegram Integration Security:**
   - Telegram messages MUST be processed via Laravel Queue Jobs with retry/backoff policies.
   - Telegram Bot Token and Group/Channel IDs must be loaded solely from `.env` and `config/services.php`. NEVER accept Telegram destination IDs from user form input.
   - **PII Prohibition:** NEVER send student/teacher phone numbers, emails, passwords, or personal credentials into Telegram group chats.
8. **Security Headers:**
   - Enforce HTTP security headers:
     - `X-Frame-Options: SAMEORIGIN`
     - `X-Content-Type-Options: nosniff`
     - `Referrer-Policy: strict-origin-when-cross-origin`
     - `Strict-Transport-Security: max-age=31536000; includeSubDomains` (HTTPS only)
     - `Content-Security-Policy`: Start in report-only if necessary, then enforce with whitelisted origins (Google Fonts).

---

## 5. AUDIT LOGGING ARCHITECTURE
1. **Immutable Log Table Schema:**
   - `audit_logs (id, user_id, action, model_type, model_id, ip_address, user_agent, details [JSON], created_at)`
2. **Database-Level Immutability:**
   - The application database user must ONLY have `INSERT` and `SELECT` privileges on `audit_logs` (NO `UPDATE`, NO `DELETE`).
   - If single DB user is used, install a database trigger preventing `UPDATE` and `DELETE` on `audit_logs`.
3. **Mandatory Audit Events:**
   - Failed login attempts
   - Password changes & 2FA modifications
   - Teacher approval, rejection, or suspension
   - Role and status changes
   - Record deletions (schedules, classes, announcements, users)
   - Unauthorized access attempts (403 forbidden)
   - Bulk data exports
4. **Sanitization Guard:**
   - NEVER log passwords, password confirmations, remember tokens, or sensitive API keys inside `details`.

---

## 6. DATA PRIVACY, RETENTION & PERFORMANCE
1. **Student Data Privacy:**
   - Student phone numbers and emails must never be displayed publicly or to other students.
   - Graduated or inactive student accounts must be cleanly deactivated/archived.
2. **Data Retention Standards:**
   - Audit logs retained for minimum 90 days (configurable via `AUDIT_LOG_RETENTION_DAYS`).
   - Database backups retained: daily for 30 days, monthly for 12 months.
3. **Query Performance (N+1 Prevention):**
   - Mandatory eager loading (`with()`) on all relational queries.
   - Mandatory pagination on all administrative and listing views.
4. **Accessibility (WCAG AA):**
   - Accessible color contrast, semantic HTML, explicit `<label>` elements for form inputs, keyboard focusable controls, and image `alt` attributes.

---

## 7. LOCALIZATION & REGIONAL STANDARDS
1. **Official Timezone:** `Asia/Phnom_Penh`
2. **No Hardcoded Strings:**
   - All UI labels, notices, and error messages must use Laravel localization: `__('messages.key')` or `@lang('messages.key')`.
   - Maintain full bilingual dictionaries: `lang/km/messages.php` and `lang/en/messages.php`.
3. **Khmer Typography:**
   - Primary font: "Noto Sans Khmer" with appropriate line-height (1.6 to 1.8) for comfortable Khmer readability.

---

## 8. INCIDENT RESPONSE PROTOCOL
In the event of a credential, secret, or token leak:
1. Revoke and regenerate the compromised key immediately (e.g., Telegram Bot Token, `APP_KEY`, database credentials).
2. If `APP_KEY` is rotated, re-encrypt sensitive encrypted fields and flush active sessions.
3. Inspect `audit_logs` for suspicious IP addresses, actions, or data exports during the exposure window.
4. Force global logout across all user accounts.

---

## 9. DEFINITION OF DONE (DoD)
Before completing any phase and requesting user sign-off:
1. [ ] Automated tests for the phase pass 100%.
2. [ ] Zero broken routes or missing Blade templates.
3. [ ] No `.env`, secrets, or database dumps committed.
4. [ ] Code changes are committed cleanly to the dedicated phase branch.
5. [ ] Dependency security audits pass: `composer audit` and `npm audit` report 0 vulnerabilities.
6. [ ] Mobile responsiveness verified at 375px with zero horizontal scrolling.
7. [ ] Accurate, honest report of implemented features and any unresolved risks.
8. [ ] **Golden Rule:** "ប្រសិនបើមិនប្រាកដ ឬខ្វះព័ត៌មាន សូមសួរមុន កុំស្មាន / If unsure or lacking information, ask first, never guess."
9. [ ] STOP and wait for explicit user approval before proceeding to the next phase.

