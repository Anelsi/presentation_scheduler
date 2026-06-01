<?php

session_start();

require_once "config/db.php";

$sql = "
SELECT *
FROM presentations
WHERE presentation_date >= CURDATE()
ORDER BY presentation_date ASC
LIMIT 3
";

$stmt = $pdo->query($sql);

$presentations = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Presentation Scheduler</title>

    <style>

        body{
            margin:0;
            font-family:Arial,sans-serif;
            background:#f4f6f9;
            color:#111827;
        }

        .hero{
            background:linear-gradient(135deg,#4f46e5,#7c3aed);
            color:white;
            padding:100px 40px;
            text-align:center;
        }

        .hero h1{
            font-size:60px;
            margin-bottom:20px;
        }

        .hero p{
            font-size:22px;
            max-width:700px;
            margin:auto;
            margin-bottom:40px;
            line-height:1.6;
        }

        .hero-buttons{
            display:flex;
            justify-content:center;
            gap:20px;
            flex-wrap:wrap;
        }

        .btn{
            padding:16px 28px;
            border-radius:12px;
            text-decoration:none;
            font-size:18px;
            transition:0.3s;
        }

        .login-btn{
            background:white;
            color:#4f46e5;
        }

        .register-btn{
            background:#111827;
            color:white;
        }

        .btn:hover{
            transform:translateY(-3px);
        }

        .section{
            padding:60px 40px;
        }

        .section-title{
            font-size:40px;
            margin-bottom:30px;
            text-align:center;
        }

        .cards{
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
            gap:20px;
        }

        .card{
            background:white;
            padding:25px;
            border-radius:20px;
            box-shadow:0 4px 15px rgba(0,0,0,0.08);
        }

        .card h3{
            font-size:26px;
            margin-bottom:10px;
        }

        .info{
            margin-bottom:10px;
            color:#374151;
        }

    </style>

</head>

<body>

    <section class="hero">

        <h1>
            Presentation Scheduler
        </h1>

        <p>
            Organize presentations, manage schedules,
            upload files to AWS S3 and track upcoming events.
        </p>

        <div class="hero-buttons">

            <a
                href="pages/login.php"
                class="btn login-btn"
            >
                Login
            </a>

            <a
                href="pages/register.php"
                class="btn register-btn"
            >
                Register
            </a>

        </div>

    </section>

    <section class="section">

        <h2 class="section-title">
            Upcoming Presentations
        </h2>

        <div class="cards">

            <?php foreach ($presentations as $presentation): ?>

                <div class="card">

                    <h3>
                        <?php echo htmlspecialchars($presentation["title"]); ?>
                    </h3>

                    <p class="info">
                        Presenter:
                        <?php echo htmlspecialchars($presentation["presenter"]); ?>
                    </p>

                    <p class="info">
                        Category:
                        <?php echo htmlspecialchars($presentation["category"]); ?>
                    </p>

                    <p class="info">
                        Date:
                        <?php echo $presentation["presentation_date"]; ?>
                    </p>

                    <p class="info">
                        Room:
                        <?php echo htmlspecialchars($presentation["room"]); ?>
                    </p>

                </div>

            <?php endforeach; ?>

        </div>

    </section>

</body>

</html>