// In StackNavigator.js (or similar file)
// ... imports ...
import WelcomeScreen from './screens/WelcomeScreen'; 
import BusinessLoginScreen from './screens/BusinessLoginScreen'; // NEW
import BusinessRegisterScreen from './screens/BusinessRegisterScreen'; // NEW (Placeholder)
import BusinessDashboardScreen from './screens/BusinessDashboardScreen'; // NEW (Placeholder)

const Stack = createNativeStackNavigator();

function AppNavigator() {
    return (
        <NavigationContainer>
            <Stack.Navigator initialRouteName="Welcome">
                <Stack.Screen 
                    name="Welcome" 
                    component={WelcomeScreen} 
                    options={{ headerShown: false }}
                />
                <Stack.Screen 
                    name="BusinessLogin" 
                    component={BusinessLoginScreen} 
                    options={{ title: 'Login' }}
                />
                <Stack.Screen 
                    name="BusinessRegister" 
                    component={BusinessRegisterScreen} 
                    options={{ title: 'Register' }}
                />
                <Stack.Screen 
                    name="BusinessDashboard" 
                    component={BusinessDashboardScreen} 
                    options={{ title: 'Dashboard' }}
                />
                {/* ... other screens ... */}
            </Stack.Navigator>
        </NavigationContainer>
    );
}
// ...