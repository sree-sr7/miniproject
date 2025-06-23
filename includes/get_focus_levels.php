<?php
require_once 'db_connect.php';

$user_id = 21; // Replace with dynamic user ID
echo json_encode(get_focus_levels($conn, $user_id));
