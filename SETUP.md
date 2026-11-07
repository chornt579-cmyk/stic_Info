# STIC Info — Setup Guide / មគ្គុទ្ទេសក៍ដំឡើង

> ប្រព័ន្ធព័ត៌មាន និងកាលវិភាគ STIC Siem Reap Polytechnic Institute  
> School information & schedule management system — Firebase + HTML/JS

---

## ១. Prerequisites / តម្រូវការ

| Tool | Version | Notes |
|------|---------|-------|
| Node.js | 18+ | [nodejs.org](https://nodejs.org) |
| Firebase CLI | latest | `npm install -g firebase-tools` |
| Firebase account | — | [console.firebase.google.com](https://console.firebase.google.com) |

```bash
# Install Firebase CLI globally
npm install -g firebase-tools

# Verify
firebase --version
node --version   # should be 18+
```

---

## ២. Firebase Project Setup / បង្កើត Project

1. ចូលទៅ [console.firebase.google.com](https://console.firebase.google.com)
2. ចុច **"Add project"** → ដាក់ឈ្មោះ `stic-info`
3. Enable Google Analytics (optional)
4. រង់ចាំ Project ត្រូវបានបង្កើត

### Enable Authentication
1. Firebase Console → **Authentication** → **Get started**
2. **Sign-in method** → Enable **Email/Password**

### Create Firestore Database
1. Firebase Console → **Firestore Database** → **Create database**
2. ជ្រើសរើស **Production mode** (rules will be deployed separately)
3. ជ្រើសរើស **asia-southeast1** (Singapore) ជាប្រភពទិន្នន័យ

### Get Firebase Config
1. Firebase Console → ⚙️ **Project Settings** → **General**
2. Scroll down → **Your apps** → ចុច **</>** (Web)
3. ចុះឈ្មោះ app ជា `stic-info-web`
4. Copy the `firebaseConfig` object

---

## ៣. Configure Firebase Config / ដំឡើង Config

1. បើក [`public/js/firebase-config.js`](public/js/firebase-config.js)
2. Replace placeholder values ជាមួយ config ពី Firebase Console:

```javascript
const firebaseConfig = {
  apiKey:            "AIzaSy...",          // ← paste yours
  authDomain:        "stic-info.firebaseapp.com",
  projectId:         "stic-info",
  storageBucket:     "stic-info.appspot.com",
  messagingSenderId: "123456789",
  appId:             "1:123:web:abc..."
};
```

3. Update `.firebaserc` with your Project ID:
```json
{ "projects": { "default": "stic-info" } }
```

---

## ៤. Create Admin Account / បង្កើតគណនី Admin

ព្រោះ Admin មិនអាចចុះឈ្មោះដោយខ្លួនឯងតាម UI:

### Method A — Firebase Console
1. Firebase Console → **Authentication** → **Users** → **Add user**
2. ពេលបញ្ចូល Email + Password → Copy the UID ដែលបង្ហាញ
3. Firebase Console → **Firestore** → **Start collection** → `users`
4. Document ID = UID ដែល copy → Add fields:

| Field | Type | Value |
|-------|------|-------|
| `uid` | string | (UID) |
| `name` | string | `"Admin Name"` |
| `email` | string | `"admin@stic.edu.kh"` |
| `role` | string | `"admin"` |
| `status` | string | `"active"` |
| `createdAt` | timestamp | (now) |

### Method B — Firebase CLI (after login)
```bash
firebase auth:import users.json --project stic-info
```

---

## ៥. Deploy Firestore Rules & Indexes / ដំឡើង Rules

```bash
# Login to Firebase
firebase login

# Deploy rules and indexes only
firebase deploy --only firestore
```

> [!IMPORTANT]
> Rules protect your data. Deploy them BEFORE going live.

---

## ៦. Deploy Hosting / ដំឡើង Website

```bash
# Deploy the public/ folder to Firebase Hosting
firebase deploy --only hosting
```

After deployment you will receive a URL like:
```
https://stic-info.web.app
```

---

## ៧. Setup Telegram Bot (Optional) / ដំឡើង Telegram Bot

> [!NOTE]
> Cloud Functions require the **Blaze (pay-as-you-go)** plan.

### ៧.១ Create Bot
1. Open Telegram → search **@BotFather**
2. Send `/newbot` → Follow instructions → Get **BOT_TOKEN**

### ៧.២ Get Chat ID
1. Add your bot to your Telegram group
2. Send a message in the group
3. Visit: `https://api.telegram.org/bot{TOKEN}/getUpdates`
4. Find `"chat":{"id": -XXXXXXXXXX}` → That's your **CHAT_ID**

### ៧.៣ Set Config & Deploy Functions
```bash
# Set Telegram config (replace with real values)
firebase functions:config:set telegram.token="1234567890:ABCdef..." telegram.chat_id="-1001234567890"

# Install function dependencies
cd functions
npm install
cd ..

# Deploy functions
firebase deploy --only functions
```

> [!WARNING]
> Never commit your Telegram token to version control.

---

## ៨. Local Development with Emulator / ការអភិវឌ្ឍ Local

```bash
# Start all emulators
firebase emulators:start

# Emulator URLs:
# Hosting:   http://localhost:5000
# Firestore: http://localhost:8080
# Auth:      http://localhost:9099
# Functions: http://localhost:5001
# Emulator UI: http://localhost:4000
```

> [!TIP]
> The Emulator UI at port 4000 lets you inspect Firestore, Auth, and Functions locally.

---

## ៩. Seed Initial Data / បន្ថែមទិន្នន័យដំបូង

ក្រោយ Login ជា Admin:

1. **Departments tab**: បន្ថែម departments (IT, CE, Accounting, etc.)
2. **Classes tab**: បន្ថែម classes (IT-1A, IT-1B, CE-2A, etc.) ជ្រើសរើស dept, floor, room, shift
3. **Schedules tab**: បន្ថែម schedules — ជ្រើសរើស class, teacher, day, subject, shift
4. **Users tab**: Approve teachers, assign classes to students

---

## ១០. File Structure / រចនាសម្ព័ន្ធឯកសារ

```
stic_Info/
├── .firebaserc              ← Project ID config
├── firebase.json            ← Hosting, Firestore, Functions, Emulator config
├── firestore.rules          ← Security Rules
├── firestore.indexes.json   ← Composite indexes
├── SETUP.md                 ← This file
├── README.md                ← Project overview
├── public/                  ← Hosted website (Firebase Hosting)
│   ├── index.html           ← Public landing page
│   ├── app.html             ← Main authenticated app
│   ├── css/
│   │   └── style.css        ← All styles (Noto Sans Khmer + Roboto)
│   └── js/
│       ├── firebase-config.js    ← ⚠ Update with your project config
│       ├── auth.js               ← Login, register, auth state
│       ├── dashboard-student.js  ← Student dashboard logic
│       ├── dashboard-teacher.js  ← Teacher dashboard logic
│       └── dashboard-admin.js    ← Admin dashboard + CRUD
└── functions/               ← Cloud Functions
    ├── package.json
    └── index.js             ← Telegram, deleteUser functions
```

---

## ១១. Data Model Reference / ម៉ូដែលទិន្នន័យ

### `/users/{uid}`
```json
{
  "uid": "...",
  "name": "ឈ្មោះ",
  "email": "...",
  "phone": "0xx xxx xxx",
  "role": "student | teacher | admin",
  "status": "active | pending | approved",
  "classId": "...",       // students only
  "deptId": "...",        // students & teachers
  "teacherClasses": [],   // teachers only: array of classId
  "createdAt": "timestamp"
}
```

### `/departments/{deptId}`
```json
{ "name": "Information Technology", "code": "IT", "description": "..." }
```

### `/classes/{classId}`
```json
{ "name": "IT-1A", "deptId": "...", "floor": "2", "room": "A201", "shift": "morning" }
```

### `/schedules/{scheduleId}`
```json
{
  "classId": "...", "teacherId": "...",
  "dayOfWeek": "monday", "subject": "Programming",
  "shift": "morning", "shiftStart": "07:00", "shiftEnd": "10:00",
  "floor": "2", "room": "A201"
}
```

### `/announcements/{annId}`
```json
{
  "title": "...", "message": "...",
  "targetType": "public | all_students | class | dept_students | all_teachers | dept_teachers",
  "targetId": "classId_or_deptId (if applicable)",
  "authorId": "uid", "authorName": "...",
  "teacherOnly": false,
  "sendTelegram": false,
  "createdAt": "timestamp"
}
```

---

## ១២. Troubleshooting / ដោះស្រាយបញ្ហា

| Problem | Solution |
|---------|----------|
| "Permission denied" in Firestore | Deploy rules: `firebase deploy --only firestore` |
| Login fails | Check `firebase-config.js` has correct values |
| Public announcements not showing | Check index exists or deploy: `firebase deploy --only firestore:indexes` |
| Telegram not sending | Check `functions:config:set` and deploy functions |
| Teacher can't login after register | Admin must approve in Users tab |
| Emulator not starting | Check ports 4000, 5000, 8080, 9099, 5001 are free |
