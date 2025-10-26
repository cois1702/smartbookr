<?php
// booking.php - Client Appointment Booking Page

// --- START DEBUG BLOCK ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- END DEBUG BLOCK ---

include "db.php"; // Includes the database connection from the Canvas

// Ensure $pdo is available and connected
if (!isset($pdo) || !$pdo instanceof PDO) {
    die("Internal Error: Database connection object (\$pdo) is unavailable.");
}

// --------------------------------------------------------
// --- 1. TWILIO SMS NOTIFICATION FUNCTION ---
// --------------------------------------------------------

/**
 * Sends an SMS using the Twilio API.
 * Returns true on success, or a detailed error string on failure.
 */
function sendSms($to_number, $body) {
    // YOUR TWILIO ACCOUNT DETAILS
    $sid = 'AC2abb1f2ed9680daa0aa6cc6c69159f47';
    // AUTH TOKEN HAS BEEN UPDATED WITH THE USER-PROVIDED TOKEN
    $token = '416c7c471183dd1e8491d97950834b77'; 
    $twilio_number = '+16282370770'; // Your Twilio number

    // Twilio API URL
    $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
    
    // Twilio requires basic authentication
    $auth = base64_encode("{$sid}:{$token}");

    $data = [
        'To' => $to_number,
        'From' => $twilio_number,
        'Body' => $body,
    ];

    $options = [
        'http' => [
            'header'  => "Authorization: Basic {$auth}\r\n" .
                         "Content-Type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'ignore_errors' => true // Necessary to capture Twilio error responses
        ]
    ];
    
    $context  = stream_context_create($options);
    // Use @ to suppress file_get_contents errors that often occur when debugging
    $result = @file_get_contents($url, false, $context);
    
    $http_response_header_safe = $http_response_header ?? [];
    $status_line = $http_response_header_safe[0] ?? '';

    // Check for success (HTTP 200 or 201)
    if (strpos($status_line, '200') !== false || strpos($status_line, '201') !== false) {
        return true;
    } else {
        $response_data = json_decode($result, true);
        $error_message = $response_data['message'] ?? 'Unknown Twilio API Error. Check Auth Token and Twilio phone numbers.';
        return "Twilio SMS Failure ({$status_line}): {$error_message}";
    }
}


// --------------------------------------------------------
// --- 2. Business Fetching Logic 
// --------------------------------------------------------

$business_id_param = $_GET['business_id'] ?? null; 
$business_info = null;
$message = ""; // Initialize message here
$cancellation_link = ""; // Initialize cancellation link variable

if (empty($business_id_param)) {
    header("Location: index.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM businesses WHERE id = ?");
    $stmt->execute([$business_id_param]);
    $business_info = $stmt->fetch();
} catch (\PDOException $e) {
    die("FATAL DB ERROR (Business Fetch): Check if 'businesses' table exists. Error: " . $e->getMessage());
}

if (!$business_info) {
    die("Error: Business not found or invalid ID.");
}

// Set required variables
$business_id = $business_info['id'];
$business_name = $business_info['business_name'];
$brand_color = $business_info['brand_color'] ?? '#0077cc';
$logo_url = $business_info['logo_url'] ?? '';

// FETCH OWNER CONTACTS DYNAMICALLY FROM DB
$owner_email = 'cois1702@gmail.com'; 
// OWNER'S PHONE NUMBER FOR SMS ALERTS (Updated to +27798427846)
$owner_phone = '+27798427846'; 

// --------------------------------------------------------
// --- 3. Handle Form Submission & Notifications 
// --------------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $service_id = $_POST['service_id'] ?? null;
    $customer_name = $_POST['customer_name'] ?? '';
    $customer_email = $_POST['customer_email'] ?? '';
    $customer_phone = $_POST['customer_phone'] ?? '';
    $date = $_POST['appointment_date'] ?? null;
    $time = $_POST['appointment_time'] ?? null;
    $notification_log = ""; // Initialize log storage

    if (!$service_id || !$date || !$time) {
        $message = "Please fill out all required fields.";
    } else {
        try {
            // 3a. Fetch service details for the notification message
            $stmt_service = $pdo->prepare("SELECT name FROM services WHERE id=?");
            $stmt_service->execute([$service_id]);
            $service_details = $stmt_service->fetch();
            $service_name = $service_details['name'] ?? 'Unknown Service';

            // 3b. Insert Appointment
            $stmt = $pdo->prepare("INSERT INTO appointments (business_id, service_id, customer_name, customer_email, customer_phone, appointment_date, appointment_time, status) VALUES (?,?,?,?,?,?,?, 'scheduled')");
            
            if ($stmt->execute([$business_id, $service_id, $customer_name, $customer_email, $customer_phone, $date, $time])) {
                
                $new_appointment_id = $pdo->lastInsertId();
                $cancellation_link = "cancel.php?id=" . $new_appointment_id . "&business_id=" . $business_id;

                // --- 1. Client Notification (On-Screen ONLY, Email Disabled) ---
                $message = "Appointment booked successfully! A confirmation message is displayed below.";

                // --- 2. Send SMS to Business Owner (New Booking Alert) ---
                $owner_alert_body = "[NEW BOOKING] {$business_name} - Service: {$service_name}. Date/Time: {$date} @ {$time}. Client: {$customer_name} ({$customer_phone}).";
                
                // Only attempt to send SMS if the owner has a valid phone number
                if (strlen($owner_phone) > 10) {
                    $owner_sms_status = sendSms($owner_phone, $owner_alert_body); 
                    if ($owner_sms_status !== true) {
                        $notification_log .= "\n\n--- OWNER SMS FAILURE ---\nReason: " . $owner_sms_status;
                    }
                } else {
                    $notification_log .= "\n\n--- OWNER SMS SKIPPED ---\nReason: Owner phone number is missing or invalid. Please update the \$owner_phone variable.";
                }

                // Append all notification error logs to the main message
                if (!empty($notification_log)) {
                    $message .= $notification_log;
                }

            } else {
                $message = "Error booking appointment (Database execution failed).";
            }
        } catch (\PDOException $e) {
            // Handle error
            $message = "Database Error during booking: " . $e->getMessage();
        }
    }
}

