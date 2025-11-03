import { useNavigation } from '@react-navigation/native'; // <-- import navigation
import React from 'react';
import { Text, TouchableOpacity, View } from 'react-native';

export default function HomeScreen() {
  const navigation = useNavigation(); // <-- get navigation instance

  const handleBusinessLogin = () => {
    console.log('Business Login pressed');
    navigation.navigate('Login'); // <-- navigate to Login screen
  };

  return (
    <View style={styles.container}>
      <View style={styles.content}>
        <Text style={styles.title}>Welcome to SmartBookr 📘</Text>
        <Text style={styles.subtitle}>
          Manage your bookings, clients, and schedule all in one place.
        </Text>

        <TouchableOpacity style={styles.button} onPress={handleBusinessLogin}>
          <Text style={styles.buttonText}>Business Login</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

// ... keep your styles as they are
