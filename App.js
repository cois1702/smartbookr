import 'react-native-gesture-handler'; // Must be first import for navigation
import * as React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createStackNavigator } from '@react-navigation/stack';
import { Provider as PaperProvider } from 'react-native-paper';

// --- Import all screens ---
// Client Screens (Booking Flow)
import BusinessListingScreen from './screens/client/BusinessListingScreen';
import ServiceSelectionScreen from './screens/client/ServiceSelectionScreen';
import BookingConfirmationScreen from './screens/client/BookingConfirmationScreen';

// Business Screens (Management Flow)
import LoginScreen from './screens/business/LoginScreen';
import RegistrationScreen from './screens/business/RegistrationScreen';
import BusinessDashboardScreen from './screens/business/BusinessDashboardScreen';
import StaffManagementScreen from './screens/business/StaffManagementScreen';
import BookingManagementScreen from './screens/business/BookingManagementScreen';

// --- Stack Navigators ---

// 1. Client Booking Stack
const ClientStack = createStackNavigator();

function ClientAppStack() {
  return (
    <ClientStack.Navigator 
      initialRouteName="BusinessListing"
      screenOptions={{
        headerStyle: { backgroundColor: '#1E88E5' },
        headerTintColor: '#fff',
        headerTitleStyle: { fontWeight: 'bold' },
      }}
    >
      <ClientStack.Screen 
        name="BusinessListing" 
        component={BusinessListingScreen} 
        options={{ title: 'Find a Business' }} 
      />
      <ClientStack.Screen 
        name="ServiceSelection" 
        component={ServiceSelectionScreen} 
        options={({ route }) => ({ title: route.params.businessName || 'Select Service' })}
      />
      <ClientStack.Screen 
        name="BookingConfirmation" 
        component={BookingConfirmationScreen} 
        options={{ title: 'Confirm Booking' }} 
      />
    </ClientStack.Navigator>
  );
}

// 2. Business Management Stack
const BusinessStack = createStackNavigator();

function BusinessAppStack() {
  return (
    <BusinessStack.Navigator 
      initialRouteName="BusinessLogin"
      screenOptions={{
        headerStyle: { backgroundColor: '#00796B' },
        headerTintColor: '#fff',
        headerTitleStyle: { fontWeight: 'bold' },
      }}
    >
      <BusinessStack.Screen 
        name="BusinessLogin" 
        component={LoginScreen} 
        options={{ title: 'Business Login' }} 
      />
      <BusinessStack.Screen 
        name="BusinessRegistration" 
        component={RegistrationScreen} 
        options={{ title: 'Register Your Business' }} 
      />
      <BusinessStack.Screen 
        name="Dashboard" 
        component={BusinessDashboardScreen} 
        options={{ title: 'Dashboard', headerLeft: () => null }} // Disable back button after login
      />
      <BusinessStack.Screen 
        name="StaffManagement" 
        component={StaffManagementScreen} 
        options={{ title: 'Manage Staff' }} 
      />
      <BusinessStack.Screen 
        name="BookingManagement" 
        component={BookingManagementScreen} 
        options={{ title: 'Manage Bookings' }} 
      />
    </BusinessStack.Navigator>
  );
}

// --- Main App Component ---

// NOTE: For demonstration, we will start with the Client App. 
// In a real application, you would use authentication state 
// to decide which stack to render.

export default function App() {
  // To test the Business App flow, change ClientAppStack to BusinessAppStack below.
  const MainStack = ClientAppStack; 

  return (
    <PaperProvider>
      <NavigationContainer>
        <MainStack />
      </NavigationContainer>
    </PaperProvider>
  );
}
```
eof

### ⚠️ **CRITICAL: New Dependencies Required**

Since this file introduces navigation, you must install the following libraries before you can build the APK/AAB:

```bash
# Install the core navigation library
npm install @react-navigation/native

# Install the stack navigator (for screen transitions) and its dependencies
npx expo install @react-navigation/stack react-native-screens react-native-safe-area-context
```

### 🚀 Next Steps to Build the APK

1.  **Run the installation commands above.**
2.  **Restart your Expo server** (`npx expo start -c`).
3.  **To Build the APK (Android):**
    ```bash
    npx expo run:android 
    # OR for production build (.aab):
    npx eas build -p android --profile production
    ```
4.  **To Build for iOS:**
    ```bash
    npx eas build -p ios --profile production
    
