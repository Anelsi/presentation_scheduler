<?php

session_start();

require_once "../config/db.php";

$id = $_GET["id"];

$sql = "
SELECT *
FROM presentations
WHERE id = :id
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":id" => $id
]);

$presentation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$presentation) {

    die("Presentation not found");

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        <?php echo htmlspecialchars($presentation["title"]); ?>
    </title>

    <style>

        body{
            margin:0;
            padding:40px;
            background:#f4f6f9;
            font-family:Arial,sans-serif;
        }

        .details-container{
            max-width:900px;
            margin:auto;
        }

        .details-card{
            background:white;
            padding:40px;
            border-radius:24px;
            box-shadow:0 4px 15px rgba(0,0,0,0.1);
        }

        .title{
            font-size:48px;
            margin-bottom:20px;
            color:#111827;
        }

        .description{
            font-size:18px;
            line-height:1.8;
            color:#4b5563;
            margin-bottom:30px;
        }

        .badge{
            display:inline-block;
            background:#4f46e5;
            color:white;
            padding:10px 16px;
            border-radius:999px;
            margin-bottom:30px;
            font-size:14px;
        }

        .info{
            margin-bottom:16px;
            font-size:18px;
            color:#374151;
        }

        .actions{
            margin-top:40px;
            display:flex;
            gap:15px;
            flex-wrap:wrap;
        }

        .btn{
            padding:14px 20px;
            border-radius:10px;
            text-decoration:none;
            color:white;
            transition:0.3s;
        }

        .open-btn{
            background:#2563eb;
        }

        .schedule-btn{
            background:#059669;
        }

        .edit-btn{
            background:#d97706;
        }

        .delete-btn{
            background:#dc2626;
        }

        .alert-success{
            background:#d1fae5;
            color:#065f46;
            padding:15px;
            border-radius:10px;
            margin-bottom:20px;
            border:1px solid #a7f3d0;
            font-weight:bold;
        }

        .alert-error{
            background:#fde8e8;
            color:#dc2626;
            padding:15px;
            border-radius:10px;
            margin-bottom:20px;
            border:1px solid #f8b4b4;
            font-weight:bold;
        }

        .btn:hover{
            transform:translateY(-2px);
        }

    </style>

</head>

<body>

<?php require_once "../includes/navbar.php"; ?>

<div class="details-container">

    <?php if (isset($_SESSION["success"])): ?>
        <div class="alert-success"><?php echo htmlspecialchars($_SESSION["success"]); unset($_SESSION["success"]); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION["error"])): ?>
        <div class="alert-error"><?php echo htmlspecialchars($_SESSION["error"]); unset($_SESSION["error"]); ?></div>
    <?php endif; ?>

    <div class="details-card">

        <div class="badge">
            <?php echo htmlspecialchars($presentation["category"]); ?>
        </div>

        <h1 class="title">
            <?php echo htmlspecialchars($presentation["title"]); ?>
        </h1>

        <p class="description">
            <?php echo htmlspecialchars($presentation["description"]); ?>
        </p>

        <p class="info">
            <strong>Presenter:</strong>
            <?php echo htmlspecialchars($presentation["presenter"]); ?>
        </p>

        <p class="info">
            <strong>Date:</strong>
            <?php echo $presentation["presentation_date"]; ?>
        </p>

        <p class="info">
            <strong>Time:</strong>
            <?php echo $presentation["start_time"]; ?>
            -
            <?php echo $presentation["end_time"]; ?>
        </p>

        <p class="info">
            <strong>Room:</strong>
            <?php echo htmlspecialchars($presentation["room"]); ?>
        </p>

        <div class="actions">

            <?php if (!empty($presentation["file_url"])): ?>

                <a
                    href="<?php echo $presentation["file_url"]; ?>"
                    target="_blank"
                    class="btn open-btn"
                >
                    Open Presentation
                </a>

            <?php endif; ?>

            <?php if ($_SESSION["user_role"] === "student"): ?>

                <a
                    href="../actions/add_to_schedule.php?id=<?php echo $presentation["id"]; ?>"
                    class="btn schedule-btn"
                >
                    Add To Schedule
                </a>

            <?php endif; ?>

            <?php if ($_SESSION["user_role"] === "admin"): ?>

                <a
                    href="edit_presentation.php?id=<?php echo $presentation["id"]; ?>"
                    class="btn edit-btn"
                >
                    Edit
                </a>

                <a
                    href="../actions/delete_presentation.php?id=<?php echo $presentation["id"]; ?>"
                    class="btn delete-btn"
                >
                    Delete
                </a>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>

</html>
