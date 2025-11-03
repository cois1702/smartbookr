import * as Notifications from 'expo-notifications';
import { useEffect, useState } from 'react';
import { Alert, StyleSheet, View } from 'react-native';
import { WebView } from 'react-native-webview';


// Configure notifications correctly
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldShowBadge: true,  // this shows badge in UI
    shouldSetBadge: true,   // this sets the actual badge count
    shouldShowBanner: true,
    shouldShowList: true,
  }),
});


export default function App() {
  const [expoPushToken, setExpoPushToken] = useState('');

  // Register for push notifications
  useEffect(() => {
    async function registerToken() {
      const token = await registerForPushNotificationsAsync();
      if (token) {
        setExpoPushToken(token);
        console.log('Expo push token:', token);

        try {
          const response = await fetch(
            'https://smartbookr.homeworkplanner.co.za/save_push_token.php',
            {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                user_id: 123, // Replace with actual logged-in user ID
                expo_token: token,
              }),
            }
          );
          const data = await response.json();
          console.log('Token saved:', data);
        } catch (err) {
          console.error('Error sending token:', err);
        }
      }
    }

    registerToken();
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
      <WebView
        source={{ uri: 'https://smartbookr.homeworkplanner.co.za' }}
        style={{ flex: 1 }}
        startInLoadingState
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
    Alert.alert('Permission denied', 'Failed to get push token!');
    return undefined;
  }

  const tokenData = await Notifications.getExpoPushTokenAsync({
    projectId: 'a2b97cd6-f4da-461f-a956-7ca3d4073877',
  });

  return tokenData.data;
}

const styles = StyleSheet.create({
  container: { flex: 1 },
});
