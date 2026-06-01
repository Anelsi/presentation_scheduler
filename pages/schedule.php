<?php

session_start();

require_once "../config/db.php";

$today = date("Y-m-d");

$upcomingSql = "
SELECT *
FROM presentations
WHERE presentation_date >= :today
ORDER BY presentation_date ASC, start_time ASC
";

$upcomingStmt = $pdo->prepare($upcomingSql);

$upcomingStmt->execute([
    ":today" => $today
]);

$upcomingPresentations = $upcomingStmt->fetchAll(PDO::FETCH_ASSOC);

$presentedSql = "
SELECT *
FROM presentations
WHERE presentation_date < :today
ORDER BY presentation_date DESC, start_time DESC
";

$presentedStmt = $pdo->prepare($presentedSql);

$presentedStmt->execute([
    ":today" => $today
]);

$presentedPresentations = $presentedStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Schedule</title>

    <style>

        body{
            margin:0;
            padding:40px;
            background:#f4f6f9;
            font-family:Arial,sans-serif;
        }

        .back-link{
            display:inline-block;
            margin-bottom:25px;
            text-decoration:none;
            color:#4f46e5;
            font-weight:bold;
            font-size:16px;
        }

        .page-title{
            font-size:42px;
            margin-bottom:40px;
            color:#111827;
            text-align:center;
        }

        .section-title{
            font-size:30px;
            margin-bottom:25px;
            color:#111827;
        }

        .presentations-container{
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
            gap:20px;
            margin-bottom:50px;
        }

        .presentation-card{
            background:white;
            padding:25px;
            border-radius:20px;
            box-shadow:0 4px 15px rgba(0,0,0,0.1);
            transition:0.3s;
        }

        .presentation-card:hover{
            transform:translateY(-5px);
        }

        .upcoming{
            border-left:6px solid #059669;
        }

        .presented{
            border-left:6px solid #dc2626;
        }

        .presentation-title{
            font-size:24px;
            margin-bottom:15px;
            color:#111827;
        }

        .info{
            margin-bottom:10px;
            color:#374151;
        }

        .empty-message{
            background:white;
            padding:20px;
            border-radius:16px;
            color:#6b7280;
            margin-bottom:40px;
            box-shadow:0 4px 15px rgba(0,0,0,0.08);
        }

    </style>

</head>

<body>
    <?php require_once "../includes/navbar.php"; ?>

    <a
        href="dashboard.php"
        class="back-link"
    >
        ← Back to Dashboard
    </a>

    <h1 class="page-title">
        Full Schedule
    </h1>

    <h2 class="section-title">
        Upcoming Presentations
    </h2>

    <?php if (count($upcomingPresentations) === 0): ?>

        <div class="empty-message">
            No upcoming presentations
        </div>

    <?php endif; ?>

    <div class="presentations-container">

        <?php foreach ($upcomingPresentations as $presentation): ?>

            <div class="presentation-card upcoming">

                <h3 class="presentation-title">
                    <?php echo htmlspecialchars($presentation["title"]); ?>
                </h3>

                <p class="info">
                    <strong>Presenter:</strong>
                    <?php echo htmlspecialchars($presentation["presenter"]); ?>
                </p>

                <p class="info">
                    <strong>Category:</strong>
                    <?php echo htmlspecialchars($presentation["category"]); ?>
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

            </div>

        <?php endforeach; ?>

    </div>

    <h2 class="section-title">
        Presented Presentations
    </h2>

    <?php if (count($presentedPresentations) === 0): ?>

        <div class="empty-message">
            No presented presentations
        </div>

    <?php endif; ?>

    <div class="presentations-container">

        <?php foreach ($presentedPresentations as $presentation): ?>

            <div class="presentation-card presented">

                <h3 class="presentation-title">
                    <?php echo htmlspecialchars($presentation["title"]); ?>
                </h3>

                <p class="info">
                    <strong>Presenter:</strong>
                    <?php echo htmlspecialchars($presentation["presenter"]); ?>
                </p>

                <p class="info">
                    <strong>Category:</strong>
                    <?php echo htmlspecialchars($presentation["category"]); ?>
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

            </div>

        <?php endforeach; ?>

    </div>

</body>

</html>