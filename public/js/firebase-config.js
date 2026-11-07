// Real Firebase Configuration for STIC Info
const firebaseConfig = {
  apiKey: "AIzaSyDIK5W-gQetYJbOw8vNN2y_UV4XRLmYG_4",
  authDomain: "stic-info-demo.firebaseapp.com",
  projectId: "stic-info-demo",
  storageBucket: "stic-info-demo.firebasestorage.app",
  messagingSenderId: "6845072427",
  appId: "1:6845072427:web:349b00adcd24a4d0c2566e",
  measurementId: "G-JP317DSZG3"
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);
const db = firebase.firestore();
const auth = firebase.auth();
