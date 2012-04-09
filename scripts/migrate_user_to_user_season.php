<?php

function stop($message) {
    echo $message . "\n";
    exit;
}

$seasonId = 1;

$DS = DIRECTORY_SEPARATOR;
$parameters = parse_ini_file(__DIR__.$DS.'..'.$DS.'app'.$DS.'config'.$DS.'parameters.ini');
if ($parameters['db_backend']!='mysql') stop('This script supports only MySQL.');

$user = $parameters['db_mysql_user'];
$password = $parameters['db_mysql_password'];
$database = $parameters['db_mysql_name'];

if (empty($user) || empty($password) || empty($database))
    stop('Nezadane prihlasovacie udaje k DB.');

$mysqli = @new mysqli("localhost", $user, $password, $database);
if ($mysqli->connect_errno) {
    stop("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

if (!($stmt = $mysqli->prepare("INSERT INTO `UserSeason` (`user_id`, `season_id`, `isStudent`, `participated`, `finished`, `isTeacher`)
VALUES (?, ?, ?, ?, 1, 0)"))) {
    stop("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
}

$res = $mysqli->query("SELECT id, hasVote, participated FROM User");
while ($row = $res->fetch_assoc()) {
    if (!$stmt->bind_param('iiii', $row['id'], $seasonId, $row['participated'], $row['participated'])) {
        stop("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
    }
    if (!$stmt->execute()) {
        stop("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
    }
}
?>
