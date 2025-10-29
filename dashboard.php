<?php
include "db.php";
session_start();
if(!isset($_SESSION['business_id'])){
    header("Location: login.php");
    exit;
}

// Load business info from session
$business_id = $_SESSION['business_id'];
$business_name = $_SESSION['business_name'];

// --- Branding Logic ---
// Fetch business info to get logo and color
$business_logo = '';
$business_color = '#0077cc'; // Default color

try {
    // Fetch brand_color and logo_url
    $stmt = $pdo->prepare("SELECT business_name, logo_url, brand_color FROM businesses WHERE id=?");
    $stmt->execute([$business_id]);
    $business_info = $stmt->fetch();
    
    if ($business_info) {
        $business_name = $business_info['business_name']; // Use fetched name
        $business_logo = $business_info['logo_url'] ?? '';
        $business_color = $business_info['brand_color'] ?? '#0077cc';
    }
} catch (PDOException $e) {
    error_log("Database Fetch Error (Business Info): " . $e->getMessage());
}

// PHPMailer (Keep PHPMailer requirements)
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Twilio SMS
function sendTwilioSMS($to, $message){
    // Using remembered credentials and cURL
    $account_sid = 'AC2abb1f2ed9680daa0aa6cc6c69159f47'; // Loaded from memory
    $auth_token = '0f24fd7905299452ef84a5a7e09c4041';     // Loaded from memory
    $twilio_number = '+16282370770';                    // Loaded from memory
    
    $data = ['To'=>$to,'From'=>$twilio_number,'Body'=>$message];
    
    // NOTE: This assumes the Twilio PHP library is not used, relying on a direct cURL call.
    // Ensure cURL is enabled on your server.
    $ch = curl_init("https://api.twilio.com/2010-04-01/Accounts/$account_sid/Messages.json");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_USERPWD, "$account_sid:$auth_token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    
    // Log any errors (optional, for debugging)
    if ($result === false) {
        error_log("Twilio cURL Error: " . curl_error($ch));
    }
    return $result;
}

// --- LOGO AND BRANDING SUBMISSION HANDLER ---
if(isset($_POST['update_branding'])){
    $new_color = $_POST['brand_color'];
    $logo_url_to_save = $business_logo; // Start with current logo URL

    // 1. Handle Logo Upload
    if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'logos/'; // MUST exist and be writable (chmod 755 or 777)!
        $file_info = pathinfo($_FILES['logo_file']['name']);
        $extension = strtolower($file_info['extension']);
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($extension, $allowed_ext)) {
            // Create a unique filename based on business ID and timestamp
            $filename = $business_id . '_' . time() . '.' . $extension;
            $upload_path = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $upload_path)) {
                $logo_url_to_save = $upload_path;
            } else {
                $_SESSION['error'] = "Error uploading logo file. Check 'logos/' folder permissions.";
            }
        } else {
            $_SESSION['error'] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        }
    }
    
    // 2. Update Database with new URL and color
    $stmt = $pdo->prepare("UPDATE businesses SET logo_url = ?, brand_color = ? WHERE id = ?");
    $stmt->execute([$logo_url_to_save, $new_color, $business_id]);
    
    $_SESSION['success'] = "Branding updated successfully!";
    // Redirect to ensure branding changes are reflected
    header("Location: dashboard.php");
    exit;
}

// --- Services ---
if(isset($_POST['add_service'])){
    $stmt = $pdo->prepare("INSERT INTO services (business_id, name, duration, price) VALUES (?,?,?,?)");
    $stmt->execute([$business_id,$_POST['service_name'],$_POST['duration'],$_POST['price']]);
    header("Location: dashboard.php#services");
    exit;
}
if(isset($_POST['edit_service'])){
    $stmt = $pdo->prepare("UPDATE services SET name=?, duration=?, price=? WHERE id=? AND business_id=?");
    $stmt->execute([$_POST['service_name'],$_POST['duration'],$_POST['price'],$_POST['service_id'],$business_id]);
    header("Location: dashboard.php#services");
    exit;
}
if(isset($_GET['delete_service'])){
    $stmt = $pdo->prepare("DELETE FROM services WHERE id=? AND business_id=?");
    $stmt->execute([$_GET['delete_service'],$business_id]);
    header("Location: dashboard.php#services");
    exit;
}
$edit_service = null;
if(isset($_GET['edit_service'])){
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id=? AND business_id=?");
    $stmt->execute([$_GET['edit_service'],$business_id]);
    $edit_service = $stmt->fetch();
}
$stmt = $pdo->prepare("SELECT * FROM services WHERE business_id=?");
$stmt->execute([$business_id]);
$services = $stmt->fetchAll();

