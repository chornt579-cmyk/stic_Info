# STIC Info 🎓

**ប្រព័ន្ធព័ត៌មាន និងកាលវិភាគ STIC Siem Reap Polytechnic Institute**  
School information & schedule management system for students, teachers, and administrators.

---

## Overview

STIC Info is a web application built for **STIC Siem Reap** that centralises scheduling and announcements for all three user groups:

| Role | Khmer | Capabilities |
|------|-------|-------------|
| **Student** | សិស្ស | View today's & weekly schedule, receive announcements |
| **Teacher** | គ្រូ | View teaching schedule, send class announcements |
| **Admin** | អ្នកគ្រប់គ្រង | Full CRUD: departments, classes, users, schedules, announcements |

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML5, CSS3, Vanilla JavaScript (ES6+) |
| Fonts | Noto Sans Khmer + Roboto (Google Fonts) |
| Backend | Firebase Firestore (NoSQL) |
| Auth | Firebase Authentication (Email/Password) |
| Hosting | Firebase Hosting |
| Functions | Firebase Cloud Functions (Node.js 18) |
| Notifications | Telegram Bot API |

---

## Key Features

- 📅 **Real-time schedule** — Today's class, teacher, floor/room, time
- 🗓 **Weekly schedule** — Full week view grouped by day
- 📢 **Smart announcements** — Targeted by class, department, role
- 🔒 **Role-based access** — Firestore Security Rules enforce visibility
- ⚠ **Conflict detection** — Prevents double-booking of teacher/class/room
- 📱 **Telegram Bot** — Push notifications for new announcements
- 🌐 **Bilingual** — Khmer + English throughout
- 🛡 **XSS Protection** — All HTML output escaped

---

## Quick Start

```bash
# 1. Install Firebase CLI
npm install -g firebase-tools

# 2. Login
firebase login

# 3. Configure your project ID in .firebaserc and firebase-config.js

# 4. Deploy
firebase deploy
```

👉 **See [SETUP.md](SETUP.md) for the complete setup guide (Khmer + English)**

---

## Project Structure

```
public/
├── index.html          # Landing page (public)
├── app.html            # Main app (authenticated)
├── css/style.css       # Complete stylesheet
└── js/
    ├── firebase-config.js    # ← Update with your config
    ├── auth.js
    ├── dashboard-student.js
    ├── dashboard-teacher.js
    └── dashboard-admin.js
functions/
├── index.js            # Cloud Functions (Telegram, deleteUser)
└── package.json
firestore.rules         # Security Rules
firestore.indexes.json  # Composite indexes
```

---

## License

MIT — STIC Siem Reap Polytechnic Institute, 2024
