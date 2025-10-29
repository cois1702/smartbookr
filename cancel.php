<?php
// cancel.php - Handles the DELETION of an appointment

// --- START DEBUG BLOCK ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- END DEBUG BLOCK ---

include "db.php"; 

$message = "";
$appointment_id = $_GET['id'] ?? null;
$business_id = $_GET['business_id'] ?? null; // For redirection/context

if (empty($appointment_id)) {
    die("Error: No appointment ID provided for deletion.");
}

// CRITICAL FIX: Ensure the ID is a valid integer before proceeding.
if (!filter_var($appointment_id, FILTER_VALIDATE_INT) || $appointment_id < 1) {
    $message = "Error: Invalid appointment ID format provided. The ID must be a positive number.";
    // Skip database attempt and jump to display result
} else {
    // Proceed with deletion logic only if ID is valid
    if (!isset($pdo) || !$pdo instanceof PDO) {
        die("Internal Error: Database connection object (\$pdo) is unavailable.");
    }

    try {
        // --- STEP 1: VERIFY RECORD EXISTENCE & LOGIC CHECK ---
        $stmt_check = $pdo->prepare("SELECT id FROM appointments WHERE id = ?");
        $stmt_check->execute([$appointment_id]);
        $record_exists = $stmt_check->fetch();
        
        if (!$record_exists) {
            // Case where ID is valid format, but doesn't exist in the table.
            $message = "Warning: Appointment ID #{$appointment_id} was not found in the database. Deletion failed.";
            
        } else {
            // Case where record is found, proceed with deletion.
            
            // 2. Prepare the SQL statement for PERMANENT DELETION
            $sql = "DELETE FROM appointments WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            
            // --- DEBUG: Display the SQL being executed ---
            $message .= "<p class='font-mono text-xs mt-2'>DEBUG SQL: " . htmlspecialchars($sql) . "</p>";
            $message .= "<p class='font-mono text-xs'>DEBUG ID: " . htmlspecialchars($appointment_id) . "</p>";
            // --- END DEBUG ---
            
            // 3. Execute the deletion
            $stmt->execute([$appointment_id]);
            
            // 4. Check if any row was affected
            if ($stmt->rowCount() > 0) {
                // Updated success message for deletion
                $message = "Success: Appointment ID #{$appointment_id} has been successfully deleted (permanently removed from the system).";
            } else {
                // This shouldn't happen if the record_exists check passed, but acts as a final safety net.
                $message = "Warning: Appointment ID #{$appointment_id} was found but deletion failed (Row Count was 0). Database may be locked or table structure is wrong.";
            }
        }

    } catch (\PDOException $e) {
        // CRITICAL: Catch database errors and display them clearly.
        $message = "FATAL DATABASE ERROR: " . $e->getMessage() . "<br>Please check your `appointments` table column names.";
    }
}


// Redirect back to the booking page or display the result
$redirect_url = $business_id ? "booking.php?business_id=" . htmlspecialchars($business_id) : "index.php";

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Appointment Deleted</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    body { font-family: 'Inter', sans-serif; background-color: #f7f7f9; }
</style>
</head>
<body class="p-4 md:p-8 flex flex-col items-center min-h-screen justify-center">
    <div class="max-w-md w-full bg-white shadow-xl rounded-lg p-6 md:p-10 text-center">
        <h1 class="text-2xl font-bold mb-4">Deletion Status</h1>
        
        <div class="p-4 rounded-lg text-sm mb-6 <?php echo strpos($message, 'Success') !== false ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
            <?php echo $message; ?>
        </div>

        <a href="<?php echo htmlspecialchars($redirect_url); ?>" class="inline-block bg-indigo-600 text-white p-3 rounded-lg font-semibold hover:bg-indigo-700 transition duration-150 shadow-md">
            Return to Booking Page
        </a>
    </div>
</body>
</html>
