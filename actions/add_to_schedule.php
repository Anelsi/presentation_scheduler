<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {

    header("Location: ../pages/login.php");
    exit;

}

$userId = $_SESSION["user_id"];

$presentationId = $_GET["id"];

$checkSql = "
SELECT *
FROM personal_schedule
WHERE user_id = :user_id
AND presentation_id = :presentation_id
";

$checkStmt = $pdo->prepare($checkSql);

$checkStmt->execute([
    ":user_id" => $userId,
    ":presentation_id" => $presentationId
]);

$existing = $checkStmt->fetch();

if (!$existing) {

    $sql = "
    INSERT INTO personal_schedule
    (
        user_id,
        presentation_id
    )
    VALUES
    (
        :user_id,
        :presentation_id
    )
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":user_id" => $userId,
        ":presentation_id" => $presentationId
    ]);

}

header("Location: ../pages/my_schedule.php");
exit;