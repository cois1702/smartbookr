// screens/BusinessLoginScreen.js
import { useState } from 'react';
import { Alert, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { Button, Title } from 'react-native-paper';
import { loginBusiness } from '../api/api'; // <--- IMPORT LOGIN FUNCTION

export default function BusinessLoginScreen({ navigation }) {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [loading, setLoading] = useState(false);

    const handleLogin = async () => { // <--- MADE ASYNC
        if (!email || !password) {
            Alert.alert('Missing Info', 'Please enter both email and password.');
            return;
        }

        setLoading(true);
        try {
            // Call the API with credentials
            const response = await loginBusiness({ 
                email: email, 
                password: password 
            });
            const data = response.data;

            if (data.status === 'success') {
                // SUCCESS: Extract critical authentication data
                const { token, business } = data; 
                
                // Navigate to the main dashboard, passing the token and business object
                navigation.navigate('BusinessDashboard', { 
                    token: token, 
                    businessInfo: business 
                });
            } else {
                // FAILURE: Display the error message returned from the API
                Alert.alert('Login Failed', data.message || 'Invalid email or password.');
            }
        } catch (error) {
            console.error('Login Error:', error);
            // Handle network errors or server connection failures
            Alert.alert('Network Error', 'Could not connect to the server or API. Please check your connection.');
        } finally {
            setLoading(false);
        }
    };
    
    // ... rest of the component (navigateToRegister, return statement, styles) ...

    const navigateToRegister = () => {
        navigation.navigate('BusinessRegister');
    };

    return (
        <ScrollView contentContainerStyle={styles.container}>
            <Title style={styles.title}>Business Login</Title>
            <TextInput
                style={styles.input}
                placeholder="Business Email"
                value={email}
                onChangeText={setEmail}
                keyboardType="email-address"
                autoCapitalize="none"
            />
            <TextInput
                style={styles.input}
                placeholder="Password"
                value={password}
                onChangeText={setPassword}
                secureTextEntry
            />
            <Button 
                mode="contained" 
                onPress={handleLogin} // <--- CALLS NEW ASYNC FUNCTION
                loading={loading}
                style={styles.loginButton}
                disabled={loading}
            >
                {loading ? 'Logging In...' : 'Login'}
            </Button>
            
            <View style={styles.registerSection}>
                <Text style={styles.registerText}>Don't have an account?</Text>
                <Button 
                    mode="text" 
                    onPress={navigateToRegister}
                    labelStyle={styles.registerLink}
                >
                    Register New Business
                </Button>
            </View>
        </ScrollView>
    );
}

const styles = StyleSheet.create({
    container: { flexGrow: 1, justifyContent: 'center', padding: 30, backgroundColor: '#fff' },
    title: { fontSize: 32, marginBottom: 30, color: '#1E88E5' },
    input: { height: 50, borderColor: '#ccc', borderWidth: 1, marginBottom: 15, paddingHorizontal: 15, borderRadius: 8 },
    loginButton: { marginVertical: 10, paddingVertical: 5, backgroundColor: '#1E88E5' },
    registerSection: { marginTop: 20, alignItems: 'center' },
    registerText: { fontSize: 16, color: '#555' },
    registerLink: { fontSize: 16, color: '#1E88E5' },
});