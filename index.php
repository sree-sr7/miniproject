<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

require_once 'includes/db_connect.php';
require_once 'functions.php';
// In index.php where you're getting the weight data
/*require_once 'includes/get_weight_changes.php';
$weight_data = get_weight_history($conn, $user_id);*/

$user_id = $_SESSION['UserID'];
$user_data = get_user_data($conn, $user_id);
$progress_data = get_progress_data($conn, $user_id);
$weight_data = get_weight_data($conn, $user_id);
$nutrition_recommendations = get_nutrition_recommendations($conn, $user_id);
$calories_consumed = calculate_calories_consumed($conn, $user_id);

// Get initial muscle focus levels for the past week
$focus_query = "
    SELECT 
        mg.MuscleGroupName,
        COALESCE(ROUND(AVG(pm.FocusLevel)), 0) as focus_level
    FROM 
        muscle_group mg
        LEFT JOIN progress_muscle pm ON mg.MuscleGroupID = pm.MuscleGroupID
        LEFT JOIN progress p ON pm.ProgressID = p.ProgressID
    WHERE 
        p.UserID = ? 
        AND p.Date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 WEEK)
    GROUP BY 
        mg.MuscleGroupID,
        mg.MuscleGroupName
    ORDER BY 
        mg.MuscleGroupID";

$stmt = $conn->prepare($focus_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$focus_result = $stmt->get_result();

$muscle_focus_levels = [];
while ($row = $focus_result->fetch_assoc()) {
    $muscle_focus_levels[$row['MuscleGroupName']] = (int)$row['focus_level'];
}

// Ensure all muscle groups are represented
$all_muscles = ['Chest', 'Back', 'Traps', 'Shoulders', 'Triceps', 'Biceps', 'Forearms', 'Legs', 'Calves', 'Glutes', 'Core'];
foreach ($all_muscles as $muscle) {
    if (!isset($muscle_focus_levels[$muscle])) {
        $muscle_focus_levels[$muscle] = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack - Your Fitness Companion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/index.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
</head>
<body class="light-mode" data-user-id="<?= $user_id ?>">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">FitTrack</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="progress.php">Progress</a></li>
                    <li class="nav-item"><a class="nav-link" href="workout.php">Workout Builder</a></li>
                    <li class="nav-item"><a class="nav-link" href="nutrition.php">Nutrition</a></li>
                    <li class="nav-item"><a class="nav-link" href="exercise.php">Exercise</a></li>
                    <li class="nav-item"><a class="nav-link" href="user_info.php">User Info</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <li class="nav-item d-flex align-items-center">
                        <div class="form-check form-switch mode-switch mb-0 d-flex align-items-center">
                            <input class="form-check-input" type="checkbox" id="modeSwitch">
                            <label class="form-check-label ms-2 mb-0" for="modeSwitch">Dark Mode</label>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <section class="hero text-center">
        <div class="container">
            <h1>Welcome to FitTrack, <?= htmlspecialchars($user_data['UserName']) ?>!</h1>
            <p>Your personal fitness journey starts here. Track your progress, manage your nutrition, and achieve your goals.</p>
            <p>Current Fitness Goal: <?= isset($user_data['fitness_goal']) ? htmlspecialchars($user_data['fitness_goal']) : 'maintainHealth' ?></p>
        </div>
    </section>

    <div class="container main-content">
        <div class="row g-2">
            <!-- Progress Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card" id="progress">
                    <div class="card-header">Your Progress</div>
                    <div class="card-body">
                        <div class="chart-container"><canvas id="radarChart"></canvas></div>
                        <div class="time-selector">
                        <button data-range="1W">1W</button>
                        <button data-range="1M">1M</button>
                        <button data-range="3M">3M</button>
                        <button data-range="1Y">1Y</button>
                        <button data-range="All">All</button>
                    </div>
                        <!-- Hidden div for muscle focus data -->
                        <div class="muscle-focus-data" style="display: none;">
                            <?php foreach ($all_muscles as $muscle): ?>
                                <span data-muscle="<?= strtolower($muscle) ?>"><?= $muscle_focus_levels[$muscle] ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weight Trend Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-header">Weight Trend</div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="weightChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Stats Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card" id="user-stats">
                    <div class="card-header">Your Stats</div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Height: <?= htmlspecialchars($user_data['Height']) ?> cm</li>
                            <li class="list-group-item">Weight: <?= htmlspecialchars($user_data['Weight']) ?> kg</li>
                            <li class="list-group-item">Age: <?= htmlspecialchars($user_data['Age']) ?> years</li>
                            <li class="list-group-item">BMI: <?= htmlspecialchars($user_data['BMI']) ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Nutrition Card -->
        <div class="col-md-6 col-lg-3">
            <div class="card" id="nutrition">
                <div class="card-header">Nutrition Recommendations</div>
                <div class="card-body">
                    <?php 
                    $calories_data = calculate_calories_consumed($conn, $user_id);
                    $percentage = min(100, ($calories_data['consumed'] / $calories_data['goal']) * 100);
                    $calorie_difference = $calories_data['consumed'] - $calories_data['goal'];
                    $warning_message = '';
                    if (abs($calorie_difference) > 500) {
                        $warning_message = $calorie_difference > 500 
                            ? "Warning: You're ${calorie_difference} calories over your daily goal!"
                            : "Warning: You're " . abs($calorie_difference) . " calories under your daily goal!";
                    }
                    ?>
                    <div class="calorie-info mb-3">
                        <p class="mb-1">Daily Goal: <?= htmlspecialchars($calories_data['goal']) ?> calories</p>
                        <div class="progress">
                            <div class="progress-bar <?= $percentage > 100 ? 'bg-danger' : ($percentage > 80 ? 'bg-warning' : 'bg-success') ?>"
                                role="progressbar"
                                style="width: <?= $percentage ?>%"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="<?= htmlspecialchars($warning_message) ?>"
                                aria-valuenow="<?= $percentage ?>"
                                aria-valuemin="0"
                                aria-valuemax="100">
                                <?= round($percentage) ?>%
                            </div>
                        </div>
                        <p class="mt-1 small">
                            Consumed: <?= htmlspecialchars($calories_data['consumed']) ?> cal
                            <span class="float-end">Remaining: <?= htmlspecialchars($calories_data['remaining']) ?> cal</span>
                        </p>
                    </div>
                    
                    <h6 class="mb-2">Recommended Foods (<?= ucfirst($calories_data['fitness_goal']) ?>):</h6>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($nutrition_recommendations as $recommendation): ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?= htmlspecialchars($recommendation['foodItem']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($recommendation['calories']) ?> cal</small>
                                    </div>
                                    <?php if ($recommendation['image']): ?>
                                        <img src="<?= htmlspecialchars($recommendation['image']) ?>" 
                                            alt="<?= htmlspecialchars($recommendation['foodItem']) ?>" 
                                            class="rounded" 
                                            style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Feedback Section -->
        <div class="row mt-2">
            <div class="col-12">
                <div class="card" id="feedback">
                    <div class="card-header">Feedback</div>
                    <div class="card-body">
                        <form action="submit_feedback.php" method="post">
                            <div class="mb-3">
                                <textarea class="form-control" name="feedback" rows="2" placeholder="Share your thoughts or suggestions..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Submit Feedback</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" defer></script>
    <script>
        // Pass PHP data to JavaScript
        const progressData = <?php echo json_encode($muscle_focus_levels); ?>;
        const weightData = <?php echo json_encode($weight_data); ?>;
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover'
                });
            });
        });
    </script>
    <script src="js/index.js" defer></script>
</body>
</html>