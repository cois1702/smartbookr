import React from 'react';
import { View, Text, StyleSheet, TextInput, TouchableOpacity } from 'react-native';

export default function ClientLogin() {
const handleLogin = () => {
console.log('Client Login submitted');
// TODO: Add client authentication logic
};

return ( <View style={styles.container}> <Text style={styles.title}>Client Login</Text>

```
  <TextInput style={styles.input} placeholder="Email" keyboardType="email-address" />
  <TextInput style={styles.input} placeholder="Password" secureTextEntry />

  <TouchableOpacity style={styles.button} onPress={handleLogin}>
    <Text style={styles.buttonText}>Login</Text>
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
backgroundColor: '#34C759', // green for client
paddingVertical: 14,
paddingHorizontal: 80,
borderRadius: 10,
},
buttonText: { color: '#fff', fontSize: 18, fontWeight: '600' },
});
