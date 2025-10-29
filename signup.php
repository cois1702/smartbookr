<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input to prevent malicious code
    $business_name = trim($_POST['business_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$business_name || !$email || !$password || !$confirm_password) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM businesses WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email already registered.";
            } else {
                // Hash password before storing
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert the business into the database
                $stmt = $pdo->prepare("INSERT INTO businesses (business_name, email, password) VALUES (?,?,?)");
                $stmt->execute([$business_name, $email, $password_hash]);

                // Set session variables
                $business_id = $pdo->lastInsertId();
                $_SESSION['business_id'] = $business_id;
                $_SESSION['business_name'] = $business_name;

                // Success message
                $success = "Registration successful! Redirecting to dashboard...";

                // Redirect after a short delay (optional)
                header("Refresh:2; url=dashboard.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SmartBookr — Signup</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<h1>SmartBookr Signup</h1>

<!-- Display Success or Error Messages -->
<?php if ($success): ?>
<p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
<?php elseif ($error): ?>
<p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="post">
    <label>Business Name:<br>
        <input type="text" name="business_name" required>
    </label><br><br>

    <label>Email:<br>
        <input type="email" name="email" required>
    </label><br><br>

    <label>Password:<br>
        <input type="password" name="password" required>
    </label><br><br>

    <label>Confirm Password:<br>
        <input type="password" name="confirm_password" required>
    </label><br><br>

    <button type="submit">Sign Up</button>
</form>

<p>Already registered? <a href="login.php">Login here</a></p>

</body>
</html>
