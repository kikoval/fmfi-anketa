<?php

function stop($message) {
    echo $message . "\n";
    exit;
}

$seasonId = 1;

$user = "root";
$password = "root";
$database = "anketa2";
if (empty($user) || empty($password) || empty($database))
    stop('Nezadane prihlasovacie udaje k DB.');
$mysqli = @new mysqli("localhost", $user, $password, $database);
if ($mysqli->connect_errno) {
    stop("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

if (!($stmt = $mysqli->prepare("INSERT INTO `userseason` (`user_id`, `season_id`, `isStudent`, `participated`, `finished`, `isTeacher`)
VALUES (?, ?, ?, ?, 1, 0)"))) {
    stop("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
}



$res = $mysqli->query("SELECT id, hasVote, participated FROM user");
while ($row = $res->fetch_assoc()) {
    if (!$stmt->bind_param('iiii', $row['id'], $seasonId, $row['participated'], $row['participated'])) {
        stop("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
    }
    if (!$stmt->execute()) {
        stop("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
    }
}
?>
