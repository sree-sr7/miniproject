<?php
session_start();
include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Query to check if the user exists
    $sql = "SELECT * FROM User WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Verify the hashed password against the entered password
        if (password_verify($password, $user['Password'])) {
            $_SESSION['UserID'] = $user['UserID'];
            
            // Check if username starts with 'admin_'
            if (strpos($user['UserName'], 'admin_') === 0) {
                $_SESSION['username'] = $user['UserName']; // Store username for admin check
                header("Location: admin.php");
                exit();
            } else {
                header("Location: index.php");
                exit();
            }
        } else {
            $error_message = "Invalid email or password.";
        }
    } else {
        $error_message = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="container">
        <div class="logo"></div>
        <h2>Welcome Back</h2>
        <p>Don't have an account? <a href="signup.php">Sign up</a></p>
        
        <?php if (isset($error_message)): ?>
            <div class="error-alert"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <form id="loginForm" class="login-form" method="POST" action="login.php">
            <input type="email" id="email" name="email" placeholder="Email" required>
            <div id="emailError" class="error-message"></div>
            
            <input type="password" id="password" name="password" placeholder="Password" required>
            <div id="passwordError" class="error-message"></div>
            
            <button type="submit">Log In</button>
        </form>
        
    </div>
    <script src="js/login.js"></script>
</body>
</html>