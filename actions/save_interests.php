<?php

session_start();

require_once "../config/db.php";

$userId = $_SESSION["user_id"];

$categories = $_POST["categories"] ?? [];

$sqlDelete = "
DELETE FROM interests
WHERE user_id = :user_id
";

$stmtDelete = $pdo->prepare($sqlDelete);

$stmtDelete->execute([
    ":user_id" => $userId
]);

$sqlInsert = "
INSERT INTO interests
(user_id, category)
VALUES
(:user_id, :category)
";

$stmtInsert = $pdo->prepare($sqlInsert);

foreach ($categories as $category) {

    $stmtInsert->execute([
        ":user_id" => $userId,
        ":category" => $category
    ]);

}

header("Location: ../pages/my_schedule.php");
exit;