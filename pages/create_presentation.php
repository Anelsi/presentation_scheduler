<?php

session_start();

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit;

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>Create Presentation</title>

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

        .import-container{
            margin-bottom:25px;
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
            background:#4f46e5;
            color:white;
            border:none;
            border-radius:12px;
            font-size:18px;
            cursor:pointer;
            transition:0.3s;
        }

        .submit-btn:hover{
            background:#3730a3;
        }

        .alert-success{
            max-width:700px;
            margin:0 auto 20px auto;
            background:#d1fae5;
            color:#065f46;
            padding:15px;
            border-radius:10px;
            border:1px solid #a7f3d0;
            font-weight:bold;
        }

        .alert-error{
            max-width:700px;
            margin:0 auto 20px auto;
            background:#fde8e8;
            color:#dc2626;
            padding:15px;
            border-radius:10px;
            border:1px solid #f8b4b4;
            font-weight:bold;
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
        Create Presentation
    </h1>

    <?php if (isset($_SESSION["success"])): ?>
        <div class="alert-success"><?php echo htmlspecialchars($_SESSION["success"]); unset($_SESSION["success"]); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION["error"])): ?>
        <div class="alert-error"><?php echo htmlspecialchars($_SESSION["error"]); unset($_SESSION["error"]); ?></div>
    <?php endif; ?>

    <div class="form-container import-container">

        <form
            action="../actions/import_presentations_csv.php"
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="form-group">

                <label>Import CSV</label>

                <input
                    type="file"
                    name="presentation_csv"
                    accept=".csv,text/csv"
                    required
                >

            </div>

            <button
                type="submit"
                class="submit-btn"
            >
                Import Presentations
            </button>

        </form>

    </div>

    <div class="form-container">

        <form
            action="../actions/create_presentation_action.php"
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="form-group">

                <label>Title</label>

                <input
                    type="text"
                    name="title"
                    placeholder="Title"
                >

            </div>

            <div class="form-group">

                <label>Description</label>

                <textarea
                    name="description"
                    placeholder="Description"
                ></textarea>

            </div>

            <div class="form-group">

                <label>Presenter</label>

                <input
                    type="text"
                    name="presenter"
                    placeholder="Presenter"
                >

            </div>

            <div class="form-group">
                <label>Category (Optional)</label>
                <select name="category" style="width:100%; padding:14px; border:1px solid #d1d5db; border-radius:10px; font-size:16px;">
                    <option value="">Auto-detect from title and description</option>
                    <option value="Cloud">Cloud</option>
                    <option value="AI">AI</option>
                    <option value="Web">Web</option>
                    <option value="Security">Security</option>
                </select>
            </div>

            <div class="form-group">

                <label>Presentation Date</label>

                <input
                    type="date"
                    name="presentation_date"
                >

            </div>

            <div class="form-group">

                <label>Start Time</label>

                <input
                    type="time"
                    name="start_time"
                >

            </div>

            <div class="form-group">

                <label>End Time</label>

                <input
                    type="time"
                    name="end_time"
                >

            </div>

            <div class="form-group">

                <label>Room</label>

                <input
                    type="text"
                    name="room"
                    placeholder="Room"
                >

            </div>

            <div class="form-group">

                <label>Presentation File</label>

                <input
                    type="file"
                    name="presentation_file"
                >

            </div>

            <button
                type="submit"
                class="submit-btn"
            >
                Create Presentation
            </button>

        </form>

    </div>

</body>

</html>
