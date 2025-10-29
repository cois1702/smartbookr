// screens/BusinessDashboardScreen.js
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useCallback, useEffect, useState } from 'react';
import { RefreshControl, ScrollView, StyleSheet, Text, View } from 'react-native';
import { ActivityIndicator, Button, Card, Paragraph, Title } from 'react-native-paper';
import { fetchSchedule, manageStaff } from '../api/api'; // IMPORT API FUNCTIONS

export default function BusinessDashboardScreen({ route, navigation }) {
    // 1. Get Authentication Data and Business Info from the route parameters
    const { token, businessInfo } = route.params;

    const [schedule, setSchedule] = useState([]);
    const [staffCount, setStaffCount] = useState(0); 
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    // --- Core Data Fetching Logic ---
    const loadDashboardData = useCallback(async () => {
        try {
            // 1. Fetch Schedule
            const scheduleResponse = await fetchSchedule(token);
            setSchedule(scheduleResponse.schedule || []);
            
            // 2. Fetch Staff Count
            const staffResponse = await manageStaff(token); // Passing null triggers the GET request
            setStaffCount(staffResponse.staff ? staffResponse.staff.length : 0);

        } catch (error) {
            console.error('Dashboard Load Error:', error);
            // In a real app, you would log out the user if the token is invalid (401 error)
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, [token]);


    // --- Lifecycle and Refresh ---
    useEffect(() => {
        loadDashboardData();
    }, [loadDashboardData]);

    const onRefresh = () => {
        setRefreshing(true);
        loadDashboardData();
    };


    // --- Placeholder Actions ---
    const handleViewSchedule = () => navigation.navigate('ScheduleView', { token, schedule });
    const handleManageStaff = () => navigation.navigate('StaffManagement', { token });
    const handleSettings = () => navigation.navigate('BusinessSettings', { token });


    // --- Loading State UI ---
    if (loading) {
        return (
            <View style={styles.centerContainer}>
                <ActivityIndicator animating={true} color="#1E88E5" size="large" />
                <Text style={{ marginTop: 10 }}>Loading Dashboard...</Text>
            </View>
        );
    }
    
    // Calculate stats based on fetched data
    const todayBookings = schedule.length;
    const businessName = businessInfo.name || 'Your Business'; 

    return (
        <ScrollView 
            style={styles.container}
            refreshControl={
                <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
            }
        >
            <Title style={styles.headerTitle}>Welcome Back, {businessName}!</Title>

            <View style={styles.statsContainer}>
                <Card style={styles.statCard}>
                    <Card.Content style={styles.statContent}>
                        <MaterialCommunityIcons name="calendar-clock" size={32} color="#1E88E5" />
                        <Title style={styles.statNumber}>{todayBookings}</Title>
                        <Paragraph style={styles.statLabel}>Bookings Today</Paragraph>
                    </Card.Content>
                </Card>
                <Card style={styles.statCard}>
                    <Card.Content style={styles.statContent}>
                        <MaterialCommunityIcons name="account-group" size={32} color="#4CAF50" />
                        <Title style={styles.statNumber}>{staffCount}</Title>
                        <Paragraph style={styles.statLabel}>Active Staff</Paragraph>
                    </Card.Content>
                </Card>
            </View>

            <View style={styles.buttonContainer}>
                <Button 
                    mode="contained" 
                    icon="calendar-month"
                    onPress={handleViewSchedule}
                    style={styles.actionButton}
                >
                    View Schedule ({schedule.length} shifts)
                </Button>
                <Button 
                    mode="contained" 
                    icon="account-settings-outline"
                    onPress={handleManageStaff}
                    style={styles.actionButton}
                    buttonColor="#FF9800"
                >
                    Manage Staff
                </Button>
                <Button 
                    mode="outlined" 
                    icon="cog-outline"
                    onPress={handleSettings}
                    style={styles.actionButton}
                    textColor="#1E88E5"
                >
                    Settings
                </Button>
            </View>
        </ScrollView>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, padding: 20, backgroundColor: '#f9f9f9' },
    centerContainer: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    headerTitle: { fontSize: 28, fontWeight: 'bold', marginBottom: 20, color: '#333' },
    statsContainer: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 30 },
    statCard: { width: '48%', elevation: 4 },
    statContent: { alignItems: 'center', paddingVertical: 15 },
    statNumber: { fontSize: 36, fontWeight: 'bold', color: '#333', marginTop: 5 },
    statLabel: { fontSize: 14, color: '#666' },
    buttonContainer: { marginTop: 20 },
    actionButton: { marginVertical: 8, paddingVertical: 8, backgroundColor: '#1E88E5' },
});