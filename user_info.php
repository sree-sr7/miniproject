<?php
session_start();
include 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['UserID'];

// Validation functions
function validateEmail($email) {
    // First use PHP's built-in filter
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // More restrictive pattern that ensures proper domain format
    $pattern = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9][a-zA-Z0-9.-]*\.[a-zA-Z]{2,}$/";
    
    // Get the domain part
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return false;
    }
    
    // Additional checks for the domain part
    $domain = $parts[1];
    
    // Check if domain ends with valid TLD format
    if (!preg_match('/\.[a-zA-Z]{2,}$/', $domain)) {
        return false;
    }
    
    // Check for invalid domain patterns like "gmail.com63"
    if (preg_match('/\.[a-zA-Z]+[0-9]+$/', $domain)) {
        return false;
    }
    
    return preg_match($pattern, $email);
}

function validateHeight($height) {
    return is_numeric($height) && $height >= 100 && $height <= 250;
}

function validateWeight($weight) {
    return is_numeric($weight) && $weight >= 30 && $weight <= 300;
}

function validateAge($age) {
    return is_numeric($age) && $age >= 13 && $age <= 120;
}

function validateUsername($username) {
    return strlen($username) >= 3 && strlen($username) <= 50 && preg_match('/^[a-zA-Z0-9_]+$/', $username);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Calculate daily caloric goal based on user metrics
function calculateDailyCalories($weight, $height, $age, $gender, $fitnessGoal) {
    // Mifflin-St Jeor Equation for BMR
    if ($gender == 'Male') {
        $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) + 5;
    } else {
        $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) - 161;
    }
    
    // Activity factor (assuming moderate activity)
    $maintenance = $bmr * 1.375;
    
    // Adjust based on fitness goal
    switch ($fitnessGoal) {
        case 'loseWeight':
            return round($maintenance - 500); // 500 calorie deficit
        case 'gainMuscle':
            return round($maintenance + 300); // 300 calorie surplus
        default:
            return round($maintenance); // maintain weight
    }
}

// Fetch user information
$sql = "SELECT * FROM User WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    echo "User not found.";
    exit();
}

