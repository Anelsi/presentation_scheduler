<?php

session_start();

require_once "../config/db.php";

$email = trim($_POST["email"]);
$password = trim($_POST["password"]);

$sql = "SELECT * FROM users WHERE email = :email";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":email" => $email
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {

    die("User not found");

}

if (!password_verify($password, $user["password"])) {

    die("Invalid password");

}

$_SESSION["user_id"] = $user["id"];
$_SESSION["user_name"] = $user["name"];
$_SESSION["user_role"] = $user["role"];

header("Location: ../pages/dashboard.php");
exit;