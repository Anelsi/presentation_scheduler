<?php

session_start();
require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../pages/login.php");
    exit;
}

$userId = $_SESSION["user_id"];

$categories = $_POST["categories"] ?? [];

$deleteSql = "DELETE FROM interests WHERE user_id = :user_id";
$deleteStmt = $pdo->prepare($deleteSql);
$deleteStmt->execute([
    ":user_id" => $userId
]);

foreach ($categories as $category) {

    $insertSql = "
    INSERT INTO interests (user_id, category)
    VALUES (:user_id, :category)
    ";

    $insertStmt = $pdo->prepare($insertSql);

    $insertStmt->execute([
        ":user_id" => $userId,
        ":category" => $category
    ]);
}

$_SESSION["success"] = "Interests updated successfully.";

header("Location: ../pages/my_interests.php");
exit;