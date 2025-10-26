<?php
// Include the database connection file. This is the most likely source of the 500 error.
include "db.php";
session_start();

// --- CRITICAL CHECK: Ensure PDO is set up correctly ---
// If the $pdo object is not set (meaning db.php failed), show a maintenance error.
if (!isset($pdo) || !($pdo instanceof PDO)) {
    // Log the error for the owner to check server logs
    error_log("FATAL ERROR: Database connection (\$pdo object) failed to initialize in db.php.");
    // Display a user-friendly error immediately to prevent the 500 error page
    die('<h1>Maintenance Mode</h1><p>We are currently experiencing technical difficulties. Please check your database configuration in <strong>db.php</strong>.</p>');
}
// --------------------------------------------------------

// Redirect logged-in users immediately to the main dashboard
if(isset($_SESSION['business_id'])){
    // Using dashboard.php as the correct entry point
    header("Location: dashboard.php"); 
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Fetch the business record using the provided email
    try {
        $stmt = $pdo->prepare("SELECT * FROM businesses WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 2. Verify the password (assuming stored passwords are plain text for simplicity)
            // NOTE: In a real app, use password_verify($password, $user['password'])
            if ($password === $user['password']) {
                
                // Success: Set session variables
                $_SESSION['business_id'] = $user['id'];
                $_SESSION['business_name'] = $user['business_name'];
                
                // 3. Redirect to the main dashboard entry point
                header("Location: dashboard.php"); 
                exit;

            } else {
                // Password incorrect
                $error = "Invalid password.";
            }
        } else {
            // User not found
            $error = "No account found with that email address.";
        }

    } catch (PDOException $e) {
        // Log database error instead of showing it to the user (prevents 500 error on screen)
        error_log("Login PDO Error: " . $e->getMessage());
        $error = "An internal error occurred during login. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SmartBookr Login</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f4f7f6;
        }
        .login-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        h1 {
            color: #0077cc;
            margin-bottom: 25px;
            font-size: 1.8rem;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            text-align: left;
            font-weight: 600;
            color: #333;
        }
        input[type="email"], 
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 1rem;
        }
        button {
            background-color: #0077cc;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 700;
            transition: background-color 0.3s ease;
            margin-top: 15px;
        }
        button:hover {
            background-color: #005fa3;
        }
        .error {
            color: #e74c3c;
            margin-top: 10px;
            font-weight: 500;
        }
        .signup-link {
            margin-top: 20px;
            font-size: 0.9rem;
            color: #555;
        }
        .signup-link a {
            color: #0077cc;
            text-decoration: none;
            font-weight: 600;
        }
        .signup-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>SmartBookr Owner Login</h1>
        
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Login</button>
        </form>

        <div class="signup-link">
            Don't have an account? <a href="signup.php">Sign up here</a>.
        </div>
    </div>
</body>
</html>
