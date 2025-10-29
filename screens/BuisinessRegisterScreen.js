// screens/BusinessRegisterScreen.js
import { useState } from 'react';
import { Alert, ScrollView, StyleSheet, TextInput } from 'react-native';
import { Button, Title } from 'react-native-paper';
// Assume you will create 'registerBusiness' in your api.js
// import { registerBusiness } from '../api/api'; 

export default function BusinessRegisterScreen({ navigation }) {
    const [businessName, setBusinessName] = useState('');
    const [ownerName, setOwnerName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [loading, setLoading] = useState(false);

    const handleRegister = async () => {
        if (!businessName || !email || !password || !ownerName) {
            Alert.alert('Missing Info', 'Please fill in all fields.');
            return;
        }
        if (password !== confirmPassword) {
            Alert.alert('Password Error', 'Passwords do not match.');
            return;
        }

        setLoading(true);
        try {
            // Placeholder for API call:
            // const response = await registerBusiness({ businessName, ownerName, email, password });

            // Simulating a successful registration for now:
            await new Promise(resolve => setTimeout(resolve, 1500)); 
            
            // Assuming successful registration automatically logs them in or navigates to login
            Alert.alert(
                'Success!', 
                'Your business account is created. Please log in.',
                [{ text: 'OK', onPress: () => navigation.navigate('BusinessLogin') }]
            );

        } catch (error) {
            console.error('Registration Error:', error);
            Alert.alert('Error', 'Failed to register business. Please try again.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <ScrollView contentContainerStyle={styles.container}>
            <Title style={styles.title}>Register Your Business</Title>
            
            <TextInput
                style={styles.input}
                placeholder="Business Name (e.g., Jane's Salon)"
                value={businessName}
                onChangeText={setBusinessName}
            />
            <TextInput
                style={styles.input}
                placeholder="Your Full Name (Owner)"
                value={ownerName}
                onChangeText={setOwnerName}
            />
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
            <TextInput
                style={styles.input}
                placeholder="Confirm Password"
                value={confirmPassword}
                onChangeText={setConfirmPassword}
                secureTextEntry
            />

            <Button 
                mode="contained" 
                onPress={handleRegister} 
                loading={loading}
                style={styles.registerButton}
                disabled={loading}
            >
                Register & Get Started
            </Button>
            
            <Button 
                mode="text" 
                onPress={() => navigation.navigate('BusinessLogin')}
                labelStyle={styles.loginLink}
            >
                Already have an account? Log In
            </Button>
        </ScrollView>
    );
}

const styles = StyleSheet.create({
    container: { flexGrow: 1, padding: 25, backgroundColor: '#fff' },
    title: { fontSize: 26, marginBottom: 25, color: '#1E88E5', textAlign: 'center' },
    input: { height: 50, borderColor: '#ccc', borderWidth: 1, marginBottom: 15, paddingHorizontal: 15, borderRadius: 8 },
    registerButton: { marginVertical: 15, paddingVertical: 5, backgroundColor: '#4CAF50' },
    loginLink: { color: '#1E88E5' }
});