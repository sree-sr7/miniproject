<?php
require_once 'includes/db_connect.php'; // Include database connection file

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture POST data with basic sanitization
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hashing
    $age = (int)$_POST['age']; // Cast age to integer
    $gender = $_POST['gender'];
    $height = (float)$_POST['height']; // Ensure height is treated as a float
    $weight = (float)$_POST['weight']; // Ensure weight is treated as a float
    $fitnessGoal = $_POST['fitnessGoal'];

    // If it's an admin email, prefix the username with "admin_"
    $username = $_POST['username'];
    if (strpos($email, '@admin.fas.com') !== false) {
        $username = 'admin_' . $username;
    }

    // Calculate BMI with proper decimal precision
    $bmi = round(($weight / ($height / 100) ** 2), 2);

    // Set daily caloric goal based on fitness goal
    $daily_caloric_goal = 0;
    if ($fitnessGoal == 'loseWeight') {
        $daily_caloric_goal = 1500; // Example number for losing weight
    } elseif ($fitnessGoal == 'gainMuscle') {
        $daily_caloric_goal = 2500; // Example number for gaining muscle
    } else {
        $daily_caloric_goal = 2000; // Example number for maintaining health
    }

    // Prepare SQL query to check for existing email
    $email_check_query = "SELECT * FROM user WHERE Email = ?";
    $stmt = $conn->prepare($email_check_query);
    $stmt->bind_param("s", $email); // "s" expects a string
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the email already exists
    if ($result->num_rows > 0) {
        echo "Email already registered. Please use a different email.";
    } else {
        // Prepare SQL query to insert the new user into the database
        try {
            $insert_query = "INSERT INTO user (UserName, Password, Email, Age, Gender, Height, Weight, BMI, Daily_caloric_goal, Fitness_goal) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            // Bind parameters with correct data types
            // "s" for string, "d" for double, "i" for integer
            $stmt->bind_param("sssisdddss", $username, $password, $email, $age, $gender, $height, $weight, $bmi, $daily_caloric_goal, $fitnessGoal);

            // Execute the statement
            if ($stmt->execute()) {
                echo "Sign up successful!";
            } else {
                echo "Sign up failed. Please try again.";
            }
        } catch (mysqli_sql_exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up Page</title>
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>
    <div class="container">
        <form id="signupForm" class="signup-form" action="signup.php" method="POST">
            <div class="logo"></div>
            <h2>Create Account</h2>
            <p>Already have an account? <a href="login.php">Log in</a></p>
            <input type="text" id="username" name="username" placeholder="Username" required>
            <div id="usernameError" class="error-message"></div>
            <input type="email" id="email" name="email" placeholder="Email address" required>
            <div id="emailError" class="error-message"></div>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <div id="passwordError" class="error-message"></div>
            <input type="password" id="confirmPassword" placeholder="Confirm Password" required>
            <div id="confirmPasswordError" class="error-message"></div>
            <input type="number" id="age" name="age" placeholder="Age" required min="1" max="120">
            <div id="ageError" class="error-message"></div>
            <select id="gender" name="gender" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="rathernotsay">Rather not say</option>
            </select>
            <div id="genderError" class="error-message"></div>
            <input type="number" id="height" name="height" placeholder="Height (cm)" required min="0">
            <div id="heightError" class="error-message"></div>
            <input type="number" id="weight" name="weight" placeholder="Weight (kg)" required min="0">
            <div id="weightError" class="error-message"></div>
            <select id="fitnessGoal" name="fitnessGoal" required>
                <option value="">Select Fitness Goal</option>
                <option value="loseWeight">Lose Weight</option>
                <option value="gainMuscle">Gain Muscle</option>
                <option value="maintainHealth">Maintain Health</option>
            </select>
            <div id="fitnessGoalError" class="error-message"></div>
            <button type="submit">Sign Up</button>
        </form>
    </div>
    <script src="js/signup.js"></script>
</body>
</html>