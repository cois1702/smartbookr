import React from 'react';
import { View, Text, StyleSheet, TextInput, TouchableOpacity } from 'react-native';
import { useRouter } from 'expo-router';

export default function Login() {
const router = useRouter();

const handleLogin = () => {
console.log('Business Login submitted');
// TODO: Add authentication logic
};

const handleBackHome = () => {
router.push('/'); // Navigate back to Home screen
};

return ( <View style={styles.container}> <Text style={styles.title}>Business Login</Text>

```
  <TextInput style={styles.input} placeholder="Email" keyboardType="email-address" />
  <TextInput style={styles.input} placeholder="Password" secureTextEntry />

  <TouchableOpacity style={styles.button} onPress={handleLogin}>
    <Text style={styles.buttonText}>Login</Text>
  </TouchableOpacity>

  <TouchableOpacity style={styles.backButton} onPress={handleBackHome}>
    <Text style={styles.backButtonText}>Back to Home</Text>
  </TouchableOpacity>
</View>
```

);
}

const styles = StyleSheet.create({
container: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 20 },
title: { fontSize: 28, fontWeight: 'bold', marginBottom: 40 },
input: {
width: '100%',
borderWidth: 1,
borderColor: '#ccc',
padding: 12,
borderRadius: 8,
marginBottom: 20,
},
button: {
backgroundColor: '#007AFF',
paddingVertical: 14,
paddingHorizontal: 80,
borderRadius: 10,
marginBottom: 20,
},
buttonText: { color: '#fff', fontSize: 18, fontWeight: '600' },
backButton: {
paddingVertical: 12,
paddingHorizontal: 40,
borderRadius: 10,
borderWidth: 1,
borderColor: '#007AFF',
},
backButtonText: {
color: '#007AFF',
fontSize: 16,
fontWeight: '600',
},
});