$user = $result->fetch_assoc();
$errors = [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $height = sanitizeInput($_POST['height']);
    $weight = sanitizeInput($_POST['weight']);
    $age = sanitizeInput($_POST['age']);
    $gender = sanitizeInput($_POST['gender']);
    $fitnessGoal = sanitizeInput($_POST['fitnessGoal']);

    // Validate inputs
    if (!validateUsername($username)) {
        $errors[] = "Username must be 3-50 characters long and contain only letters, numbers, and underscores.";
    }

    if (!validateEmail($email)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (!validateHeight($height)) {
        $errors[] = "Height must be between 100cm and 250cm.";
    }

    if (!validateWeight($weight)) {
        $errors[] = "Weight must be between 30kg and 300kg.";
    }

    if (!validateAge($age)) {
        $errors[] = "Age must be between 13 and 120 years.";
    }

    if (!in_array($gender, ['Male', 'Female', 'Other'])) {
        $errors[] = "Please select a valid gender.";
    }

    if (!in_array($fitnessGoal, ['loseWeight', 'gainMuscle', 'maintainHealth'])) {
        $errors[] = "Please select a valid fitness goal.";
    }

    // If no validation errors, proceed with update
    if (empty($errors)) {
        // Calculate new daily caloric goal
        $daily_caloric_goal = calculateDailyCalories($weight, $height, $age, $gender, $fitnessGoal);
        
        // Calculate BMI
        $bmi = round($weight / (($height/100) * ($height/100)), 2);

        // Update user information
        $update_sql = "UPDATE User SET 
                      UserName = ?, 
                      Email = ?, 
                      Height = ?, 
                      Weight = ?, 
                      Age = ?, 
                      Gender = ?, 
                      Fitness_goal = ?, 
                      Daily_caloric_goal = ?,
                      BMI = ?
                      WHERE UserID = ?";
                      
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param(
            "ssddsssddi",
            $username,
            $email,
            $height,
            $weight,
            $age,
            $gender,
            $fitnessGoal,
            $daily_caloric_goal,
            $bmi,
            $userID
        );
        
        if ($update_stmt->execute()) {
            $success = "Information updated successfully.";
            // Refresh user data
            $user['UserName'] = $username;
            $user['Email'] = $email;
            $user['Height'] = $height;
            $user['Weight'] = $weight;
            $user['Age'] = $age;
            $user['Gender'] = $gender;
            $user['Fitness_goal'] = $fitnessGoal;
            $user['Daily_caloric_goal'] = $daily_caloric_goal;
            $user['BMI'] = $bmi;
        } else {
            $errors[] = "Error updating information: " . $conn->error;
        }
    }
}

// Ensure Fitness_goal is set
if (!isset($user['Fitness_goal'])) {
    $user['Fitness_goal'] = 'maintainHealth';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Information - FitTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/user_info.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">FitTrack</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="progress.php">Progress</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="workout.php">Workout</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="nutrition.php">Nutrition</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="exercise.php">Exercise</a>
                    </li>
                </ul>
                <div class="form-check form-switch mode-switch">
                    <input class="form-check-input" type="checkbox" id="modeSwitch">
                    <label class="form-check-label" for="modeSwitch">Dark Mode</label>
                </div>
            </div>
        </div>
    </nav>

    <section class="hero text-center">
        <div class="container">
            <h1>User Information</h1>
            <p>Update your profile and personal details</p>
        </div>
    </section>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Profile Information</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="" id="userInfoForm" novalidate>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username:</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($user['UserName']); ?>" required
                               pattern="[a-zA-Z0-9_]{3,50}">
                        <div class="invalid-feedback">
                            Username must be 3-50 characters and contain only letters, numbers, and underscores.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                        <div class="invalid-feedback">
                            Please enter a valid email address.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="height" class="form-label">Height (cm):</label>
                        <input type="number" class="form-control" id="height" name="height" 
                               value="<?php echo htmlspecialchars($user['Height']); ?>" 
                               min="100" max="250" required>
                        <div class="invalid-feedback">
                            Height must be between 100cm and 250cm.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="weight" class="form-label">Weight (kg):</label>
                        <input type="number" class="form-control" id="weight" name="weight" 
                               value="<?php echo htmlspecialchars($user['Weight']); ?>" 
                               min="30" max="300" required>
                        <div class="invalid-feedback">
                            Weight must be between 30kg and 300kg.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="age" class="form-label">Age:</label>
                        <input type="number" class="form-control" id="age" name="age" 
                               value="<?php echo htmlspecialchars($user['Age']); ?>" 
                               min="13" max="120" required>
                        <div class="invalid-feedback">
                            Age must be between 13 and 120 years.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender:</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="Male" <?php echo ($user['Gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($user['Gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($user['Gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <div class="invalid-feedback">
                            Please select a gender.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="fitnessGoal" class="form-label">Fitness Goal:</label>
                        <select class="form-select" id="fitnessGoal" name="fitnessGoal" required>
                            <option value="loseWeight" <?php echo ($user['Fitness_goal'] == 'loseWeight') ? 'selected' : ''; ?>>Lose Weight</option>
                            <option value="gainMuscle" <?php echo ($user['Fitness_goal'] == 'gainMuscle') ? 'selected' : ''; ?>>Gain Muscle</option>
                            <option value="maintainHealth" <?php echo ($user['Fitness_goal'] == 'maintainHealth') ? 'selected' : ''; ?>>Maintain Health</option>
                        </select>
                        <div class="invalid-feedback">
                            Please select a fitness goal.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('userInfoForm');
        
        // Enable Bootstrap form validation styles
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });

        // Real-time validation for username
        const username = document.getElementById('username');
        username.addEventListener('input', function () {
            const isValid = /^[a-zA-Z0-9_]{3,50}$/.test(this.value);
            this.setCustomValidity(isValid ? '' : 'Username must be 3-50 characters and contain only letters, numbers, and underscores.');
        });

        // Dark mode toggle
        const modeSwitch = document.getElementById('modeSwitch');
        modeSwitch.addEventListener('change', function() {
            document.body.classList.toggle('dark-mode', this.checked);
            localStorage.setItem('darkMode', this.checked);
        });

        // Load dark mode preference
        const savedDarkMode = localStorage.getItem('darkMode') === 'true';
        modeSwitch.checked = savedDarkMode;
        document.body.classList.toggle('dark-mode', savedDarkMode);
    });
    </script>
</body>
</html>