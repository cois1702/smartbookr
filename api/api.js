import axios from 'axios';

// 🛑 IMPORTANT: Use your secure HTTPS domain for the Smartbookr API base URL
const SMARTBOOKR_API_BASE = 'https://yourdomain.co.za/api/smartbookr'; 


// -----------------------------------------------------------------
// 1. REGISTRATION & AUTHENTICATION
// -----------------------------------------------------------------

/**
 * Handles the registration of a new business and owner account.
 * (POST to register_business.php)
 */
export const registerBusiness = async (registrationData) => {
    return axios.post(`${SMARTBOOKR_API_BASE}/register_business.php`, registrationData);
};

/**
 * Handles the login of a business owner.
 * (POST to login_business.php)
 */
export const loginBusiness = async (credentials) => {
    return axios.post(`${SMARTBOOKR_API_BASE}/login_business.php`, credentials);
};


// -----------------------------------------------------------------
// 2. CORE BOOKING & MANAGEMENT FUNCTIONS (Require Token)
// -----------------------------------------------------------------

/**
 * Helper function to create the authenticated config object.
 */
const getConfig = (token) => ({
    headers: {
        'Authorization': `Bearer ${token}`, // Used by validate_token.php
        'Content-Type': 'application/json',
    },
});

/**
 * Fetches the current schedule and bookings for the authenticated business.
 * (GET to fetch_schedule.php)
 */
export const fetchSchedule = async (token) => {
    const response = await axios.get(`${SMARTBOOKR_API_BASE}/fetch_schedule.php`, getConfig(token));
    return response.data;
};

/**
 * Creates a new client booking.
 * (POST to create_booking.php)
 */
export const createBooking = async (token, bookingPayload) => {
    return axios.post(`${SMARTBOOKR_API_BASE}/create_booking.php`, bookingPayload, getConfig(token));
};

/**
 * Manages staff: Fetch (GET) or Add (POST).
 * This function is versatile and handles both actions based on method/payload.
 * (GET/POST to manage_staff.php)
 */
export const manageStaff = async (token, staffPayload = null) => {
    if (staffPayload) {
        // POST: Add new staff member
        return axios.post(`${SMARTBOOKR_API_BASE}/manage_staff.php`, staffPayload, getConfig(token));
    } else {
        // GET: Fetch all staff members
        const response = await axios.get(`${SMARTBOOKR_API_BASE}/manage_staff.php`, getConfig(token));
        return response.data;
    }
};