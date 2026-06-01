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

    <title>Choose Interests</title>

    <style>

        body{
            margin:0;
            padding:40px;
            background:#f4f6f9;
            font-family:Arial,sans-serif;
        }

        .page-title{
            font-size:42px;
            margin-bottom:30px;
            color:#111827;
            text-align:center;
        }

        .form-container{
            max-width:600px;
            margin:0 auto;
            background:white;
            padding:40px;
            border-radius:20px;
            box-shadow:0 4px 15px rgba(0,0,0,0.1);
        }

        .interest-item{
            display:flex;
            align-items:center;
            padding:18px;
            border:1px solid #d1d5db;
            border-radius:12px;
            margin-bottom:15px;
            transition:0.3s;
            cursor:pointer;
        }

        .interest-item:hover{
            border-color:#4f46e5;
            background:#eef2ff;
        }

        .interest-item input{
            margin-right:15px;
            width:20px;
            height:20px;
            cursor:pointer;
        }

        .interest-label{
            font-size:18px;
            color:#111827;
            font-weight:bold;
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
            margin-top:20px;
        }

        .submit-btn:hover{
            background:#3730a3;
        }

        .back-link{
            display:inline-block;
            margin-bottom:25px;
            text-decoration:none;
            color:#4f46e5;
            font-weight:bold;
            font-size:16px;
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
        Select Your Interests
    </h1>

    <div class="form-container">

        <form
            action="../actions/save_interests.php"
            method="POST"
        >

            <label class="interest-item">

                <input
                    type="checkbox"
                    name="categories[]"
                    value="Cloud"
                >

                <span class="interest-label">
                    Cloud
                </span>

            </label>

            <label class="interest-item">

                <input
                    type="checkbox"
                    name="categories[]"
                    value="AI"
                >

                <span class="interest-label">
                    AI
                </span>

            </label>

            <label class="interest-item">

                <input
                    type="checkbox"
                    name="categories[]"
                    value="Web"
                >

                <span class="interest-label">
                    Web
                </span>

            </label>

            <label class="interest-item">

                <input
                    type="checkbox"
                    name="categories[]"
                    value="Security"
                >

                <span class="interest-label">
                    Security
                </span>

            </label>

            <button
                type="submit"
                class="submit-btn"
            >
                Save Interests
            </button>

        </form>

    </div>

</body>

</html>