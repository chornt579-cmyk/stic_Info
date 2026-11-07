// Firebase Configuration
// If on localhost / codespaces, it connects automatically to Firebase Emulators!
const firebaseConfig = {
  apiKey: "demo-api-key",
  authDomain: "demo-stic.firebaseapp.com",
  projectId: "demo-stic",
  storageBucket: "demo-stic.appspot.com",
  messagingSenderId: "1234567890",
  appId: "1:1234567890:web:demo"
};

firebase.initializeApp(firebaseConfig);
const db = firebase.firestore();
const auth = firebase.auth();

// Automatically connect to local Firebase Emulators when running locally or on Codespaces
if (
  location.hostname === "localhost" ||
  location.hostname === "127.0.0.1" ||
  location.hostname.endsWith(".app.github.dev")
) {
  console.log("🔧 Connecting to Firebase Local Emulators...");
  
  // Connect Firestore Emulator (port 8080)
  db.useEmulator(location.hostname.endsWith(".app.github.dev") ? location.hostname : "localhost", 8080);
  
  // Connect Auth Emulator (port 9099)
  auth.useEmulator(`http://${location.hostname.endsWith(".app.github.dev") ? location.hostname : "localhost"}:9099`);
}