// --- Staff ---
if(isset($_POST['add_staff'])){
    $stmt = $pdo->prepare("INSERT INTO staff (business_id, name, email, phone, role) VALUES (?,?,?,?,?)");
    $stmt->execute([$business_id,$_POST['staff_name'],$_POST['staff_email'],$_POST['staff_phone'],$_POST['staff_role']]);
    header("Location: dashboard.php#staff");
    exit;
}
if(isset($_GET['delete_staff'])){
    $stmt = $pdo->prepare("DELETE FROM staff WHERE id=? AND business_id=?");
    $stmt->execute([$_GET['delete_staff'],$business_id]);
    header("Location: dashboard.php#staff");
    exit;
}
$stmt = $pdo->prepare("SELECT * FROM staff WHERE business_id=?");
$stmt->execute([$business_id]);
$staff_list = $stmt->fetchAll();

// --- Appointments ---
if(isset($_POST['book_appointment'])){
    $staff_id = empty($_POST['staff_id']) ? null : $_POST['staff_id'];

    $stmt = $pdo->prepare("INSERT INTO appointments (business_id, customer_name, customer_email, customer_phone, service_id, staff_id, appointment_date, appointment_time, status) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$business_id,$_POST['customer_name'],$_POST['customer_email'],$_POST['customer_phone'],$_POST['service_id'], $staff_id ,$_POST['appointment_date'],$_POST['appointment_time'],'scheduled']);
    header("Location: dashboard.php#appointments");
    exit;
}

// FIX: Corrected the appointment ID parameter. This handles Complete and Cancel.
if(isset($_GET['update_status']) && isset($_GET['status'])){
    $stmt = $pdo->prepare("UPDATE appointments SET status=? WHERE id=? AND business_id=?");
    // Corrected the parameter: using $_GET['update_status'] as the ID
    $stmt->execute([$_GET['status'], $_GET['update_status'], $business_id]); 
    header("Location: dashboard.php#appointments");
    exit;
}

// NEW: Logic to handle permanent deletion
if(isset($_GET['delete_appointment'])){
    $stmt = $pdo->prepare("DELETE FROM appointments WHERE id=? AND business_id=?");
    $stmt->execute([$_GET['delete_appointment'], $business_id]);
    header("Location: dashboard.php#appointments");
    exit;
}

$stmt = $pdo->prepare("SELECT a.*, s.name as service_name, st.name as staff_name FROM appointments a JOIN services s ON a.service_id=s.id LEFT JOIN staff st ON a.staff_id=st.id WHERE a.business_id=? ORDER BY a.appointment_date,a.appointment_time");
$stmt->execute([$business_id]);
$appointments = $stmt->fetchAll();

// --- Reminder Templates ---
if(isset($_POST['update_template'])){
    $stmt = $pdo->prepare("SELECT id FROM reminder_templates WHERE business_id=? AND type=?");
    $stmt->execute([$business_id,$_POST['type']]);
    $existing = $stmt->fetch();
    if($existing){
        $stmt = $pdo->prepare("UPDATE reminder_templates SET subject=?, message=? WHERE id=?");
        $stmt->execute([$_POST['subject'],$_POST['message'],$existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO reminder_templates (business_id,type,subject,message) VALUES (?,?,?,?)");
        $stmt->execute([$business_id,$_POST['type'],$_POST['subject'],$_POST['message']]);
    }
    header("Location: dashboard.php#templates");
    exit;
}
$stmt = $pdo->prepare("SELECT * FROM reminder_templates WHERE business_id=?");
$stmt->execute([$business_id]);
$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
$template_map = [];
foreach($templates as $t) $template_map[$t['type']] = $t;

// --- Test Reminder ---
if(isset($_POST['send_test'])){
    $test_email = $_POST['test_email'];
    $test_phone = $_POST['test_phone'];
    $status='sent'; $error='';
    
    // Email Test Logic (simplified)
    if(isset($template_map['email'])){
        try{
            $mail = new PHPMailer(true);
            // ... configure SMTP settings here ...
            $mail->setFrom('no-reply@smartbookr.com','SmartBookr');
            $mail->addAddress($test_email);
            $mail->isHTML(true);
            $mail->Subject=$template_map['email']['subject'];
            $mail->Body=$template_map['email']['message'];
            $mail->send();
        }catch(Exception $e){ $status='failed'; $error=$mail->ErrorInfo; }
        $stmt = $pdo->prepare("INSERT INTO reminder_logs (appointment_id,business_id,type,recipient,status,error_message) VALUES (?,?,?,?,?,?)");
        $stmt->execute([0,$business_id,'email',$test_email,$status,$error]);
    }

    // SMS Test Logic (simplified)
    if(isset($template_map['sms'])){
        try{ sendTwilioSMS($test_phone,$template_map['sms']['message']); }catch(Exception $e){ $status='failed'; $error=$e->getMessage(); }
        $stmt = $pdo->prepare("INSERT INTO reminder_logs (appointment_id,business_id,type,recipient,status,error_message) VALUES (?,?,?,?,?,?)");
        $stmt->execute([0,$business_id,'sms',$test_phone,$status,$error]);
    }
    $test_sent=true;
}

// Display session messages
$message = '';
if (isset($_SESSION['success'])) {
    $message = '<p style="color:green;">' . $_SESSION['success'] . '</p>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $message = '<p style="color:red;">' . $_SESSION['error'] . '</p>';
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SmartBookr — <?php echo htmlspecialchars($business_name);?> Dashboard</title>
<link rel="stylesheet" href="css/style.css">
<style>
:root {
    --primary-color: <?php echo $business_color; ?>;
}
</style>
</head>
<body>
<header>
<div style="display:flex;align-items:center;">
<?php if($business_logo):?><img src="<?php echo htmlspecialchars($business_logo);?>" alt="Logo"><?php endif;?>
<h1><?php echo htmlspecialchars($business_name);?> Dashboard</h1>
</div>
<a href="logout.php">Logout</a>
</header>
<main>
<?php echo $message; // Display success/error messages ?>

<section id="book">
<h2>Book Appointment</h2>
<form method="post">
<label>Name: <input type="text" name="customer_name" required></label>
<label>Email: <input type="email" name="customer_email" required></label>
<label>Phone: <input type="text" name="customer_phone" required></label>
<label>Service:
<select name="service_id" required>
<option value="">Select Service</option>
<?php foreach($services as $s):?>
<option value="<?php echo $s['id'];?>"><?php echo htmlspecialchars($s['name']);?></option>
<?php endforeach;?>
</select>
</label>
<label>Staff (optional):
<select name="staff_id">
<option value="">Any</option>
<?php foreach($staff_list as $st):?>
<option value="<?php echo $st['id'];?>"><?php echo htmlspecialchars($st['name']);?></option>
<?php endforeach;?>
</select>
</label>
<label>Date: <input type="date" name="appointment_date" required></label>
<label>Time: <input type="time" name="appointment_time" required></label>
<button type="submit" name="book_appointment">Book Appointment</button>
</form>
</section>

<section id="appointments">
<h2>Appointments</h2>
<table>
<tr><th>Date</th><th>Time</th><th>Customer</th><th>Service</th><th>Staff</th><th>Status</th><th>Actions</th></tr>
<?php foreach($appointments as $a):?>
<tr>
<td><?php echo $a['appointment_date'];?></td>
<td><?php echo $a['appointment_time'];?></td>
<td><?php echo htmlspecialchars($a['customer_name']);?></td>
<td><?php echo htmlspecialchars($a['service_name']);?></td>
<td><?php echo htmlspecialchars($a['staff_name'] ?? 'Any');?></td>
<td><?php echo ucfirst($a['status']);?></td>
<td class="actions">
<a href="dashboard.php?update_status=<?php echo $a['id'];?>&status=completed#appointments" class="edit">Complete</a>
<a href="dashboard.php?update_status=<?php echo $a['id'];?>&status=canceled#appointments" class="delete">Cancel</a>
<a href="dashboard.php?delete_appointment=<?php echo $a['id'];?>#appointments" onclick="return confirm('Are you sure you want to permanently delete this appointment?');" class="delete">Delete</a>
</td>
</tr>
<?php endforeach;?>
</table>
</section>

<section id="services">
<h2>Services</h2>
<form method="post">
<input type="hidden" name="service_id" value="<?php echo $edit_service['id'] ?? '';?>">
<label>Service Name: <input type="text" name="service_name" value="<?php echo $edit_service['name'] ?? '';?>" required></label>
<label>Duration (minutes): <input type="number" name="duration" value="<?php echo $edit_service['duration'] ?? '';?>" required></label>
<label>Price: <input type="number" step="0.01" name="price" value="<?php echo $edit_service['price'] ?? '';?>" required></label>
<button type="submit" name="<?php echo isset($edit_service)?'edit_service':'add_service';?>"><?php echo isset($edit_service)?'Update':'Add';?> Service</button>
</form>
<table>
<tr><th>Name</th><th>Duration</th><th>Price</th><th>Actions</th></tr>
<?php foreach($services as $s):?>
<tr>
<td><?php echo htmlspecialchars($s['name']);?></td>
<td><?php echo $s['duration'];?> min</td>
<td>R<?php echo number_format($s['price'], 2);?></td>
<td class="actions">
<a href="dashboard.php?edit_service=<?php echo $s['id'];?>#services" class="edit">Edit</a>
<a href="dashboard.php?delete_service=<?php echo $s['id'];?>" class="delete">Delete</a>
</td>
</tr>
<?php endforeach;?>
</table>
</section>

<section id="staff">
<h2>Staff</h2>
<form method="post">
<label>Name: <input type="text" name="staff_name" required></label>
<label>Email: <input type="email" name="staff_email" required></label>
<label>Phone: <input type="text" name="staff_phone" required></label>
<label>Role: <input type="text" name="staff_role" required></label>
<button type="submit" name="add_staff">Add Staff</button>
</form>
<table>
<tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Actions</th></tr>
<?php foreach($staff_list as $st):?>
<tr>
<td><?php echo htmlspecialchars($st['name']);?></td>
<td><?php echo htmlspecialchars($st['email']);?></td>
<td><?php echo htmlspecialchars($st['phone']);?></td>
<td><?php echo htmlspecialchars($st['role']);?></td>
<td class="actions">
<a href="dashboard.php?delete_staff=<?php echo $st['id'];?>" class="delete">Delete</a>
</td>
</tr>
<?php endforeach;?>
</table>
</section>

<section id="templates">
<h2>Reminder Templates</h2>
<form method="post">
<label>Type:
<select name="type" required>
<option value="email" <?php echo (isset($template_map['email'])?'selected':'');?>>Email</option>
<option value="sms" <?php echo (isset($template_map['sms'])?'selected':'');?>>SMS</option>
</select>
</label>
<label>Subject: <input type="text" name="subject" value="<?php echo $template_map['email']['subject'] ?? '';?>" required></label>
<label>Message: <textarea name="message" required><?php echo $template_map['email']['message'] ?? '';?></textarea></label>
<button type="submit" name="update_template">Save Template</button>
</form>
<h3>Send Test Reminder</h3>
<?php if(isset($test_sent) && $test_sent):?><p style="color:green;">Test reminder sent successfully!</p><?php endif;?>
<form method="post">
<label>Test Email: <input type="email" name="test_email" required></label>
<label>Test Phone: <input type="text" name="test_phone" required></label>
<button type="submit" name="send_test">Send Test Reminder</button>
</form>
</section>

<section style="flex: 1 1 100%;">
    <details>
        <summary>
            <h2 style="display: inline-block; margin: 0; color: #555; cursor: pointer;">⚙️ Branding & Settings</h2>
        </summary>
        
        <div style="padding: 20px; border-top: 1px solid #eee;">
            <p style="font-style: italic; color: #777;">Use this section to upload your business logo and set your primary brand color.</p>
            <form method="post" enctype="multipart/form-data" style="max-width: 400px; padding: 0;">
                
                <?php if($business_logo):?>
                    <p>Current Logo: <img src="<?php echo htmlspecialchars($business_logo);?>" style="max-height: 40px; border: 1px solid #ccc;"></p>
                <?php else: ?>
                    <p>No logo currently uploaded.</p>
                <?php endif;?>

                <label>Upload New Logo (.jpg, .png, .gif): 
                    <input type="file" name="logo_file" accept="image/*" style="padding: 4px;">
                </label>
                
                <label>Brand Color (Hex Code): 
                    <input type="color" name="brand_color" value="<?php echo htmlspecialchars($business_color);?>" required style="height: 40px; padding: 2px;">
                </label>

                <button type="submit" name="update_branding">Save Branding</button>
            </form>
        </div>
    </details>
</section>

</main>
</body>
</html>