import React from 'react';
import { StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { useRouter } from 'expo-router'; // Expo Router navigation

export default function HomeScreen() {
const router = useRouter(); // Router instance for navigation

const handleBusinessLogin = () => {
console.log('Business Login pressed');
router.push('/login'); // Correct route for login screen
};

const handleClientLogin = () => {
console.log('Client Login pressed');
router.push('/client-login'); // Correct route for client login
};

return ( <View style={styles.container}> <View style={styles.content}> <Text style={styles.title}>Welcome to SmartBookr 📘</Text> <Text style={styles.subtitle}>
Manage your bookings, clients, and schedule all in one place. </Text>

```
    <TouchableOpacity style={styles.button} onPress={handleBusinessLogin}>
      <Text style={styles.buttonText}>Business Login</Text>
    </TouchableOpacity>

    <TouchableOpacity
      style={[styles.button, { backgroundColor: '#34C759', marginTop: 20 }]}
      onPress={handleClientLogin}
    >
      <Text style={styles.buttonText}>Client Login</Text>
    </TouchableOpacity>
  </View>
</View>
```

);
}

const styles = StyleSheet.create({
container: {
flex: 1,
backgroundColor: '#F3F6FA',
justifyContent: 'center',
alignItems: 'center',
paddingHorizontal: 20,
},
content: {
alignItems: 'center',
width: '100%',
marginTop: 80,
},
title: {
fontSize: 28,
fontWeight: 'bold',
color: '#1E293B',
marginBottom: 10,
textAlign: 'center',
},
subtitle: {
fontSize: 16,
color: '#475569',
textAlign: 'center',
marginBottom: 40,
},
button: {
backgroundColor: '#007AFF',
paddingVertical: 16,
paddingHorizontal: 60,
borderRadius: 12,
elevation: 3,
shadowColor: '#000',
shadowOpacity: 0.2,
shadowOffset: { width: 0, height: 2 },
shadowRadius: 4,
},
buttonText: {
color: '#fff',
fontSize: 18,
fontWeight: '600',
textAlign: 'center',
},
});
