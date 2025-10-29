// screens/StaffManagementScreen.js (UPDATED with DELETE functionality)
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useCallback, useEffect, useState } from 'react';
import { Alert, FlatList, RefreshControl, StyleSheet, Text, View } from 'react-native';
import { ActivityIndicator, Button, Card, Modal, Portal, TextInput, Title } from 'react-native-paper';
import { deleteStaff, manageStaff } from '../api/api'; // IMPORT deleteStaff

export default function StaffManagementScreen({ route }) {
    const { token } = route.params;
    const [staffList, setStaffList] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [isModalVisible, setIsModalVisible] = useState(false);
    
    // State for New Staff Form
    const [newName, setNewName] = useState('');
    const [newEmail, setNewEmail] = useState('');
    const [isAdding, setIsAdding] = useState(false);
    const [deletingId, setDeletingId] = useState(null); // Tracks staff member being deleted

    // --- Fetch Staff Logic (GET) ---
    const loadStaff = useCallback(async () => {
        try {
            const response = await manageStaff(token);
            setStaffList(response.staff || []);
        } catch (error) {
            console.error('Failed to load staff:', error);
            Alert.alert('Error', 'Failed to load staff list. Please check your network.');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, [token]);

    useEffect(() => {
        loadStaff();
    }, [loadStaff]);
    
    const onRefresh = () => {
        setRefreshing(true);
        loadStaff();
    };

    // --- Add Staff Logic (POST) is unchanged ---
    const handleAddStaff = async () => {
        // ... (existing handleAddStaff code remains here) ...
        if (!newName.trim()) {
            Alert.alert('Missing Info', 'Staff name is required.');
            return;
        }

        setIsAdding(true);
        const payload = {
            name: newName.trim(),
            email: newEmail.trim() || null, 
        };

        try {
            const response = await manageStaff(token, payload);

            if (response.data.status === 'success') {
                Alert.alert('Success', `${newName} has been added to staff.`);
                setIsModalVisible(false);
                setNewName('');
                setNewEmail('');
                loadStaff(); // Refresh the list
            } else {
                Alert.alert('Error', response.data.message || 'Failed to add staff member.');
            }
        } catch (error) {
            console.error('Add Staff Error:', error);
            Alert.alert('Network Error', 'Could not connect to the API to add staff.');
        } finally {
            setIsAdding(false);
        }
    };

    // --- NEW: Delete Staff Logic (DELETE) ---
    const confirmDelete = (staffId, staffName) => {
        Alert.alert(
            "Confirm Deletion",
            `Are you sure you want to remove ${staffName}? All their future bookings will also be cancelled.`,
            [
                { text: "Cancel", style: "cancel" },
                { text: "Delete", style: "destructive", onPress: () => handleDeleteStaff(staffId) }
            ]
        );
    };

    const handleDeleteStaff = async (staffId) => {
        setDeletingId(staffId);
        try {
            const response = await deleteStaff(token, staffId); // Call the new API function

            if (response.data.status === 'success') {
                Alert.alert('Deleted', response.data.message);
                loadStaff(); // Refresh the list
            } else {
                // This handles errors like trying to delete the Owner
                Alert.alert('Deletion Error', response.data.message || 'Failed to delete staff member.');
            }
        } catch (error) {
            console.error('Delete Staff Error:', error);
            Alert.alert('Network Error', 'Could not connect to the API to delete staff.');
        } finally {
            setDeletingId(null);
        }
    };

    // --- Render Staff Item (UPDATED to include Delete Button) ---
    const renderStaffItem = ({ item }) => (
        <Card style={styles.staffCard}>
            <View style={styles.cardContent}>
                <MaterialCommunityIcons 
                    name={item.is_owner ? "star-circle" : "account-circle"} 
                    size={30} 
                    color={item.is_owner ? "#FFC107" : "#1E88E5"} 
                />
                <View style={styles.info}>
                    <Text style={styles.staffName}>{item.name}</Text>
                    {item.email && <Text style={styles.staffDetail}>{item.email}</Text>}
                    <Text style={styles.staffRole}>{item.is_owner ? 'Owner/Manager' : (item.role || 'Staff')}</Text>
                </View>
                
                {/* Delete Button Area */}
                <View style={styles.deleteArea}>
                    {!item.is_owner && ( // Only show delete button for non-owners
                        <Button
                            mode="contained"
                            icon="delete"
                            compact
                            onPress={() => confirmDelete(item.id, item.name)}
                            disabled={deletingId === item.id}
                            style={styles.deleteButton}
                            buttonColor="#D32F2F"
                        >
                            {deletingId === item.id ? '...' : 'Delete'}
                        </Button>
                    )}
                </View>
            </View>
        </Card>
    );

    if (loading) {
        return (
            <View style={styles.centerContainer}>
                <ActivityIndicator animating={true} color="#1E88E5" size="large" />
            </View>
        );
    }

    return (
        <View style={styles.container}>
            <Title style={styles.header}>Staff Management ({staffList.length})</Title>
            
            <Button 
                mode="contained" 
                icon="plus"
                onPress={() => setIsModalVisible(true)}
                style={styles.addButton}
            >
                Add New Staff
            </Button>

            <FlatList
                data={staffList}
                renderItem={renderStaffItem}
                keyExtractor={item => item.id.toString()}
                contentContainerStyle={styles.listContent}
                refreshControl={
                    <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
                }
                ListEmptyComponent={() => (
                    <Text style={styles.emptyText}>No staff members found. Add your first employee!</Text>
                )}
            />

            {/* --- Add Staff Modal (Unchanged) --- */}
            <Portal>
                <Modal visible={isModalVisible} onDismiss={() => setIsModalVisible(false)} contentContainerStyle={styles.modalContainer}>
                    <Title style={styles.modalTitle}>Add New Employee</Title>
                    <TextInput
                        label="Full Name *"
                        value={newName}
                        onChangeText={setNewName}
                        style={styles.modalInput}
                        mode="outlined"
                    />
                    <TextInput
                        label="Email (Optional)"
                        value={newEmail}
                        onChangeText={setNewEmail}
                        keyboardType="email-address"
                        autoCapitalize="none"
                        style={styles.modalInput}
                        mode="outlined"
                    />
                    <Button 
                        mode="contained" 
                        onPress={handleAddStaff}
                        loading={isAdding}
                        disabled={isAdding || !newName.trim()}
                        style={styles.modalButton}
                    >
                        {isAdding ? 'Adding...' : 'Save Staff Member'}
                    </Button>
                    <Button 
                        mode="text" 
                        onPress={() => setIsModalVisible(false)}
                        disabled={isAdding}
                    >
                        Cancel
                    </Button>
                </Modal>
            </Portal>
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#f9f9f9', padding: 10 },
    centerContainer: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    header: { fontSize: 24, fontWeight: 'bold', marginVertical: 10, textAlign: 'center' },
    addButton: { marginVertical: 10, paddingVertical: 5, backgroundColor: '#4CAF50' },
    listContent: { paddingBottom: 20 },
    staffCard: { marginVertical: 8, elevation: 2, padding: 10 },
    cardContent: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, // Updated
    info: { marginLeft: 15, flex: 1 },
    staffName: { fontSize: 18, fontWeight: '600' },
    staffDetail: { fontSize: 14, color: '#666' },
    staffRole: { fontSize: 12, color: '#333', marginTop: 2, fontStyle: 'italic' },
    deleteArea: { marginLeft: 10, justifyContent: 'center' }, // New style
    deleteButton: { paddingHorizontal: 0 },
    emptyText: { textAlign: 'center', marginTop: 50, fontSize: 16, color: '#888' },
    
    // Modal Styles
    modalContainer: { backgroundColor: 'white', padding: 20, margin: 20, borderRadius: 8 },
    modalTitle: { fontSize: 20, marginBottom: 15, textAlign: 'center' },
    modalInput: { marginBottom: 10 },
    modalButton: { marginVertical: 10, paddingVertical: 5 },
});