<?php

session_start();



require_once "../config/db.php";
require_once "../config/aws_config.php";

$title = trim($_POST["title"]);
$description = trim($_POST["description"]);
$presenter = trim($_POST["presenter"]);
$category = trim($_POST["category"]);

$presentation_date = $_POST["presentation_date"];

$start_time = $_POST["start_time"];
$end_time = $_POST["end_time"];

$room = trim($_POST["room"]);

$fileUrl = null;
$s3Key = null;

if (
    isset($_FILES["presentation_file"])
    && $_FILES["presentation_file"]["error"] === 0
) {

    $tmpPath = $_FILES["presentation_file"]["tmp_name"];

    $fileName = time() . "_" .
        basename($_FILES["presentation_file"]["name"]);

    try {

        if ($s3 === null || $bucketName === "") {
            $_SESSION["error"] = "S3 is not configured. Add AWS settings in the .env file.";
            header("Location: ../pages/create_presentation.php");
            exit;
        }

        $result = $s3->putObject([

            "Bucket" => $bucketName,

            "Key" => $fileName,

            "SourceFile" => $tmpPath,


            "ContentType" => "application/vnd.openxmlformats-officedocument.presentationml.presentation"

        ]);

        $fileUrl = $result["ObjectURL"];

        $s3Key = $fileName;

    } catch (Exception $e) {

        die("S3 Upload Error: " . $e->getMessage());

    }

}

$sql = "
INSERT INTO presentations
(
    title,
    description,
    presenter,
    category,
    presentation_date,
    start_time,
    end_time,
    room,
    file_url,
    s3_key
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
    :room,
    :file_url,
    :s3_key
)
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":title" => $title,
    ":description" => $description,
    ":presenter" => $presenter,
    ":category" => $category,
    ":presentation_date" => $presentation_date,
    ":start_time" => $start_time,
    ":end_time" => $end_time,
    ":room" => $room,
    ":file_url" => $fileUrl,
    ":s3_key" => $s3Key
]);

$_SESSION["success"] = "Presentation created successfully!";

header("Location: ../pages/dashboard.php");
exit;

// header("Location: ../pages/presentations.php");
// exit;
