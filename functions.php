<?php
function get_user_data($conn, $user_id) {
    $stmt = $conn->prepare("SELECT UserName, Height, Weight, Age, Gender, BMI, Daily_caloric_goal, fitness_goal 
                           FROM user 
                           WHERE UserID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    // Sanitize each field individually
    if ($data) {
        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
    }
    return $data;
}

function get_weight_data($conn, $user_id) {
    $query = "SELECT 
                DATE(p1.Date) as date,
                p1.Weight 
              FROM progress p1
              INNER JOIN (
                  SELECT MIN(ProgressID) as FirstProgressID
                  FROM progress
                  WHERE UserID = ? AND Weight IS NOT NULL
                  GROUP BY CONCAT(Date, Weight)
              ) p2 ON p1.ProgressID = p2.FirstProgressID
              ORDER BY p1.Date";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Query preparation failed: " . $conn->error);
        return ['labels' => [], 'data' => []];
    }

    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $weight_data = [
        'labels' => [],
        'data' => []
    ];
    
    while ($row = $result->fetch_assoc()) {
        $weight_data['labels'][] = date('Y-m-d', strtotime($row['date']));
        $weight_data['data'][] = $row['Weight'];
    }
    
    return $weight_data;
}

function get_progress_data($conn, $user_id, $time_range = '1M') {
    // Calculate the date limit based on time range
    $date_limit = match($time_range) {
        '1W' => 'DATE_SUB(CURRENT_DATE, INTERVAL 1 WEEK)',
        '1M' => 'DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)',
        '3M' => 'DATE_SUB(CURRENT_DATE, INTERVAL 3 MONTH)',
        '1Y' => 'DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR)',
        'All' => NULL, // No date limit for 'All'
        default => 'DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)'
    };

    // Base query to get latest focus level for each muscle group
    $query = "
        SELECT 
            mg.MuscleGroupID,
            mg.MuscleGroupName,
            COALESCE(MAX(pm.FocusLevel), 0) as focus_level
        FROM muscle_group mg
        LEFT JOIN (
            SELECT pm.MuscleGroupID, pm.FocusLevel
            FROM progress_muscle pm
            JOIN progress p ON p.ProgressID = pm.ProgressID
            WHERE p.UserID = ?
            " . ($date_limit ? " AND p.Date >= {$date_limit}" : "") . "
        ) pm ON mg.MuscleGroupID = pm.MuscleGroupID
        GROUP BY mg.MuscleGroupID, mg.MuscleGroupName
        ORDER BY mg.MuscleGroupName
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $progress = [];
    while ($row = $result->fetch_assoc()) {
        $progress[htmlspecialchars($row['MuscleGroupName'], ENT_QUOTES, 'UTF-8')] = 
            (int)$row['focus_level'];
    }
    
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'labels' => array_keys($progress),
            'data' => array_values($progress)
        ]);
        exit;
    }
    
    return $progress;
}

