<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../pages/login.php");
    exit;
}

$columns = [
    "title",
    "description",
    "presenter",
    "category",
    "presentation_date",
    "start_time",
    "end_time",
    "room"
];

$stmt = $pdo->query("
    SELECT
        title,
        description,
        presenter,
        category,
        presentation_date,
        start_time,
        end_time,
        room
    FROM presentations
    ORDER BY presentation_date ASC, start_time ASC, title ASC
");

$filename = "presentations_export_" . date("Y-m-d_H-i-s") . ".csv";

header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
header("Pragma: no-cache");
header("Expires: 0");

$output = fopen("php://output", "w");

fputcsv($output, $columns);

while ($presentation = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $presentation["title"],
        $presentation["description"],
        $presentation["presenter"],
        $presentation["category"],
        $presentation["presentation_date"],
        $presentation["start_time"],
        $presentation["end_time"],
        $presentation["room"]
    ]);
}

fclose($output);
exit;
