//importScripts('https://www.gstatic.com/firebasejs/3.9.0/firebase-app.js');
//importScripts('https://www.gstatic.com/firebasejs/3.9.0/firebase-messaging.js');
importScripts('https://www.gstatic.com/firebasejs/7.2.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/7.2.0/firebase-messaging.js');

// Initialize the Firebase app in the service worker by passing in the
// messagingSenderId.
firebase.initializeApp({
  'messagingSenderId': '103123974542'
});

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  console.log('Data=' + payload.data.notification);
  var obj = JSON.parse(payload.data.notification, function (key, value) {
    return value;
  });
  console.log('Title=' + obj.title);
  // Customize notification here
  const notificationTitle = obj.title;
  const notificationOptions = {
    body: obj.body,
    icon: obj.icon,
	vibrate: [100, 50, 100]
  };

  return self.registration.showNotification(notificationTitle,
      notificationOptions);
});