function get_nutrition_recommendations($conn, $user_id) {
    // Get user's fitness goal
    $goal_query = "SELECT fitness_goal FROM user WHERE UserID = ?";
    $stmt = $conn->prepare($goal_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $fitness_goal = $user_data['fitness_goal'];

    // Get appropriate food recommendations based on fitness goal
    $recommendations = [];
    
    switch($fitness_goal) {
        case 'gainMuscle':
            // Get protein-rich foods
            $food_query = "SELECT FoodItem, Calories, Recommendation, Image, FoodDesc 
                          FROM nutrition 
                          WHERE Recommendation LIKE '%Protein-rich%' 
                          ORDER BY RAND() LIMIT 2";
            $stmt = $conn->prepare($food_query);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while($row = $result->fetch_assoc()) {
                $recommendations[] = [
                    'foodItem' => $row['FoodItem'],
                    'calories' => $row['Calories'],
                    'recommendation' => $row['Recommendation'],
                    'image' => $row['Image'],
                    'description' => $row['FoodDesc']
                ];
            }
            
            // Get fiber-rich foods
            $fiber_query = "SELECT FoodItem, Calories, Recommendation, Image, FoodDesc 
                           FROM nutrition 
                           WHERE Recommendation LIKE '%Good source of fibre%' 
                           ORDER BY RAND() LIMIT 1";
            $stmt = $conn->prepare($fiber_query);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while($row = $result->fetch_assoc()) {
                $recommendations[] = [
                    'foodItem' => $row['FoodItem'],
                    'calories' => $row['Calories'],
                    'recommendation' => $row['Recommendation'],
                    'image' => $row['Image'],
                    'description' => $row['FoodDesc']
                ];
            }
            break;
            
        case 'loseWeight':
            // Get protein-rich foods for weight loss
            $food_query = "SELECT FoodItem, Calories, Recommendation, Image, FoodDesc 
                          FROM nutrition 
                          WHERE Recommendation LIKE '%Protein-rich%' 
                          ORDER BY RAND() LIMIT 3";
            $stmt = $conn->prepare($food_query);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while($row = $result->fetch_assoc()) {
                $recommendations[] = [
                    'foodItem' => $row['FoodItem'],
                    'calories' => $row['Calories'],
                    'recommendation' => $row['Recommendation'],
                    'image' => $row['Image'],
                    'description' => $row['FoodDesc']
                ];
            }
            break;
            
        default: // maintainHealth
            // Get all types of foods
            $food_query = "SELECT FoodItem, Calories, Recommendation, Image, FoodDesc 
                          FROM nutrition 
                          ORDER BY RAND() LIMIT 3";
            $stmt = $conn->prepare($food_query);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while($row = $result->fetch_assoc()) {
                $recommendations[] = [
                    'foodItem' => $row['FoodItem'],
                    'calories' => $row['Calories'],
                    'recommendation' => $row['Recommendation'],
                    'image' => $row['Image'],
                    'description' => $row['FoodDesc']
                ];
            }
            break;
    }
    
    return $recommendations;
}

function calculate_calories_consumed($conn, $user_id) {
    // Get user's daily caloric goal and fitness goal from user table
    $goal_query = "SELECT Daily_caloric_goal, fitness_goal FROM user WHERE UserID = ?";
    $stmt = $conn->prepare($goal_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $daily_goal = $user_data['Daily_caloric_goal'];
    $fitness_goal = $user_data['fitness_goal'];

    // Modified query to ignore NULL values and get the latest non-NULL entry
    $calories_query = "SELECT calories_consumed 
                      FROM progress 
                      WHERE UserID = ? 
                      AND Date = CURRENT_DATE()
                      AND calories_consumed IS NOT NULL
                      ORDER BY ProgressID DESC
                      LIMIT 1";
    $stmt = $conn->prepare($calories_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $calories_data = $result->fetch_assoc();
    
    // If no calories logged today, check the most recent non-NULL entry
    if (!$calories_data) {
        $recent_calories_query = "SELECT calories_consumed, Date 
                                FROM progress 
                                WHERE UserID = ? 
                                AND calories_consumed IS NOT NULL
                                ORDER BY Date DESC, ProgressID DESC 
                                LIMIT 1";
        $stmt = $conn->prepare($recent_calories_query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $calories_data = $result->fetch_assoc();
    }

    $consumed = isset($calories_data['calories_consumed']) ? $calories_data['calories_consumed'] : 0;

    return [
        'consumed' => $consumed,
        'goal' => $daily_goal,
        'remaining' => max(0, $daily_goal - $consumed),
        'fitness_goal' => $fitness_goal
    ];
}

function get_workout_history($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT 
            w.WorkoutName,
            p.Date,
            p.Sets,
            p.Reps,
            p.Weight,
            p.TimeTaken,
            mg.MuscleGroupName,
            e.ExerciseName
        FROM progress p
        JOIN workout w ON w.WorkoutID = p.WorkoutID
        JOIN workout_exercise we ON we.WorkoutID = w.WorkoutID
        JOIN exercise e ON e.ExerciseID = we.ExerciseID
        JOIN muscle_group mg ON mg.MuscleGroupID = e.MuscleGroupID
        WHERE p.UserID = ?
        ORDER BY p.Date DESC
        LIMIT 10
    ");
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = [
            'workoutName' => htmlspecialchars($row['WorkoutName'], ENT_QUOTES, 'UTF-8'),
            'date' => htmlspecialchars($row['Date'], ENT_QUOTES, 'UTF-8'),
            'sets' => intval($row['Sets']),
            'reps' => intval($row['Reps']),
            'weight' => floatval($row['Weight']),
            'timeTaken' => htmlspecialchars($row['TimeTaken'], ENT_QUOTES, 'UTF-8'),
            'muscleGroupName' => htmlspecialchars($row['MuscleGroupName'], ENT_QUOTES, 'UTF-8'),
            'exerciseName' => htmlspecialchars($row['ExerciseName'], ENT_QUOTES, 'UTF-8')
        ];
    }
    
    return $history;
}

// New function to handle feedback submission
function submit_feedback($conn, $user_id, $feedback_text) {
    try {
        // Validate inputs
        if (empty($user_id) || empty($feedback_text)) {
            throw new Exception("Missing required fields");
        }

        // Prepare the SQL statement
        $stmt = $conn->prepare("
            INSERT INTO feedback (UserID, Date, FeedbackText)
            VALUES (?, CURRENT_DATE, ?)
        ");

        // Bind parameters and execute
        $stmt->bind_param("is", $user_id, $feedback_text);
        $success = $stmt->execute();

        if (!$success) {
            throw new Exception("Failed to submit feedback");
        }

        return [
            'success' => true,
            'message' => 'Feedback submitted successfully'
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Helper function to safely output JSON data
function output_json($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
