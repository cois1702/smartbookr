import * as Notifications from 'expo-notifications';
import { useEffect, useState } from 'react';
import { StyleSheet, View } from 'react-native';
import { WebView } from 'react-native-webview';

// Configure notifications
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldSetBadge: true
  })
});

export default function App() {
  const [expoPushToken, setExpoPushToken] = useState('');

  // Register for push notifications
  useEffect(() => {
    registerForPushNotificationsAsync().then(token => {
      if (token) {
        setExpoPushToken(token);
        console.log('Expo push token:', token);

        // Send token to your backend
        fetch('https://smartbookr.homeworkplanner.co.za/save_push_token.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            user_id: 123, // Replace with actual logged-in user ID
            expo_token: token
          })
        })
        .then(res => res.json())
        .then(data => console.log('Token saved:', data))
        .catch(err => console.error('Error sending token:', err));
      }
    });
  }, []);

  // Listen for notifications while app is open
  useEffect(() => {
    const subscription = Notifications.addNotificationReceivedListener(notification => {
      console.log('Notification received:', notification);
    });
    return () => subscription.remove();
  }, []);

  return (
    <View style={styles.container}>
      {/* WebView loads your website */}
      <WebView
        source={{ uri: 'https://smartbookr.homeworkplanner.co.za' }}
        style={{ flex: 1 }}
        startInLoadingState
        scalesPageToFit
      />
    </View>
  );
}

// Helper function for push notifications
async function registerForPushNotificationsAsync() {
  const { status: existingStatus } = await Notifications.getPermissionsAsync();
  let finalStatus = existingStatus;

  if (existingStatus !== 'granted') {
    const { status } = await Notifications.requestPermissionsAsync();
    finalStatus = status;
  }

  if (finalStatus !== 'granted') {
    alert('Failed to get push token!');
    return;
  }

  const tokenData = await Notifications.getExpoPushTokenAsync({
    projectId: 'a2b97cd6-f4da-461f-a956-7ca3d4073877'
  });

  return tokenData.data;
}

const styles = StyleSheet.create({
  container: { flex: 1 },
});
