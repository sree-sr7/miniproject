<?php
include 'includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['UserID'];

// Function to handle database errors
function handle_db_error($conn, $query) {
    echo "MySQL Error: " . $conn->error . "<br>";
    echo "Query: " . $query . "<br>";
    die("Database error occurred. Please try again later.");
}

$query = "SELECT DISTINCT p.Date, p.Sets, p.Reps, p.Weight, 
                 TIME_FORMAT(p.TimeTaken, '%H:%i:%s') as TimeTaken, 
                 w.WorkoutName, p.calories_consumed
          FROM progress p 
          JOIN workout w ON p.WorkoutID = w.WorkoutID 
          WHERE p.UserID = ? 
            AND p.WorkoutID IS NOT NULL 
            AND p.Sets IS NOT NULL 
            AND p.Reps IS NOT NULL
          ORDER BY p.Date DESC 
          LIMIT 10";
$stmt = $conn->prepare($query);
if (!$stmt) {
    handle_db_error($conn, $query);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$workout_history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$query = "SELECT Weight FROM user WHERE UserID = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    handle_db_error($conn, $query);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$current_weight = $result->fetch_assoc()['Weight'] ?? 0;
$stmt->close();

// Fetch user's muscle group focus levels
$query = "SELECT MuscleGroupID, AVG(FocusLevel) as AvgFocus FROM progress WHERE UserID = ? GROUP BY MuscleGroupID";
$stmt = $conn->prepare($query);
if (!$stmt) {
    handle_db_error($conn, $query);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$muscle_focus = [];
while ($row = $result->fetch_assoc()) {
    $muscle_focus[$row['MuscleGroupID']] = $row['AvgFocus'];
}
$stmt->close();

// Handle progress update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_progress'])) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        $new_weight = floatval($_POST['new_weight']);
        $workout_id = intval($_POST['workout_id']);
        $sets = intval($_POST['sets']);
        $reps = intval($_POST['reps']);
        $weight = floatval($_POST['weight']);
        $time_taken = $_POST['time_taken'];
        
        if (!preg_match("/^(?:2[0-3]|[01]?[0-9]):[0-5][0-9]:[0-5][0-9]$/", $time_taken)) {
            throw new Exception("Invalid time format. Please use HH:MM:SS");
        }
        
        $time_taken = date('H:i:s', strtotime($time_taken));
        $focus_level = intval($_POST['focus_level']);
        $muscle_groups = isset($_POST['muscle_groups']) ? $_POST['muscle_groups'] : [];
        $calories_consumed = !empty($_POST['calories_consumed']) ? intval($_POST['calories_consumed']) : null;

        // Update user's weight in the user table
        $query = "UPDATE user SET Weight = ? WHERE UserID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        $stmt->bind_param("di", $new_weight, $user_id);
        $stmt->execute();
        $stmt->close();

        // Insert progress record for each selected muscle group
        $query = "INSERT INTO progress (UserID, Date, WorkoutID, Sets, Reps, Weight, TimeTaken, 
                                      FocusLevel, MuscleGroupID, calories_consumed) 
                  VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception($conn->error);
        }

        foreach ($muscle_groups as $muscle_group_id) {
            $stmt->bind_param("iiiidsiis", 
                $user_id, $workout_id, $sets, $reps, $weight, 
                $time_taken, $focus_level, $muscle_group_id, $calories_consumed
            );
            $stmt->execute();
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();
        
        // Refresh page to show updated data
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}

