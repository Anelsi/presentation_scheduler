<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../pages/login.php");
    exit;
}

if (($_SESSION["user_role"] ?? "") !== "admin") {
    $_SESSION["error"] = "Only admins can import presentations.";
    header("Location: ../pages/presentations.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/create_presentation.php");
    exit;
}

$requiredColumns = [
    "title",
    "description",
    "presenter",
    "category",
    "presentation_date",
    "start_time",
    "end_time",
    "room"
];

$allowedCategories = ["Cloud", "AI", "Web", "Security"];

function redirectWithImportError($message)
{
    $_SESSION["error"] = $message;
    header("Location: ../pages/create_presentation.php");
    exit;
}

function normalizeCsvDate($value)
{
    $date = DateTime::createFromFormat("!Y-m-d", $value);

    if ($date === false || $date->format("Y-m-d") !== $value) {
        return null;
    }

    return $date->format("Y-m-d");
}

function normalizeCsvTime($value)
{
    foreach (["!H:i", "!H:i:s"] as $format) {
        $time = DateTime::createFromFormat($format, $value);

        if ($time !== false && $time->format(substr($format, 1)) === $value) {
            return $time->format("H:i:s");
        }
    }

    return null;
}

function duplicateImportKey($presentation)
{
    return strtolower(implode("|", [
        $presentation["title"],
        $presentation["presenter"],
        $presentation["presentation_date"],
        $presentation["start_time"],
        $presentation["room"]
    ]));
}

if (
    !isset($_FILES["presentation_csv"])
    || $_FILES["presentation_csv"]["error"] === UPLOAD_ERR_NO_FILE
) {
    redirectWithImportError("Please choose a CSV file to import.");
}

if ($_FILES["presentation_csv"]["error"] !== UPLOAD_ERR_OK) {
    redirectWithImportError("The CSV file could not be uploaded.");
}

$extension = strtolower(pathinfo($_FILES["presentation_csv"]["name"], PATHINFO_EXTENSION));

if ($extension !== "csv") {
    redirectWithImportError("Please upload a .csv file.");
}

$handle = fopen($_FILES["presentation_csv"]["tmp_name"], "r");

if ($handle === false) {
    redirectWithImportError("The CSV file could not be opened.");
}

$header = fgetcsv($handle);

if ($header === false) {
    fclose($handle);
    redirectWithImportError("The CSV file is empty.");
}

$header[0] = preg_replace('/^\xEF\xBB\xBF/', "", $header[0]);
$normalizedHeader = array_map(function ($column) {
    return strtolower(trim($column));
}, $header);

$columnIndexes = [];
$missingColumns = [];

foreach ($requiredColumns as $column) {
    $index = array_search($column, $normalizedHeader, true);

    if ($index === false) {
        $missingColumns[] = $column;
        continue;
    }

    $columnIndexes[$column] = $index;
}

if (!empty($missingColumns)) {
    fclose($handle);
    redirectWithImportError(
        "Import failed. Missing required CSV columns: " . implode(", ", $missingColumns) . "."
    );
}

$duplicateStmt = $pdo->prepare("
    SELECT id
    FROM presentations
    WHERE title = :title
    AND presenter = :presenter
    AND presentation_date = :presentation_date
    AND start_time = :start_time
    AND room = :room
    LIMIT 1
");

$insertStmt = $pdo->prepare("
    INSERT INTO presentations
    (
        title,
        description,
        presenter,
        category,
        presentation_date,
        start_time,
        end_time,
        room
    )
    VALUES
    (
        :title,
        :description,
        :presenter,
        :category,
        :presentation_date,
        :start_time,
        :end_time,
        :room
    )
");

$imported = 0;
$skipped = 0;
$rowNumber = 1;
$errors = [];
$seenKeys = [];

while (($row = fgetcsv($handle)) !== false) {
    $rowNumber++;

    if (count(array_filter($row, "strlen")) === 0) {
        continue;
    }

    $presentation = [];
    $missingValues = [];

    foreach ($requiredColumns as $column) {
        $value = trim($row[$columnIndexes[$column]] ?? "");
        $presentation[$column] = $value;

        if ($value === "") {
            $missingValues[] = $column;
        }
    }

    if (!empty($missingValues)) {
        $skipped++;
        $errors[] = "Row " . $rowNumber . " skipped: missing " . implode(", ", $missingValues) . ".";
        continue;
    }

    if (!in_array($presentation["category"], $allowedCategories, true)) {
        $skipped++;
        $errors[] = "Row " . $rowNumber . " skipped: invalid category.";
        continue;
    }

    $normalizedDate = normalizeCsvDate($presentation["presentation_date"]);
    $normalizedStartTime = normalizeCsvTime($presentation["start_time"]);
    $normalizedEndTime = normalizeCsvTime($presentation["end_time"]);

    if ($normalizedDate === null || $normalizedStartTime === null || $normalizedEndTime === null) {
        $skipped++;
        $errors[] = "Row " . $rowNumber . " skipped: invalid date or time format.";
        continue;
    }

    $presentation["presentation_date"] = $normalizedDate;
    $presentation["start_time"] = $normalizedStartTime;
    $presentation["end_time"] = $normalizedEndTime;

    $duplicateKey = duplicateImportKey($presentation);

    if (isset($seenKeys[$duplicateKey])) {
        $skipped++;
        $errors[] = "Row " . $rowNumber . " skipped: duplicate in CSV file.";
        continue;
    }

    $duplicateStmt->execute([
        ":title" => $presentation["title"],
        ":presenter" => $presentation["presenter"],
        ":presentation_date" => $presentation["presentation_date"],
        ":start_time" => $presentation["start_time"],
        ":room" => $presentation["room"]
    ]);

    if ($duplicateStmt->fetch(PDO::FETCH_ASSOC)) {
        $skipped++;
        $errors[] = "Row " . $rowNumber . " skipped: duplicate presentation already exists.";
        continue;
    }

    $insertStmt->execute([
        ":title" => $presentation["title"],
        ":description" => $presentation["description"],
        ":presenter" => $presentation["presenter"],
        ":category" => $presentation["category"],
        ":presentation_date" => $presentation["presentation_date"],
        ":start_time" => $presentation["start_time"],
        ":end_time" => $presentation["end_time"],
        ":room" => $presentation["room"]
    ]);

    $seenKeys[$duplicateKey] = true;
    $imported++;
}

fclose($handle);

$_SESSION["success"] = $imported . " presentation(s) imported successfully.";

if ($skipped > 0) {
    $shownErrors = array_slice($errors, 0, 5);
    $_SESSION["error"] = $skipped . " row(s) skipped. " . implode(" ", $shownErrors);

    if (count($errors) > count($shownErrors)) {
        $_SESSION["error"] .= " Additional rows were skipped.";
    }
}

header("Location: ../pages/create_presentation.php");
exit;
