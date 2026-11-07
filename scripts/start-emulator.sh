#!/bin/bash
# scripts/start-emulator.sh
# Quick script to start Firebase Emulators for local/Codespaces development

set -e

echo ""
echo "╔════════════════════════════════════════════╗"
echo "║      STIC Info — Firebase Emulator         ║"
echo "╠════════════════════════════════════════════╣"
echo "║  App:         http://localhost:5000         ║"
echo "║  Emulator UI: http://localhost:4000         ║"
echo "║  Firestore:   http://localhost:8080         ║"
echo "║  Auth:        http://localhost:9099         ║"
echo "║  Functions:   http://localhost:5001         ║"
echo "╚════════════════════════════════════════════╝"
echo ""

# Check if firebase-tools is installed
if ! command -v firebase &> /dev/null; then
  echo "❌ Firebase CLI not found. Installing..."
  npm install -g firebase-tools
fi

# Check if functions dependencies are installed
if [ ! -d "functions/node_modules" ]; then
  echo "📦 Installing Cloud Functions dependencies..."
  cd functions && npm install && cd ..
fi

echo "🚀 Starting emulators..."
echo "   Press Ctrl+C to stop"
echo ""

# Start all emulators and import seed data if exists
if [ -d ".emulator-data" ]; then
  echo "📂 Loading saved emulator data from .emulator-data/"
  firebase emulators:start --import=.emulator-data --export-on-exit=.emulator-data
else
  firebase emulators:start --export-on-exit=.emulator-data
fi

