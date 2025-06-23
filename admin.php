<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'includes/db_connect.php';

// Check if the admin is logged in
if (!isset($_SESSION['username']) || strpos($_SESSION['username'], 'admin_') !== 0) {
    header("Location: login.php");
    exit();
}

// Fetch total number of users
$result = mysqli_query($conn, "SELECT COUNT(*) as total_users FROM user");
if ($result === false) {
    die("Error executing query: " . mysqli_error($conn));
}
$row = mysqli_fetch_assoc($result);
$total_users = $row['total_users'];

$date_range_query = "SELECT 
    MIN(created_at) as earliest_date,
    MAX(created_at) as latest_date
FROM user";

$date_result = mysqli_query($conn, $date_range_query);
if ($date_result === false) {
    die("Error executing query: " . mysqli_error($conn));
}

$date_row = mysqli_fetch_assoc($date_result);
$earliest_date = new DateTime($date_row['earliest_date']);
$latest_date = new DateTime($date_row['latest_date']);

if (!$earliest_date) {
    $earliest_date = new DateTime();
    $latest_date = new DateTime();
}

$monthly_data_query = "
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count 
    FROM user 
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC";

$monthly_result = mysqli_query($conn, $monthly_data_query);
if ($monthly_result === false) {
    die("Error executing query: " . mysqli_error($conn));
}

// Initialize arrays for labels and data
$labels = [];
$registration_data = [];

// Create a DateTime object for iteration
$current = clone $earliest_date;
$current->modify('first day of this month'); // Normalize to first day of month

// Generate all month labels and initialize data
while ($current <= $latest_date) {
    $month_key = $current->format('Y-m');
    $labels[] = $current->format('M Y'); // Format as "Jan 2024"
    $registration_data[$month_key] = 0;
    $current->modify('+1 month');
}

// Fill in the actual registration counts
while ($row = mysqli_fetch_assoc($monthly_result)) {
    if (isset($registration_data[$row['month']])) {
        $registration_data[$row['month']] = (int)$row['count'];
    }
}

// Convert associative array to numeric array for the chart
$final_data = array_values($registration_data);

// Pass the data to JavaScript
echo "<script>
    var registrationLabels = " . json_encode($labels) . ";
    var registrationData = " . json_encode($final_data) . ";
</script>";

// Fetch users registered in the last 30 days
$result = mysqli_query($conn, "SELECT COUNT(*) as recent_users FROM user WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
if ($result === false) {
    die("Error executing query: " . mysqli_error($conn));
}
$row = mysqli_fetch_assoc($result);
$recent_users = $row['recent_users'];

// Fetch latest registered users
$result = mysqli_query($conn, "SELECT * FROM user ORDER BY created_at DESC LIMIT 5");
if ($result === false) {
    die("Error executing query: " . mysqli_error($conn));
}
$latest_users = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Update nutrition data with image URL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_nutrition'])) {
    $nutritionId = mysqli_real_escape_string($conn, $_POST['nutrition_id']);
    $foodItem = mysqli_real_escape_string($conn, $_POST['food_item']);
    $calories = mysqli_real_escape_string($conn, $_POST['calories']);
    $recommendation = mysqli_real_escape_string($conn, $_POST['recommendation']);
    $imageUrl = mysqli_real_escape_string($conn, $_POST['image_url']);
    $description = mysqli_real_escape_string($conn,$_POST['description']);

    $query = "UPDATE nutrition 
              SET FoodItem = '$foodItem',
                  Calories = '$calories',
                  Recommendation = '$recommendation',
                  Image = '$imageUrl',
                  FoodDesc = '$description',
                  Date = CURDATE()
              WHERE NutritionID = '$nutritionId'";

    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Nutrition data updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating nutrition data: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }

    header("Location: admin.php");
    exit();
}

// Add new nutrition data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_nutrition'])) {
    $foodItem = mysqli_real_escape_string($conn, $_POST['new_food_item']);
    $calories = mysqli_real_escape_string($conn, $_POST['new_calories']);
    $recommendation = mysqli_real_escape_string($conn, $_POST['new_recommendation']);
    $imageUrl = mysqli_real_escape_string($conn, $_POST['new_image_url']);
    $description = mysqli_real_escape_string($conn, $_POST['new_description']);

    $query = "INSERT INTO nutrition (FoodItem, Calories, Recommendation, Image, FoodDesc, Date) 
              VALUES ('$foodItem', '$calories', '$recommendation', '$imageUrl','$description', CURDATE())";

    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "New nutrition data added successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error adding nutrition data: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }

    header("Location: admin.php");
    exit();
}

