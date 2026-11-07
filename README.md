# STIC Info 🎓

**ប្រព័ន្ធព័ត៌មាន និងកាលវិភាគវិទ្យាស្ថានពហុបច្ចេកទេសភូមិភាគតេជោសែនសៀមរាប**  
*School Information and Timetable Management System for Techo Sen Regional Polytechnic Institute of Siem Reap (STIC)*

---

## 📌 Project Overview
STIC Info is a production-oriented web management platform built with **Laravel 13**, **Tailwind CSS**, and **Blade Templates**. It centralizes academic scheduling, departmental organization, student administration, and targeted campus announcements.

- **Primary Scope:** Web Application only (responsive across Desktop, Laptop, Tablet, Mobile).
- **Backend Framework:** Laravel 13 (PHP 8.2+)
- **Database:** SQLite (Local Development) / PostgreSQL (Production)
- **Frontend:** Blade Templates + Tailwind CSS + Vite
- **Localization:** Bilingual (Khmer `km` & English `en`)
- **Official Timezone:** `Asia/Phnom_Penh`

---

## 🚀 Getting Started

### 1. Requirements
- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ & npm

### 2. Setup Instructions
```bash
# Clone the repository
git clone <repo-url>
cd stic_Info

# Install backend dependencies
composer install

# Prepare environment file
cp .env.example .env
php artisan key:generate

# Install and build frontend assets
npm install
npm run build

# Run database migrations and seeders (local only)
php artisan migrate --seed

# Start development server
php artisan serve
```

### 3. Create Production Administrator
For secure environments, create an admin account directly from CLI:
```bash
php artisan stic:create-admin
```

---

## 🏛️ Governance & Development Rules
Refer to [`AGENTS.md`](./AGENTS.md) for strict branching, database safety rules, audit logging, student privacy rules, and Definition of Done (DoD).
