// screens/client/ServiceSelectionScreen.js
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useEffect, useState } from 'react';
import { Alert, ScrollView, StyleSheet, Text, View } from 'react-native';
import { Button, Card, Divider, Title } from 'react-native-paper';
import { fetchAvailability } from '../../api/api';

// NOTE: In a final production app, you would need a 'fetch_services.php' 
// API endpoint to retrieve the list of services for the selected business.
// For this example, we use a mock list to demonstrate the booking flow.
const MOCK_SERVICES = [
    { id: 101, name: "Haircut & Style", duration: 45, price: 350, description: "Includes wash and deep conditioning." },
    { id: 102, name: "Beard Trim", duration: 15, price: 100, description: "Quick trim and shape up." },
    { id: 103, name: "Full Colouring", duration: 120, price: 800, description: "Consultation and full colour application." },
];

export default function ServiceSelectionScreen({ route, navigation }) {
    const { business } = route.params;

    const [selectedService, setSelectedService] = useState(null);
    const [selectedDate, setSelectedDate] = useState('');
    const [availability, setAvailability] = useState([]);
    const [loading, setLoading] = useState(false);

    // Helper to get today's date in YYYY-MM-DD format
    const getTodayDate = () => {
        return new Date().toISOString().split('T')[0];
    };

    useEffect(() => {
        // Set today as the default selected date
        setSelectedDate(getTodayDate());
    }, []);

    // --- Core Availability Fetcher ---
    const handleCheckAvailability = async () => {
        if (!selectedService || !selectedDate) {
            Alert.alert('Missing Selection', 'Please select both a service and a date.');
            return;
        }

        setLoading(true);
        setAvailability([]); // Clear previous results

        try {
            // Call the API endpoint we defined
            const response = await fetchAvailability(
                business.id, 
                selectedDate, 
                selectedService.id
            );

            if (response.data.status === 'success') {
                const availableSlots = response.data.availability || [];
                setAvailability(availableSlots);
                if (availableSlots.length === 0) {
                    Alert.alert('No Slots', 'No available time slots found for the selected service and date.');
                }
            } else {
                Alert.alert('Error', response.data.message || 'Failed to fetch availability.');
            }
        } catch (error) {
            console.error('Availability Fetch Error:', error);
            Alert.alert('Network Error', 'Could not connect to the server to check times.');
        } finally {
            setLoading(false);
        }
    };

    // --- Navigation to Final Booking Screen ---
    const handleSelectSlot = (slot) => {
        navigation.navigate('BookingConfirmation', {
            businessId: business.id,
            service: selectedService,
            slot: slot,
            staffId: slot.staff_id,
        });
    };

    // --- Render Service Item ---
    const renderServiceCard = (service) => (
        <Card 
            key={service.id} 
            style={[
                styles.serviceCard, 
                selectedService?.id === service.id && styles.selectedCard
            ]}
            onPress={() => setSelectedService(service)}
        >
            <Card.Title
                title={service.name}
                subtitle={`R${service.price} - ${service.duration} min`}
                left={(props) => (
                    <MaterialCommunityIcons 
                        {...props} 
                        name="scissors-cutting" 
                        size={24} 
                        color={selectedService?.id === service.id ? '#FFFFFF' : '#00796B'}
                    />
                )}
                titleStyle={selectedService?.id === service.id ? styles.selectedText : styles.defaultText}
                subtitleStyle={selectedService?.id === service.id ? styles.selectedText : styles.defaultText}
            />
        </Card>
    );

    return (
        <View style={styles.container}>
            <Title style={styles.header}>Booking: {business.name}</Title>
            <ScrollView contentContainerStyle={styles.scrollContent}>

                {/* 1. Service Selection */}
                <Text style={styles.sectionTitle}>1. Choose a Service</Text>
                {MOCK_SERVICES.map(renderServiceCard)}

                <Divider style={styles.divider} />
                
                {/* 2. Date Selection (Simplified using TextInput for demo) */}
                <Text style={styles.sectionTitle}>2. Select Date (YYYY-MM-DD)</Text>
                <View style={styles.dateRow}>
                    <Text style={styles.dateLabel}>Date:</Text>
                    {/* NOTE: Use a proper DatePicker component in a real app */}
                    <TextInput
                        style={styles.dateInput}
                        value={selectedDate}
                        onChangeText={setSelectedDate}
                        placeholder={getTodayDate()}
                    />
                </View>

                {/* 3. Availability Check Button */}
                <Button 
                    mode="contained" 
                    onPress={handleCheckAvailability}
                    loading={loading}
                    disabled={loading || !selectedService}
                    style={styles.checkButton}
                >
                    {loading ? 'Checking...' : 'Check Available Slots'}
                </Button>

                <Divider style={styles.divider} />

                {/* 4. Available Slots List */}
                <Text style={styles.sectionTitle}>3. Available Time Slots</Text>
                
                {availability.length > 0 ? (
                    <View>
                        {availability.map((slot, index) => (
                            <TouchableOpacity 
                                key={index} 
                                style={styles.slotButton}
                                onPress={() => handleSelectSlot(slot)}
                            >
                                <View style={styles.slotContent}>
                                    <Text style={styles.slotTime}>{slot.time_display}</Text>
                                    <Text style={styles.slotStaff}>with {slot.staff_name}</Text>
                                </View>
                                <MaterialCommunityIcons name="arrow-right-circle" size={24} color="#00796B" />
                            </TouchableOpacity>
                        ))}
                    </View>
                ) : (
                    <Text style={styles.emptySlots}>
                        {loading ? 'Waiting for results...' : 'Select a service and date, then tap "Check Available Slots".'}
                    </Text>
                )}
            </ScrollView>
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#ffffff' },
    header: { fontSize: 22, fontWeight: 'bold', padding: 15, textAlign: 'center', backgroundColor: '#f0f0f0' },
    scrollContent: { padding: 15 },
    sectionTitle: { fontSize: 18, fontWeight: '600', color: '#1E88E5', marginTop: 15, marginBottom: 10 },
    divider: { marginVertical: 20 },
    
    // Service Cards
    serviceCard: { marginVertical: 5, elevation: 2, backgroundColor: '#f9f9f9' },
    selectedCard: { backgroundColor: '#00796B', elevation: 5 },
    defaultText: { color: '#333' },
    selectedText: { color: '#FFFFFF' },

    // Date Input
    dateRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 10 },
    dateLabel: { fontSize: 16, marginRight: 10 },
    dateInput: { 
        flex: 1, 
        borderWidth: 1, 
        borderColor: '#ccc', 
        padding: 10, 
        borderRadius: 5 
    },

    // Button
    checkButton: { marginVertical: 10, paddingVertical: 5, backgroundColor: '#FF5722' },

    // Slots List
    slotButton: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: 15,
        marginVertical: 4,
        backgroundColor: '#E8F5E9',
        borderRadius: 8,
        borderLeftWidth: 5,
        borderLeftColor: '#00796B'
    },
    slotContent: { flexDirection: 'column' },
    slotTime: { fontSize: 16, fontWeight: '600', color: '#333' },
    slotStaff: { fontSize: 12, color: '#555' },
    emptySlots: { textAlign: 'center', padding: 20, color: '#888' }
});