// screens/ScheduleViewScreen.js
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useEffect, useState } from 'react';
import { Alert, FlatList, ScrollView, StyleSheet, Text, View } from 'react-native';
import { ActivityIndicator, Button, Card, Modal, Portal, TextInput, Title } from 'react-native-paper';
import { createBooking, manageStaff } from '../api/api';
// Note: We use the schedule passed via route, but we need manageStaff and createBooking

// You might need a date picker library like @react-native-community/datetimepicker
// or react-native-modal-datetime-picker for a production app. 
// For this example, we'll use simple text inputs for date/time.

export default function ScheduleViewScreen({ route }) {
    // Get initial data and token from the dashboard
    const { token, schedule: initialSchedule } = route.params;

    const [schedule, setSchedule] = useState(initialSchedule);
    const [staffList, setStaffList] = useState([]);
    const [isStaffLoading, setIsStaffLoading] = useState(true);
    const [isBookingModalVisible, setIsBookingModalVisible] = useState(false);
    const [isCreatingBooking, setIsCreatingBooking] = useState(false);
    
    // State for New Booking Form (Simplified for this example)
    const [bookingData, setBookingData] = useState({
        staff_id: '',
        client_name: '',
        service_name: '',
        start_time: '', // Format: YYYY-MM-DD HH:MM:SS
        end_time: '',   // Format: YYYY-MM-DD HH:MM:SS
    });

    // --- 1. Load Staff List (Required for Booking Form) ---
    useEffect(() => {
        const loadStaffList = async () => {
            try {
                const response = await manageStaff(token);
                // Filter out the owner/manager from the staff selection
                const staffForBooking = (response.staff || []).filter(s => !s.is_owner);
                setStaffList(staffForBooking);
                // Automatically select the first staff member if available
                if (staffForBooking.length > 0) {
                    setBookingData(prev => ({ ...prev, staff_id: staffForBooking[0].id }));
                }
            } catch (error) {
                console.error('Failed to load staff list:', error);
                Alert.alert('Error', 'Failed to load staff list for booking form.');
            } finally {
                setIsStaffLoading(false);
            }
        };
        loadStaffList();
    }, [token]);

    // --- 2. Handle Booking Creation (POST) ---
    const handleCreateBooking = async () => {
        const { staff_id, client_name, start_time, end_time, service_name } = bookingData;

        if (!staff_id || !client_name || !start_time || !end_time || !service_name) {
            Alert.alert('Missing Info', 'Please fill in all required fields.');
            return;
        }

        setIsCreatingBooking(true);
        try {
            const response = await createBooking(token, bookingData);

            if (response.data.status === 'success') {
                Alert.alert('Success', `Booking for ${client_name} created.`);
                setIsBookingModalVisible(false);
                // Optionally refresh the schedule list here if you had a fetchSchedule function
                // setSchedule(prev => [...prev, newBookingData]); 
            } else {
                Alert.alert('Booking Conflict', response.data.message || 'Failed to create booking.');
            }
        } catch (error) {
            console.error('Create Booking Error:', error);
            Alert.alert('Error', 'A network or server error occurred while creating the booking.');
        } finally {
            setIsCreatingBooking(false);
        }
    };
    
    // --- Render Schedule Item ---
    const renderBookingItem = ({ item }) => (
        <Card style={styles.bookingCard}>
            <Card.Title
                title={item.service_name || 'Service Unspecified'}
                subtitle={`${item.client_name || 'Client'} - ${item.staff_name}`}
                left={(props) => (
                    <MaterialCommunityIcons {...props} name="calendar-check" size={24} color="#00796B" />
                )}
            />
            <Card.Content>
                <Text style={styles.timeText}>
                    {new Date(item.start_time).toLocaleTimeString()} - {new Date(item.end_time).toLocaleTimeString()}
                </Text>
            </Card.Content>
        </Card>
    );

    const Header = () => (
        <View>
            <Title style={styles.screenTitle}>Today's Schedule</Title>
            <Button
                mode="contained"
                icon="plus-circle-outline"
                onPress={() => setIsBookingModalVisible(true)}
                style={styles.newBookingButton}
                disabled={isStaffLoading}
            >
                Create New Booking
            </Button>
            <Text style={styles.listHeader}>Total Bookings: {schedule.length}</Text>
        </View>
    );

    return (
        <View style={styles.container}>
            <FlatList
                data={schedule}
                renderItem={renderBookingItem}
                keyExtractor={item => item.shift_id.toString()}
                ListHeaderComponent={Header}
                contentContainerStyle={styles.listContent}
                ListEmptyComponent={() => (
                    <Text style={styles.emptyText}>No shifts or bookings found for today.</Text>
                )}
            />

            {/* --- Create Booking Modal --- */}
            <Portal>
                <Modal visible={isBookingModalVisible} onDismiss={() => setIsBookingModalVisible(false)} contentContainerStyle={styles.modalContainer}>
                    <ScrollView>
                        <Title style={styles.modalTitle}>New Client Booking</Title>
                        
                        {/* Simplified Staff Selection (Requires custom Picker component in real app) */}
                        <Text style={styles.pickerLabel}>Staff Member:</Text>
                        {isStaffLoading ? (
                            <ActivityIndicator animating={true} color="#1E88E5" />
                        ) : (
                            <Text style={styles.staffSelection}>
                                {staffList.find(s => s.id === bookingData.staff_id)?.name || 'Select Staff'}
                            </Text>
                            // TODO: Replace with a <Picker> component (e.g., @react-native-picker/picker)
                        )}
                        <TextInput
                            label="Client Name *"
                            value={bookingData.client_name}
                            onChangeText={text => setBookingData(prev => ({ ...prev, client_name: text }))}
                            style={styles.modalInput}
                            mode="outlined"
                        />
                        <TextInput
                            label="Service Name/Type *"
                            value={bookingData.service_name}
                            onChangeText={text => setBookingData(prev => ({ ...prev, service_name: text }))}
                            style={styles.modalInput}
                            mode="outlined"
                        />
                        <TextInput
                            label="Start Date/Time (e.g., 2025-10-30 14:00:00) *"
                            value={bookingData.start_time}
                            onChangeText={text => setBookingData(prev => ({ ...prev, start_time: text }))}
                            style={styles.modalInput}
                            mode="outlined"
                        />
                        <TextInput
                            label="End Date/Time (e.g., 2025-10-30 15:00:00) *"
                            value={bookingData.end_time}
                            onChangeText={text => setBookingData(prev => ({ ...prev, end_time: text }))}
                            style={styles.modalInput}
                            mode="outlined"
                        />

                        <Button 
                            mode="contained" 
                            onPress={handleCreateBooking}
                            loading={isCreatingBooking}
                            disabled={isCreatingBooking}
                            style={styles.modalButton}
                        >
                            {isCreatingBooking ? 'Creating...' : 'Confirm Booking'}
                        </Button>
                        <Button 
                            mode="text" 
                            onPress={() => setIsBookingModalVisible(false)}
                            disabled={isCreatingBooking}
                        >
                            Cancel
                        </Button>
                    </ScrollView>
                </Modal>
            </Portal>
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#f9f9f9', padding: 10 },
    listContent: { paddingBottom: 20 },
    screenTitle: { fontSize: 26, fontWeight: 'bold', marginVertical: 10, textAlign: 'center' },
    newBookingButton: { marginVertical: 10, paddingVertical: 5, backgroundColor: '#FF5722' },
    listHeader: { fontSize: 18, fontWeight: '600', padding: 5, marginTop: 10, borderBottomWidth: 1, borderColor: '#ccc' },
    bookingCard: { marginVertical: 8, elevation: 2 },
    timeText: { fontSize: 16, color: '#444' },
    emptyText: { textAlign: 'center', marginTop: 50, fontSize: 16, color: '#888' },
    
    // Modal Styles
    modalContainer: { backgroundColor: 'white', padding: 20, margin: 20, borderRadius: 8, maxHeight: '90%' },
    modalTitle: { fontSize: 22, marginBottom: 15, textAlign: 'center' },
    modalInput: { marginBottom: 10 },
    modalButton: { marginVertical: 10, paddingVertical: 5 },
    pickerLabel: { fontSize: 16, color: '#333', marginTop: 10, marginBottom: 5 },
    staffSelection: { borderColor: '#ccc', borderWidth: 1, padding: 15, borderRadius: 4, marginBottom: 10, backgroundColor: '#eee' }
});