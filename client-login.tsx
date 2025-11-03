import { useRouter } from 'expo-router';
import React from 'react';
import { StyleSheet, Text, TextInput, TouchableOpacity, View } from 'react-native';

export default function ClientLogin() {
  const router = useRouter();

  const handleLogin = () => {
    console.log('Client Login submitted');
    // TODO: Add client authentication logic
  };

  const goHome = () => {
    router.push('/');
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Client Login</Text>

      <TextInput
        style={styles.input}
        placeholder="Email"
        keyboardType="email-address"
      />
      <TextInput
        style={styles.input}
        placeholder="Password"
        secureTextEntry
      />

      <TouchableOpacity style={styles.button} onPress={handleLogin}>
        <Text style={styles.buttonText}>Login</Text>
      </TouchableOpacity>

      <TouchableOpacity style={styles.homeButton} onPress={goHome}>
        <Text style={styles.homeButtonText}>Back Home</Text>
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { 
    flex: 1, 
    justifyContent: 'center', 
    alignItems: 'center', 
    padding: 20, 
    backgroundColor: '#F3F6FA' 
  },
  title: { 
    fontSize: 28, 
    fontWeight: 'bold', 
    marginBottom: 40, 
    color: '#1E293B' 
  },
  input: { 
    width: '100%', 
    borderWidth: 1, 
    borderColor: '#ccc', 
    padding: 12, 
    borderRadius: 8, 
    marginBottom: 20, 
    backgroundColor: '#fff' 
  },
  button: { 
    backgroundColor: '#34C759', 
    paddingVertical: 14, 
    paddingHorizontal: 80, 
    borderRadius: 10 
  },
  buttonText: { 
    color: '#fff', 
    fontSize: 18, 
    fontWeight: '600' 
  },
  homeButton: { 
    marginTop: 20, 
    backgroundColor: '#aaa', 
    paddingVertical: 10, 
    paddingHorizontal: 60, 
    borderRadius: 8 
  },
  homeButtonText: { 
    color: '#fff', 
    fontSize: 16, 
    fontWeight: '500' 
  }
});