// --------------------------------------------------------
// --- 4. Fetch Services for Dropdown 
// --------------------------------------------------------

$services = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE business_id=?");
    $stmt->execute([$business_id]);
    $services = $stmt->fetchAll();
} catch (\PDOException $e) {
    // Handle error fetching services
    $message .= " | FATAL DB ERROR (Service Fetch): Check if 'services' table exists. Error: " . $e->getMessage();
}

// --------------------------------------------------------
// --- 5. HTML Output 
// --------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book with <?php echo htmlspecialchars($business_name);?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: <?php echo $brand_color;?>;
        }
        .text-primary { color: var(--primary-color); }
        .bg-primary { background-color: var(--primary-color); }
        .border-primary { border-color: var(--primary-color); }
        /* Style for pre-formatted error messages */
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 0.85rem;
            line-height: 1.3;
            padding: 0.5rem;
            margin-top: 0.5rem;
            background-color: #fef2f2; /* red-50 */
            border-left: 4px solid #f87171; /* red-400 */
            color: #b91c1c; /* red-800 */
            border-radius: 0.25rem;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <!-- Card padding reduced to 5px -->
    <div class="w-full max-w-lg bg-white p-5 rounded-xl shadow-2xl">
        
        <!-- Header & Branding -->
        <header class="text-center mb-6">
            <?php if($logo_url): ?>
                <!-- Logo max-height updated to 180px -->
                <img src="<?php echo htmlspecialchars($logo_url);?>" alt="<?php echo htmlspecialchars($business_name);?> Logo" class="mx-auto mb-4 rounded-lg" style="max-height: 180px;">
            <?php endif; ?>
            <h1 class="text-3xl font-bold text-primary mb-2"><?php echo htmlspecialchars($business_name);?></h1>
            <p class="text-gray-600">Book your appointment below.</p>
        </header>

        <!-- Message Area -->
        <?php if ($message): ?>
            <div class="p-3 mb-4 rounded-lg <?php echo strpos($message, 'successfully') !== false && strpos($message, 'FAILURE') === false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';?> font-medium">
                
                <?php 
                // Separate the main message from the debug log
                $main_message = $message;
                $error_debug = "";
                
                if (strpos($message, '--- OWNER SMS FAILURE ---') !== false || strpos($message, '--- OWNER SMS SKIPPED ---') !== false) {
                    $parts = preg_split('/(\n\n--- (OWNER SMS FAILURE|OWNER SMS SKIPPED) ---)/', $message, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
                    $main_message = array_shift($parts); // First part is the success message
                    $error_debug = implode('', $parts); // The rest is the debug log
                }
                ?>

                <!-- Main Status Message -->
                <p><?php echo nl2br(htmlspecialchars($main_message)); ?></p>
                <p class="text-xs text-blue-600 mt-2">
                    Note: Email confirmation is currently disabled due to server restrictions.
                </p>

                <!-- Detailed Error/Debug Output -->
                <?php if (!empty($error_debug)): ?>
                    <p class="mt-4 font-bold text-red-900">Notification System Status:</p>
                    <pre><?php echo htmlspecialchars($error_debug); ?></pre>
                    <p class="mt-2 text-sm text-red-800">Please update the Twilio Auth Token and the owner's phone number to complete SMS setup.</p>
                <?php endif; ?>

            </div>
        <?php endif; ?>

        <!-- Cancellation Link (Visible after successful booking) -->
        <?php if ($cancellation_link && strpos($message, 'FAILURE') === false): ?>
            <div class="p-4 mb-6 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 rounded-lg">
                <p class="font-bold">Booking Reference:</p>
                <p>Keep this link to cancel your appointment:</p>
                <a href="<?php echo htmlspecialchars($cancellation_link);?>" class="text-yellow-600 underline font-mono break-all hover:text-yellow-700">
                    <?php echo htmlspecialchars($cancellation_link);?>
                </a>
            </div>
        <?php endif; ?>

        <!-- Booking Form -->
        <form method="POST" class="space-y-4">
            
            <div>
                <label for="service_id" class="block text-sm font-medium text-gray-700">Service Required</label>
                <select id="service_id" name="service_id" required 
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md border-2">
                    <option value="" disabled selected>Select a service...</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?php echo $service['id']; ?>">
                            <?php echo htmlspecialchars($service['name']) . " (" . $service['duration'] . " min, R" . number_format($service['price'], 2) . ")"; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="appointment_date" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" id="appointment_date" name="appointment_date" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 border-2 focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label for="appointment_time" class="block text-sm font-medium text-gray-700">Time</label>
                    <input type="time" id="appointment_time" name="appointment_time" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 border-2 focus:ring-primary focus:border-primary">
                </div>
            </div>

            <div>
                <label for="customer_name" class="block text-sm font-medium text-gray-700">Your Full Name</label>
                <input type="text" id="customer_name" name="customer_name" required 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 border-2 focus:ring-primary focus:border-primary">
            </div>

            <div>
                <label for="customer_email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" id="customer_email" name="customer_email" required 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 border-2 focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="customer_phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                <input type="text" id="customer_phone" name="customer_phone" required 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 border-2 focus:ring-primary focus:border-primary">
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-md text-white bg-primary hover:bg-opacity-90 transition duration-150 font-semibold shadow-md">
                Confirm Booking
            </button>

        </form>
    </div>

</body>
</html>
