<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {

    header("Location: ../pages/login.php");
    exit;

}

$userId = $_SESSION["user_id"];

$presentationId = $_GET["id"];

$sql = "
DELETE FROM personal_schedule
WHERE user_id = :user_id
AND presentation_id = :presentation_id
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":user_id" => $userId,
    ":presentation_id" => $presentationId
]);

$referer = $_SERVER["HTTP_REFERER"] ?? "../pages/my_schedule.php";
header("Location: " . $referer);
exit;
