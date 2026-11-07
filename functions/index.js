/**
 * STIC Info — Firebase Cloud Functions
 *
 * Functions:
 *  1. sendTelegramNotification — HTTPS callable (Admin only)
 *  2. onAnnouncementCreate     — Firestore trigger
 *  3. deleteUserAccount        — HTTPS callable (Admin only)
 */

'use strict';

const functions = require('firebase-functions');
const admin     = require('firebase-admin');
const fetch     = require('node-fetch');

admin.initializeApp();
const db = admin.firestore();

// ─── Helper: Verify caller is Admin ──────────────────────────────────────────
async function assertAdmin(context) {
  if (!context.auth) {
    throw new functions.https.HttpsError('unauthenticated', 'ត្រូវការ Authentication');
  }
  const userDoc = await db.collection('users').doc(context.auth.uid).get();
  if (!userDoc.exists || userDoc.data().role !== 'admin') {
    throw new functions.https.HttpsError('permission-denied', 'Admin permission required');
  }
}

// ─── Helper: Send Telegram Message ───────────────────────────────────────────
async function sendTelegram(message, chatId) {
  const token = functions.config().telegram?.token;
  if (!token) throw new Error('Telegram token not configured. Run: firebase functions:config:set telegram.token="TOKEN"');

  const targetChatId = chatId || functions.config().telegram?.chat_id;
  if (!targetChatId) throw new Error('Telegram chat_id not configured');

  const url = `https://api.telegram.org/bot${token}/sendMessage`;
  const res  = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      chat_id:    targetChatId,
      text:       message,
      parse_mode: 'HTML'
    })
  });

  const data = await res.json();
  if (!data.ok) {
    throw new Error(`Telegram API error: ${data.description}`);
  }
  return data;
}

// ════════════════════════════════════════════════════════════════════════════
// FUNCTION 1: sendTelegramNotification
// HTTPS Callable — Admin only
// Receives: { message: string, chatId?: string }
// ════════════════════════════════════════════════════════════════════════════
exports.sendTelegramNotification = functions.https.onCall(async (data, context) => {
  await assertAdmin(context);

  const { message, chatId } = data;
  if (!message || typeof message !== 'string') {
    throw new functions.https.HttpsError('invalid-argument', 'message is required');
  }

  try {
    const result = await sendTelegram(message, chatId || '');
    functions.logger.info('Telegram sent successfully', { messageId: result.result?.message_id });
    return { success: true, messageId: result.result?.message_id };
  } catch (err) {
    functions.logger.error('Telegram send failed', { error: err.message });
    throw new functions.https.HttpsError('internal', 'Telegram send failed: ' + err.message);
  }
});

// ════════════════════════════════════════════════════════════════════════════
// FUNCTION 2: onAnnouncementCreate
// Firestore trigger: fires when a new announcement document is created
// If sendTelegram == true, sends to configured Telegram group
// ════════════════════════════════════════════════════════════════════════════
exports.onAnnouncementCreate = functions.firestore
  .document('announcements/{annId}')
  .onCreate(async (snap, context) => {
    const ann = snap.data();

    if (!ann.sendTelegram) {
      functions.logger.info('Telegram not requested for announcement', { id: context.params.annId });
      return null;
    }

    // Build Telegram message text
    const targetLabels = {
      public:       'សាធារណៈ (Public)',
      all_students: 'សិស្សទាំងអស់',
      all_teachers: 'គ្រូទាំងអស់',
      class:        'ថ្នាក់',
      dept_students:'ដេប (សិស្ស)',
      dept_teachers:'ដេប (គ្រូ)'
    };

    let targetLabel = targetLabels[ann.targetType] || ann.targetType || 'N/A';

    // If targeting a specific class/dept, fetch its name
    if (['class', 'dept_students', 'dept_teachers'].includes(ann.targetType) && ann.targetId) {
      try {
        const col  = ann.targetType === 'class' ? 'classes' : 'departments';
        const doc  = await db.collection(col).doc(ann.targetId).get();
        if (doc.exists) {
          targetLabel += `: ${doc.data().name}`;
        }
      } catch (_) {}
    }

    const msgText = [
      `📢 <b>${ann.title || 'ការប្រកាសថ្មី'}</b>`,
      ``,
      ann.message || '',
      ``,
      `🎯  គោលដៅ: ${targetLabel}`,
      `👤 ដោយ: ${ann.authorName || 'N/A'}`,
      ``,
      `— STIC Info System`
    ].join('\n');

    try {
      await sendTelegram(msgText, '');
      functions.logger.info('Auto Telegram sent for announcement', { id: context.params.annId });
    } catch (err) {
      functions.logger.error('Auto Telegram failed', { error: err.message, id: context.params.annId });
      // Don't throw — we don't want the function to retry on Telegram failure
    }

    return null;
  });

// ════════════════════════════════════════════════════════════════════════════
// FUNCTION 3: deleteUserAccount
// HTTPS Callable — Admin only
// Receives: { uid: string }
// Atomically deletes Firestore /users/{uid} + Firebase Auth user
// ════════════════════════════════════════════════════════════════════════════
exports.deleteUserAccount = functions.https.onCall(async (data, context) => {
  await assertAdmin(context);

  const { uid } = data;
  if (!uid || typeof uid !== 'string') {
    throw new functions.https.HttpsError('invalid-argument', 'uid is required');
  }

  // Prevent admin from deleting themselves
  if (uid === context.auth.uid) {
    throw new functions.https.HttpsError('failed-precondition', 'Admin cannot delete own account via this function');
  }

  const errors = [];

  // Delete Firestore document
  try {
    await db.collection('users').doc(uid).delete();
    functions.logger.info('Deleted Firestore user doc', { uid });
  } catch (err) {
    errors.push(`Firestore: ${err.message}`);
    functions.logger.error('Failed to delete Firestore user doc', { uid, error: err.message });
  }

  // Delete Firebase Auth user
  try {
    await admin.auth().deleteUser(uid);
    functions.logger.info('Deleted Firebase Auth user', { uid });
  } catch (err) {
    if (err.code === 'auth/user-not-found') {
      functions.logger.warn('Auth user not found (already deleted?)', { uid });
    } else {
      errors.push(`Auth: ${err.message}`);
      functions.logger.error('Failed to delete Auth user', { uid, error: err.message });
    }
  }

  if (errors.length > 0) {
    throw new functions.https.HttpsError('internal', `Partial delete errors: ${errors.join('; ')}`);
  }

  return { success: true, uid };
});
