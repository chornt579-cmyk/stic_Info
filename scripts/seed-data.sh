#!/bin/bash
# scripts/seed-data.sh
# Seed initial data into Firebase Emulator for testing

set -e

PROJECT_ID="${1:-demo-stic}"
FIRESTORE_URL="http://localhost:8080/v1/projects/${PROJECT_ID}/databases/(default)/documents"

echo "🌱 Seeding data into Firestore Emulator (project: ${PROJECT_ID})..."
echo "   Make sure emulators are running: ./scripts/start-emulator.sh"
echo ""

# Helper function to create a Firestore document via REST
create_doc() {
  local collection=$1
  local doc_id=$2
  local body=$3
  
  curl -s -X PATCH \
    "${FIRESTORE_URL}/${collection}/${doc_id}" \
    -H "Content-Type: application/json" \
    -d "$body" > /dev/null
  echo "  ✅ Created ${collection}/${doc_id}"
}

# ── Departments ────────────────────────────────────
echo "📁 Creating departments..."

create_doc "departments" "it" '{
  "fields": {
    "name":      {"stringValue": "Information Technology"},
    "nameKh":    {"stringValue": "បច្ចេកវិទ្យាព័ត៌មាន"},
    "code":      {"stringValue": "IT"},
    "createdAt": {"timestampValue": "2026-11-07T00:00:00Z"}
  }
}'

create_doc "departments" "civil" '{
  "fields": {
    "name":      {"stringValue": "Civil Engineering"},
    "nameKh":    {"stringValue": "វិស្វកម្មសំណង់"},
    "code":      {"stringValue": "CE"},
    "createdAt": {"timestampValue": "2026-11-07T00:00:00Z"}
  }
}'

create_doc "departments" "electrical" '{
  "fields": {
    "name":      {"stringValue": "Electrical Engineering"},
    "nameKh":    {"stringValue": "វិស្វកម្មអគ្គីសនី"},
    "code":      {"stringValue": "EE"},
    "createdAt": {"timestampValue": "2026-11-07T00:00:00Z"}
  }
}'

create_doc "departments" "accounting" '{
  "fields": {
    "name":      {"stringValue": "Accounting"},
    "nameKh":    {"stringValue": "គណនេយ្យ"},
    "code":      {"stringValue": "ACC"},
    "createdAt": {"timestampValue": "2026-11-07T00:00:00Z"}
  }
}'

# ── Classes ────────────────────────────────────────
echo ""
echo "🏫 Creating classes..."

create_doc "classes" "it-y1-morning" '{
  "fields": {
    "name":     {"stringValue": "IT Year 1 Morning"},
    "nameKh":   {"stringValue": "IT ឆ្នាំ១ ព្រឹក"},
    "deptId":   {"stringValue": "it"},
    "year":     {"integerValue": "1"},
    "shift":    {"stringValue": "morning"},
    "room":     {"stringValue": "101"},
    "floor":    {"stringValue": "1"},
    "capacity": {"integerValue": "35"}
  }
}'

create_doc "classes" "it-y2-morning" '{
  "fields": {
    "name":     {"stringValue": "IT Year 2 Morning"},
    "nameKh":   {"stringValue": "IT ឆ្នាំ២ ព្រឹក"},
    "deptId":   {"stringValue": "it"},
    "year":     {"integerValue": "2"},
    "shift":    {"stringValue": "morning"},
    "room":     {"stringValue": "102"},
    "floor":    {"stringValue": "1"},
    "capacity": {"integerValue": "35"}
  }
}'

create_doc "classes" "it-y1-afternoon" '{
  "fields": {
    "name":     {"stringValue": "IT Year 1 Afternoon"},
    "nameKh":   {"stringValue": "IT ឆ្នាំ១ រសៀល"},
    "deptId":   {"stringValue": "it"},
    "year":     {"integerValue": "1"},
    "shift":    {"stringValue": "afternoon"},
    "room":     {"stringValue": "103"},
    "floor":    {"stringValue": "1"},
    "capacity": {"integerValue": "35"}
  }
}'

create_doc "classes" "ce-y1-morning" '{
  "fields": {
    "name":     {"stringValue": "Civil Eng Year 1 Morning"},
    "nameKh":   {"stringValue": "សំណង់ ឆ្នាំ១ ព្រឹក"},
    "deptId":   {"stringValue": "civil"},
    "year":     {"integerValue": "1"},
    "shift":    {"stringValue": "morning"},
    "room":     {"stringValue": "201"},
    "floor":    {"stringValue": "2"},
    "capacity": {"integerValue": "40"}
  }
}'

# ── Admin User ─────────────────────────────────────
echo ""
echo "👤 Creating admin user..."
echo "   ⚠️  Admin must be created in Firebase Auth Console separately"
echo "   After creating, update the UID below and run this script again"

ADMIN_UID="REPLACE_WITH_REAL_ADMIN_UID"

create_doc "users" "$ADMIN_UID" "{
  \"fields\": {
    \"role\":      {\"stringValue\": \"admin\"},
    \"name\":      {\"stringValue\": \"Admin STIC\"},
    \"email\":     {\"stringValue\": \"admin@stic.edu.kh\"},
    \"status\":    {\"stringValue\": \"active\"},
    \"createdAt\": {\"timestampValue\": \"2026-11-07T00:00:00Z\"}
  }
}"

echo ""
echo "✅ Seed data complete!"
echo ""
echo "📋 Created:"
echo "   4 departments (IT, Civil, Electrical, Accounting)"
echo "   4 classes (IT Y1 Morning/Afternoon, IT Y2 Morning, Civil Y1 Morning)"
echo ""
echo "⚠️  Next steps:"
echo "   1. Create Admin in Firebase Auth Emulator UI (port 4000)"
echo "   2. Update ADMIN_UID in this script and re-run"
echo "   3. Use Admin dashboard to add schedules and announcements"