// Add new exercise with video URL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_exercise'])) {
    $exerciseName = mysqli_real_escape_string($conn, $_POST['new_exercise_name']);
    $muscleGroupId = mysqli_real_escape_string($conn, $_POST['new_muscle_group_id']);
    $exerciseDesc = mysqli_real_escape_string($conn, $_POST['new_exercise_desc']);
    $muscleWorked = mysqli_real_escape_string($conn, $_POST['new_muscle_worked']);
    $videoUrl = mysqli_real_escape_string($conn, $_POST['new_video_url']);

    $query = "INSERT INTO exercise (ExerciseName, MuscleGroupID, ExerciseDesc, Video_url, muscle_worked) 
              VALUES ('$exerciseName', '$muscleGroupId', '$exerciseDesc', '$videoUrl', '$muscleWorked')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "New exercise added successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error adding new exercise: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }

    header("Location: admin.php");
    exit();
}

// Delete nutrition data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_nutrition'])) {
    $nutritionId = mysqli_real_escape_string($conn, $_POST['nutrition_id']);
    
    $query = "DELETE FROM nutrition WHERE NutritionID = '$nutritionId'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Nutrition data deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting nutrition data: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
    
    header("Location: admin.php");
    exit();
}

// Delete exercise data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_exercise'])) {
    $exerciseId = mysqli_real_escape_string($conn, $_POST['exercise_id']);
    
    $query = "DELETE FROM exercise WHERE ExerciseID = '$exerciseId'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Exercise deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting exercise: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
    
    header("Location: admin.php");
    exit();
}

