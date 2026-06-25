<?php

session_start();


if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit;

}

require_once "../config/db.php";
$todaySql = "
SELECT *
FROM presentations
WHERE presentation_date = CURDATE()
ORDER BY start_time ASC
";

$todayStmt = $pdo->prepare($todaySql);
$todayStmt->execute();

$todayPresentations = $todayStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Dashboard</title>

    <style>

        body{
            margin:0;
            padding:40px;
            background:#f4f6f9;
            font-family:Arial,sans-serif;
        }

        .dashboard-container{
            max-width:1000px;
            margin:0 auto;
        }

        .top-bar{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:40px;
        }

        .welcome-title{
            font-size:42px;
            color:#111827;
            margin:0;
        }

        .logout-btn{
            background:#dc2626;
            color:white;
            padding:12px 20px;
            border-radius:10px;
            text-decoration:none;
            transition:0.3s;
        }

        .logout-btn:hover{
            background:#b91c1c;
        }

        .cards-container{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
            gap:25px;
        }

        .dashboard-card{
            background:white;
            padding:30px;
            border-radius:20px;
            box-shadow:0 4px 15px rgba(0,0,0,0.1);
            transition:0.3s;
        }

        .dashboard-card:hover{
            transform:translateY(-5px);
        }

        .card-title{
            font-size:24px;
            margin-bottom:15px;
            color:#111827;
        }

        .card-text{
            color:#6b7280;
            margin-bottom:25px;
            line-height:1.6;
        }

        .card-btn{
            display:inline-block;
            padding:12px 18px;
            background:#4f46e5;
            color:white;
            text-decoration:none;
            border-radius:10px;
            transition:0.3s;
        }

        .card-btn:hover{
            background:#3730a3;
        }
        .today-section{
    margin-top:50px;
}

.today-title{
    font-size:32px;
    color:#111827;
    margin-bottom:20px;
}

.today-card{
    background:white;
    padding:20px;
    border-radius:15px;
    margin-bottom:15px;
    box-shadow:0 4px 15px rgba(0,0,0,0.08);
}

.today-card h3{
    margin:0 0 10px 0;
}

.today-info{
    color:#6b7280;
    margin-bottom:5px;
}

.empty-message{
    background:white;
    padding:20px;
    border-radius:15px;
    color:#6b7280;
}

    </style>

</head>

<body>
    <?php require_once "../includes/navbar.php"; ?>

    <div class="dashboard-container">

        <div class="top-bar">

            <h1 class="welcome-title">
                Welcome,
                <?php echo htmlspecialchars($_SESSION["user_name"]); ?>
            </h1>

        </div>

        <div class="cards-container">

            <div class="dashboard-card">

                <h2 class="card-title">
                    Presentations
                </h2>

                <p class="card-text">
                    View all created presentations,
                    uploaded files and schedules.
                </p>

                <a
                    href="presentations.php"
                    class="card-btn"
                >
                    Open Presentations
                </a>

            </div>

            <div class="dashboard-card">

                <h2 class="card-title">
                    Create Presentation
                </h2>

                <p class="card-text">
                    Create a new presentation,
                    upload files and organize events.
                </p>

                <a
                    href="create_presentation.php"
                    class="card-btn"
                >
                    Create Now
                </a>

            </div>

        </div>

        <div class="today-section">

    <h2 class="today-title">
        Today's Presentations
    </h2>

    <?php if (count($todayPresentations) > 0): ?>

        <?php foreach ($todayPresentations as $presentation): ?>

            <div class="today-card">

                <h3>
                    <?php echo htmlspecialchars($presentation["title"]); ?>
                </h3>

                <div class="today-info">
                    Presenter:
                    <?php echo htmlspecialchars($presentation["presenter"]); ?>
                </div>

                <div class="today-info">
                    Time:
                    <?php echo $presentation["start_time"]; ?>
                    -
                    <?php echo $presentation["end_time"]; ?>
                </div>

                <div class="today-info">
                    Room:
                    <?php echo htmlspecialchars($presentation["room"]); ?>
                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="empty-message">
            No presentations scheduled for today.
        </div>

    <?php endif; ?>

</div>

    </div>

</body>

</html>