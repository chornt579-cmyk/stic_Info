#!/bin/bash
# .devcontainer/setup.sh
# Auto-setup script run inside GitHub Codespaces

set -e

echo "🔧 Setting up STIC Info development environment..."

# Install Firebase CLI globally
echo "📦 Installing Firebase CLI..."
npm install -g firebase-tools

# Install Cloud Functions dependencies
if [ -d "functions" ]; then
  echo "📦 Installing Cloud Functions dependencies..."
  cd functions && npm install && cd ..
fi

# Create .env.local from template if it doesn't exist
if [ ! -f ".env.local" ]; then
  echo "📝 Creating .env.local template..."
  cp .env.example .env.local 2>/dev/null || cat > .env.local << 'EOF'
# Firebase Config — replace with your project values
# Get from: Firebase Console > Project Settings > Your apps > SDK snippet
FIREBASE_API_KEY=your_api_key_here
FIREBASE_AUTH_DOMAIN=your_project_id.firebaseapp.com
FIREBASE_PROJECT_ID=your_project_id
FIREBASE_STORAGE_BUCKET=your_project_id.appspot.com
FIREBASE_MESSAGING_SENDER_ID=your_sender_id
FIREBASE_APP_ID=your_app_id
EOF
fi

echo ""
echo "✅ Setup complete!"
echo ""
echo "🚀 Quick Start:"
echo "   firebase emulators:start          → Start all emulators"
echo "   Open port 5000                    → View the app"
echo "   Open port 4000                    → Firebase Emulator UI"
echo ""
echo "📖 Read SETUP.md for full configuration guide."

