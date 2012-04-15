<?php

function stop($message) {
    echo $message . "\n";
    exit;
}

function generateSubjectSlug($code) {
        $slug = preg_replace('@[^a-zA-Z0-9_]@', '-', $code);
        $slug = preg_replace('@-+@', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
}

setlocale(LC_CTYPE, 'sk_SK.utf-8');

$DS = DIRECTORY_SEPARATOR;
$parameters = parse_ini_file(__DIR__ . $DS . '..' . $DS . 'app' . $DS . 'config' . $DS . 'parameters.ini');
if ($parameters['db_backend'] != 'mysql') stop('This script supports only MySQL.');

$user = $parameters['db_mysql_user'];
$password = $parameters['db_mysql_password'];
$database = $parameters['db_mysql_name'];

if (empty($user) || empty($password) || empty($database)) stop('Nezadane prihlasovacie udaje k DB.');

$mysqli = @new mysqli("localhost", $user, $password, $database);
if ($mysqli->connect_errno) {
    stop("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

$mysqli->query("SET NAMES utf8");

if (!($stmt = $mysqli->prepare("UPDATE `Subject` SET `slug` = ? WHERE id = ?"))) {
    stop("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
}

$res = $mysqli->query("SELECT id, code FROM Subject");
while ($row = $res->fetch_assoc()) {
    $slug = generateSubjectSlug($row['code']);
    if (!$stmt->bind_param('si', $slug, $row['id'])) {
        stop("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
    }
    if (!$stmt->execute()) {
        stop("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
    }
}
?>
