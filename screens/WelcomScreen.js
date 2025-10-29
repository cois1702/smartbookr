// Updated WelcomeScreen.js (The screen in your image)
import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { Button } from 'react-native-paper';

// Make sure 'navigation' is passed as a prop
export default function WelcomeScreen({ navigation }) { 
    
    const navigateToLogin = () => {
        // This is the key fix: using navigation.navigate to go to the new screen
        navigation.navigate('BusinessLogin'); 
    };

    return (
        <View style={styles.container}>
            <Text style={styles.welcomeTitle}>Welcome to SmartBookr</Text>
            <Text style={styles.tagline}>
                Manage your bookings, clients, and schedule all in one place.
            </Text>
            <Button 
                mode="contained" 
                onPress={navigateToLogin} // The fixed handler
                style={styles.loginButton}
            >
                Business Login
            </Button>
            {/* The bottom bar components are handled elsewhere */}
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
        padding: 20,
        backgroundColor: '#f9f9f9',
    },
    welcomeTitle: {
        fontSize: 30,
        fontWeight: 'bold',
        marginBottom: 10,
    },
    tagline: {
        fontSize: 16,
        textAlign: 'center',
        color: '#666',
        marginBottom: 40,
    },
    loginButton: {
        width: '80%',
        paddingVertical: 10,
        backgroundColor: '#1E88E5',
    }
});