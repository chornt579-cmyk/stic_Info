# STIC INFO — AI AGENT & DEVELOPER GOVERNANCE MANUAL

## 1. PROJECT CHARTER & SCOPE
- **Project Name:** STIC Info — School Information and Timetable Management System
- **Institution:** Techo Sen Regional Polytechnic Institute of Siem Reap (STIC)
- **Primary Scope:** **WEB APPLICATION ONLY**. Desktop (1440px), Laptop (1280px), Tablet (768px), and Mobile (375px) responsive browser support.
- **Strict Boundary:** Do NOT build Android, iOS, or Flutter code. Keep backend APIs ready via Laravel Sanctum for future mobile consumption, but do not build the mobile app now.

---

## 2. STRICT GIT WORKFLOW
1. **Branching per Phase:**
   - Every phase MUST have its own dedicated branch (e.g. `phase-1-foundation`, `phase-2-auth`, `phase-3-admin`, etc.).
   - Never develop directly on `main`.
2. **Commit Hygiene:**
   - Small, atomic, meaningful commits (e.g., `feat(auth): add rate limiting to login endpoint`).
3. **No Force Push:**
   - Never use `git push --force`.
4. **Zero Secret Exposure:**
   - Never commit `.env` or any production secrets. Verify `.gitignore` before every commit.

---

## 3. DATABASE SAFETY & MIGRATION RULES
1. **Pre-Migration Backup:**
   - Always verify or create a database backup before executing any migrations.
2. **Forbidden Destructive Commands:**
   - **NEVER** run `php artisan migrate:fresh`, `php artisan migrate:refresh`, or `php artisan db:wipe` on any database containing user or production data.
3. **Additive & Backward-Compatible Migrations:**
   - Write safe migrations with proper down methods.
4. **Soft Deletes:**
   - Apply soft deletes (`SoftDeletes`) on core entities (`users`, `departments`, `classes`, `schedules`, `announcements`).
5. **Timetable Conflict Rule:**
   - Never rely on simplistic unique constraints that block multiple subjects per shift.
   - Enforce exact interval overlap validation:
     $$\text{new\_start} < \text{existing\_end} \quad \text{AND} \quad \text{new\_end} > \text{existing\_start}$$
   - Adjacent slots (e.g. 08:00–10:00 and 10:00–12:00) MUST be allowed.

---

## 4. AUTHENTICATION, SEEDERS & SECURITY
1. **Seeder Environment Boundary:**
   - Default seeders creating test accounts with known passwords (e.g. `password123`) MUST be gated with:
     ```php
     if (!app()->environment(['local', 'testing'])) {
         return;
     }
     ```
   - For Production, provide a secure command: `php artisan stic:create-admin` which prompts securely for Admin credentials.
2. **Mass-Assignment Guard:**
   - NEVER expose sensitive fields (`role`, `status`, `approved_at`, `is_admin`) in generic `$fillable` arrays on the User model. Use dedicated methods, Form Requests, or explicit assignment.
3. **Account Status Verification:**
   - Valid statuses: `active`, `pending`, `rejected`, `suspended`.
   - Middleware MUST strictly block `pending`, `rejected`, and `suspended` accounts from accessing authenticated application resources.
4. **Admin 2FA Readiness:**
   - Prepare architectural hooks for Two-Factor Authentication (TOTP / OTP) for Administrator accounts.
5. **Rate Limiting:**
   - Enforce strict throttling on sensitive endpoints: login, registration, password reset, and announcement broadcasts.
6. **Telegram Integration Security:**
   - Telegram Bot Token and Group/Channel IDs must be loaded solely from `.env` and `config/services.php`. NEVER accept Telegram IDs from unvalidated user input.

---

## 5. AUDIT LOGGING ARCHITECTURE
1. **Immutable Log Table:**
   - Schema: `audit_logs (id, user_id, action, model_type, model_id, ip_address, user_agent, details, created_at)`
2. **Mandatory Audit Events:**
   - Failed login attempts
   - Teacher approval or rejection
   - Role and status modifications
   - Record deletions (schedules, classes, announcements, users)
   - Unauthorized access attempts (403 forbidden)
3. **Immutability:**
   - Normal users and application routes cannot edit, truncate, or delete audit logs.

---

## 6. DATA PRIVACY & ACCESSIBILITY
1. **Student Data Privacy:**
   - Student phone numbers and emails must never be displayed publicly or to other students.
   - Graduated or inactive students must be deactivated or archived cleanly.
2. **Query Performance (N+1 Prevention):**
   - Mandatory eager loading (`with()`) on all relational queries.
   - Mandatory pagination on all administrative and listing views.
   - Proper composite indexes on `schedules (day_of_week, start_time, end_time, room)`.
3. **Accessibility (WCAG AA):**
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

## 8. DEFINITION OF DONE (DoD)
Before completing any phase and requesting user sign-off:
1. [ ] Automated tests for the phase pass 100%.
2. [ ] Zero broken routes or missing Blade templates.
3. [ ] No `.env` or sensitive credentials committed.
4. [ ] Code changes are committed cleanly to the dedicated phase branch.
5. [ ] Accurate, honest report of implemented features and any unresolved risks.
6. [ ] **Golden Rule:** "ប្រសិនបើមិនប្រាកដ ឬខ្វះព័ត៌មាន សូមសួរមុន កុំស្មាន" (If unsure or lacking info, ask first, never guess).
7. [ ] STOP and wait for explicit user approval before proceeding to the next phase.
