<?php

session_start();

require_once "../config/db.php";

$id = $_GET["id"];

$sql = "SELECT * FROM presentations WHERE id = :id";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":id" => $id
]);

$presentation = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Edit Presentation</title>

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
            margin-bottom:30px;
            color:#111827;
            text-align:center;
        }

        .form-container{
            max-width:700px;
            margin:0 auto;
            background:white;
            padding:40px;
            border-radius:20px;
            box-shadow:0 4px 15px rgba(0,0,0,0.1);
        }

        .form-group{
            margin-bottom:20px;
        }

        label{
            display:block;
            margin-bottom:8px;
            font-weight:bold;
            color:#374151;
        }

        input,
        textarea{
            width:100%;
            padding:14px;
            border:1px solid #d1d5db;
            border-radius:10px;
            font-size:16px;
            box-sizing:border-box;
        }

        textarea{
            min-height:120px;
            resize:vertical;
        }

        input:focus,
        textarea:focus{
            outline:none;
            border-color:#4f46e5;
        }

        .submit-btn{
            width:100%;
            padding:16px;
            background:#059669;
            color:white;
            border:none;
            border-radius:12px;
            font-size:18px;
            cursor:pointer;
            transition:0.3s;
        }

        .submit-btn:hover{
            background:#047857;
        }

    </style>

</head>

<body>
    <?php require_once "../includes/navbar.php"; ?>

    <a
        href="presentations.php"
        class="back-link"
    >
        ← Back to Presentations
    </a>

    <h1 class="page-title">
        Edit Presentation
    </h1>

    <div class="form-container">

        <form
            action="../actions/update_presentation.php"
            method="POST"
        >

            <input
                type="hidden"
                name="id"
                value="<?php echo $presentation["id"]; ?>"
            >

            <div class="form-group">

                <label>Title</label>

                <input
                    type="text"
                    name="title"
                    value="<?php echo htmlspecialchars($presentation["title"]); ?>"
                >

            </div>

            <div class="form-group">

                <label>Description</label>

                <textarea name="description"><?php
                    echo htmlspecialchars($presentation["description"]);
                ?></textarea>

            </div>

            <div class="form-group">

                <label>Presenter</label>

                <input
                    type="text"
                    name="presenter"
                    value="<?php echo htmlspecialchars($presentation["presenter"]); ?>"
                >

            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category" style="width:100%; padding:14px; border:1px solid #d1d5db; border-radius:10px; font-size:16px;">
                    <option value="Cloud" <?php if($presentation["category"] === 'Cloud') echo 'selected'; ?>>Cloud</option>
                    <option value="AI" <?php if($presentation["category"] === 'AI') echo 'selected'; ?>>AI</option>
                    <option value="Web" <?php if($presentation["category"] === 'Web') echo 'selected'; ?>>Web</option>
                    <option value="Security" <?php if($presentation["category"] === 'Security') echo 'selected'; ?>>Security</option>
                </select>
            </div>

            <div class="form-group">

                <label>Presentation Date</label>

                <input
                    type="date"
                    name="presentation_date"
                    value="<?php echo $presentation["presentation_date"]; ?>"
                >

            </div>

            <div class="form-group">

                <label>Start Time</label>

                <input
                    type="time"
                    name="start_time"
                    value="<?php echo $presentation["start_time"]; ?>"
                >

            </div>

            <div class="form-group">

                <label>End Time</label>

                <input
                    type="time"
                    name="end_time"
                    value="<?php echo $presentation["end_time"]; ?>"
                >

            </div>

            <div class="form-group">

                <label>Room</label>

                <input
                    type="text"
                    name="room"
                    value="<?php echo htmlspecialchars($presentation["room"]); ?>"
                >

            </div>

            <button
                type="submit"
                class="submit-btn"
            >
                Update Presentation
            </button>

        </form>

    </div>

</body>

</html>