// Update exercise data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_exercise'])) {
    $exerciseId = mysqli_real_escape_string($conn, $_POST['exercise_id']);
    $exerciseName = mysqli_real_escape_string($conn, $_POST['exercise_name']);
    $muscleGroupId = mysqli_real_escape_string($conn, $_POST['muscle_group_id']);
    $exerciseDesc = mysqli_real_escape_string($conn, $_POST['exercise_desc']);
    $videoUrl = mysqli_real_escape_string($conn, $_POST['video_url']);
    $muscleWorked = mysqli_real_escape_string($conn, $_POST['muscle_worked']);

    $query = "UPDATE exercise 
              SET ExerciseName = '$exerciseName',
                  MuscleGroupID = '$muscleGroupId',
                  ExerciseDesc = '$exerciseDesc',
                  Video_url = '$videoUrl',
                  muscle_worked = '$muscleWorked'
              WHERE ExerciseID = '$exerciseId'";

    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Exercise updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating exercise: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }

    header("Location: admin.php");
    exit();
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="light-mode">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">FitTrack Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#users">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="nutrition.php">Nutrition</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="exercise.php">Exercise</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
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
            <h1>Welcome to FitTrack Admin Dashboard</h1>
            <p>Manage users, update nutrition and exercise data, and monitor system performance.</p>
        </div>
    </section>

    <div class="container main-content">
        <div class="row g-2">
            <div class="col-md-6 col-lg-3">
                <div class="card" id="users">
                    <div class="card-header">User Statistics</div>
                    <div class="card-body">
                        <h5>Total Users: <?php echo $total_users; ?></h5>
                        <h5>New Users (30 days): <?php echo $recent_users; ?></h5>
                        <div class="chart-container">
                            <canvas id="userChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-header">Latest Registrations</div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($latest_users as $user): ?>
                                <li class="list-group-item"><?php echo htmlspecialchars($user['UserName']); ?> - <?php echo $user['created_at']; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card" id="nutrition">
                    <div class="card-header">Update Nutrition Data</div>
                    <div class="card-body">
                        <form method="POST" id="nutritionForm">
                            <div class="mb-3">
                                <label for="nutrition_id" class="form-label">Nutrition ID</label>
                                <input type="number" class="form-control" id="nutrition_id" name="nutrition_id" required>
                            </div>
                            <div class="mb-3">
                                <label for="food_item" class="form-label">Food Item</label>
                                <input type="text" class="form-control" id="food_item" name="food_item" required>
                            </div>
                            <div class="mb-3">
                                <label for="calories" class="form-label">Calories</label>
                                <input type="number" class="form-control" id="calories" name="calories" required>
                            </div>
                            <div class="mb-3">
                                <label for="recommendation" class="form-label">Recommendation</label>
                                <textarea class="form-control" id="recommendation" name="recommendation"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="image_url" class="form-label">Image URL</label>
                                <input type="url" class="form-control" id="image_url" name="image_url" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" required></textarea>
                            </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="update_nutrition" class="btn btn-primary">Update Nutrition</button>
                            <button type="submit" name="delete_nutrition" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this nutrition data?')">Delete Nutrition</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card" id="exercise">
                    <div class="card-header">Update Exercise Data</div>
                    <div class="card-body">
                        <form method="POST" id="exerciseForm">
                            <div class="mb-3">
                                <label for="exercise_id" class="form-label">Exercise ID</label>
                                <input type="number" class="form-control" id="exercise_id" name="exercise_id" required>
                            </div>
                            <div class="mb-3">
                                <label for="exercise_name" class="form-label">Exercise Name</label>
                                <input type="text" class="form-control" id="exercise_name" name="exercise_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="muscle_group_id" class="form-label">Muscle Group ID</label>
                                <input type="number" class="form-control" id="muscle_group_id" name="muscle_group_id" required>
                            </div>
                            <div class="mb-3">
                                <label for="exercise_desc" class="form-label">Exercise Description</label>
                                <textarea class="form-control" id="exercise_desc" name="exercise_desc"></textarea>
                            </div>
                            <!-- Add this inside the exercise update form, before the button group -->
                            <div class="mb-3">
                                <label for="muscle_worked" class="form-label">Muscle Worked</label>
                                <input type="text" class="form-control" id="muscle_worked" name="muscle_worked" required>
                            </div>
                            <div class="mb-3">
                                <label for="video_url" class="form-label">Video URL</label>
                                <input type="url" class="form-control" id="video_url" name="video_url" required>
                            </div>
                            <!-- Replace the existing button in the exercise update form with these buttons -->
                            <div class="d-flex gap-2">
                                <button type="submit" name="update_exercise" class="btn btn-primary">Update Exercise</button>
                                <button type="submit" name="delete_exercise" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this exercise?')">Delete Exercise</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card" id="add-nutrition">
                    <div class="card-header">Add New Nutrition Data</div>
                    <div class="card-body">
                        <form method="POST" id="addNutritionForm">
                            
                            <div class="mb-3">
                                <label for="new_food_item" class="form-label">Food Item</label>
                                <input type="text" class="form-control" id="new_food_item" name="new_food_item" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_calories" class="form-label">Calories</label>
                                <input type="number" class="form-control" id="new_calories" name="new_calories" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_recommendation" class="form-label">Recommendation</label>
                                <textarea class="form-control" id="new_recommendation" name="new_recommendation"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="new_image_url" class="form-label">Image URL</label>
                                <input type="url" class="form-control" id="new_image_url" name="new_image_url" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_description" class="form-label">Description</label>
                                <textarea class="form-control" id="new_description" name="new_description" required></textarea>
                            </div>
                            <button type="submit" name="add_nutrition" class="btn btn-success">Add Nutrition</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card" id="add-exercise">
                    <div class="card-header">Add New Exercise</div>
                    <div class="card-body">
                        <form method="POST" id="addExerciseForm">
                            <div class="mb-3">
                                <label for="new_exercise_name" class="form-label">Exercise Name</label>
                                <input type="text" class="form-control" id="new_exercise_name" name="new_exercise_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_muscle_group_id" class="form-label">Muscle Group ID</label>
                                <input type="number" class="form-control" id="new_muscle_group_id" name="new_muscle_group_id" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_exercise_desc" class="form-label">Exercise Description</label>
                                <textarea class="form-control" id="new_exercise_desc" name="new_exercise_desc"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="new_muscle_worked" class="form-label">Muscle Worked</label>
                                <input type="text" class="form-control" id="new_muscle_worked" name="new_muscle_worked">
                            </div>
                            <div class="mb-3">
                                <label for="new_video_url" class="form-label">Video URL</label>
                                <input type="url" class="form-control" id="new_video_url" name="new_video_url" required>
                            </div>
                            <button type="submit" name="add_exercise" class="btn btn-success">Add Exercise</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/admin.js"></script>
    <script>
        <?php
        if (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
            echo "document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '" . $_SESSION['message_type'] . "',
                    title: '" . ($_SESSION['message_type'] == 'success' ? 'Success!' : 'Oops...') . "',
                    text: '" . addslashes($_SESSION['message']) . "',
                    confirmButtonColor: '" . ($_SESSION['message_type'] == 'success' ? '#4CAF50' : '#d33') . "'
                });
            });";
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>
    </script>
    <script>
    <?php
    if (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
        echo "document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '" . $_SESSION['message_type'] . "',
                title: '" . ($_SESSION['message_type'] == 'success' ? 'Success!' : 'Oops...') . "',
                text: '" . addslashes($_SESSION['message']) . "',
                confirmButtonColor: '" . ($_SESSION['message_type'] == 'success' ? '#4CAF50' : '#d33') . "'
            });
        });";
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    ?>

    // Add the new chart initialization
    document.addEventListener('DOMContentLoaded', function() {
        const ctxUser = document.getElementById('userChart').getContext('2d');
        charts.userChart = new Chart(ctxUser, {
            type: 'bar',
            data: {
                labels: registrationLabels,
                datasets: [{
                    label: 'New Users',
                    data: registrationData,
                    backgroundColor: '#4CAF50',
                    borderColor: '#4CAF50',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            color: '#333333',
                            font: {
                                size: 10
                            },
                            stepSize: 1 // Force whole number steps
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            color: '#333333',
                            font: {
                                size: 10
                            },
                            maxRotation: 45, // Rotate labels for better readability
                            minRotation: 45
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
</script>
</body>
</html>