// Fetch workouts for dropdown
$query = "SELECT WorkoutID, WorkoutName FROM workout WHERE UserID = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    handle_db_error($conn, $query);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$workouts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff" id="theme-color">
    <title>Progress Tracker - FitTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/progress.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light" id="navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php">FitTrack</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="workout.php">Workout Builder</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="exercise.php">Exercise</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="nutrition.php">Nutrition</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user_info.php">User Info</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Log out</a>
                    </li>
                </ul>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="darkModeToggle">
                    <label class="form-check-label" for="darkModeToggle">Dark Mode</label>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Progress Tracker</h1>
        <div class="row">
            <div class="col-md-6">
                <div class="card progress-card">
                    <div class="card-body">
                        <h5 class="card-title">Enter Progress</h5>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="new_weight" class="form-label">Current Weight (kg)</label>
                                <input type="number" step="0.1" class="form-control" id="new_weight" name="new_weight" value="<?php echo htmlspecialchars($current_weight); ?>" required>
                              </div>
                            <div class="mb-3">
                                <label for="workout_id" class="form-label">Workout</label>
                                <select class="form-control" id="workout_id" name="workout_id" required>
                                    <?php foreach ($workouts as $workout): ?>
                                        <option value="<?php echo $workout['WorkoutID']; ?>"><?php echo htmlspecialchars($workout['WorkoutName']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="sets" class="form-label">Sets</label>
                                <input type="number" class="form-control" id="sets" name="sets" required>
                            </div>
                            <div class="mb-3">
                                <label for="reps" class="form-label">Reps</label>
                                <input type="number" class="form-control" id="reps" name="reps" required>
                            </div>
                            <div class="mb-3">
                                <label for="weight" class="form-label">Weight (kg)</label>
                                <input type="number" step="0.1" class="form-control" id="weight" name="weight" required>
                            </div>
                            <div class="mb-3">
                                <label for="time_taken" class="form-label">Time Taken (HH:MM:SS)</label>
                                <input type="text" class="form-control" id="time_taken" name="time_taken" pattern="^(?:2[0-3]|[01]?[0-9]):[0-5][0-9]:[0-5][0-9]$" placeholder="HH:MM:SS" required>
                            </div>
                            <div class="mb-3">
                                <label for="focus_level" class="form-label">Focus Level (1-10)</label>
                                <input type="number" min="1" max="10" class="form-control" id="focus_level" name="focus_level" required>
                            </div>
                            <div class="mb-3">
                                <label for="calories_consumed" class="form-label">Calories Consumed</label>
                                <input type="number" class="form-control" id="calories_consumed" name="calories_consumed" (optional)>
                            </div>
                            <div class="mb-3">
    <label for="muscle_groups" class="form-label">Muscle Groups</label>
    <select class="form-select" id="muscle_groups" name="muscle_groups[]" multiple required>
        <option value="1">Chest</option>
        <option value="2">Back</option>
        <option value="3">Traps</option>
        <option value="4">Shoulders</option>
        <option value="5">Triceps</option>
        <option value="6">Biceps</option>
        <option value="7">Forearms</option>
        <option value="8">Legs</option>
        <option value="9">Calves</option>
        <option value="10">Glutes</option>
        <option value="11">Core</option>
    </select>
    <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple muscle groups</div>
</div>
                            <button type="submit" name="update_progress" class="btn btn-primary">Update Progress</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card progress-card">
                    <div class="card-body">
                        <h5 class="card-title">Muscle Group Focus</h5>
                        <ul class="list-group">
                            <?php
                            $muscle_groups = [
                              1 => 'Chest',
                              2 => 'Back',
                              3 => 'Traps',
                              4 => 'Shoulders',
                              5 => 'Triceps',
                              6 => 'Biceps',
                              7 => 'Forearms',
                              8 => 'Legs',
                              9 => 'Calves',
                              10 => 'Glutes',
                              11 => 'Core'
                          ];
                            foreach ($muscle_groups as $id => $name) {
                                $focus = isset($muscle_focus[$id]) ? round($muscle_focus[$id], 2) : 0;
                                echo "<li class='list-group-item d-flex justify-content-between align-items-center'>";
                                echo $name;
                                echo "<span class='badge bg-primary rounded-pill'>$focus</span>";
                                echo "</li>";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Workout history section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card progress-card">
                    <div class="card-body">
                        <h5 class="card-title">Workout History</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Workout</th>
                                        <th>Sets</th>
                                        <th>Reps</th>
                                        <th>Weight (kg)</th>
                                        <th>Time Taken (min)</th>
                                        <th>Calories Consumed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($workout_history as $workout): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d', strtotime($workout['Date'])); ?></td>
                                        <td><?php echo htmlspecialchars($workout['WorkoutName']); ?></td>
                                        <td><?php echo $workout['Sets']; ?></td>
                                        <td><?php echo $workout['Reps']; ?></td>
                                        <td><?php echo $workout['Weight']; ?></td>
                                        <td><?php echo $workout['TimeTaken']; ?></td>
                                        <td><?php echo $workout['calories_consumed'] !== null ? $workout['calories_consumed'] : 'N/A'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const muscleGroupsSelect = document.getElementById('muscle_groups');
    
    // Validate that at least one muscle group is selected
    document.querySelector('form').addEventListener('submit', function(e) {
        if (muscleGroupsSelect.selectedOptions.length === 0) {
            e.preventDefault();
            alert('Please select at least one muscle group');
        }
    });
});
</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/progress.js"></script>
</body>
</html>