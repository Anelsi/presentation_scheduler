<?php
session_start();
// Взимаме старите стойности, ако съществуват
$old_name = $_SESSION["old_input"]["name"] ?? "";
$old_email = $_SESSION["old_input"]["email"] ?? "";
unset($_SESSION["old_input"]); // Изчистваме ги веднага след като сме ги прочели
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        body{
            margin:0;
            padding:40px;
            background:#f4f6f9;
            font-family:Arial,sans-serif;
            display:flex;
            justify-content:center;
            align-items:center;
            min-height:100vh;
            box-sizing:border-box;
        }
        .register-container{
            width:100%;
            max-width:500px;
            background:white;
            padding:40px;
            border-radius:20px;
            box-shadow:0 4px 15px rgba(0,0,0,0.1);
        }
        .page-title{
            font-size:42px;
            margin-bottom:30px;
            color:#111827;
            text-align:center;
        }
        .error-alert {
            background: #fde8e8;
            color: #dc2626;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #f8b4b4;
            font-size: 15px;
        }
        .error-alert ul {
            margin: 0;
            padding-left: 20px;
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
        input{
            width:100%;
            padding:14px;
            border:1px solid #d1d5db;
            border-radius:10px;
            font-size:16px;
            box-sizing:border-box;
        }
        input:focus{
            outline:none;
            border-color:#4f46e5;
        }
        .register-btn{
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
        .register-btn:hover{
            background:#3730a3;
        }
        .login-link{
            margin-top:20px;
            text-align:center;
            color:#6b7280;
        }
        .login-link a{
            color:#4f46e5;
            text-decoration:none;
            font-weight:bold;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <h1 class="page-title">Register</h1>

        <?php if (isset($_SESSION["errors"]) && !empty($_SESSION["errors"])): ?>
            <div class="error-alert">
                <ul>
                    <?php foreach ($_SESSION["errors"] as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION["errors"]); ?>
        <?php endif; ?>

        <form action="../actions/register_action.php" method="POST">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" placeholder="Enter your name" value="<?php echo htmlspecialchars($old_name); ?>">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($old_email); ?>">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password">
            </div>

            <button type="submit" class="register-btn">Register</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
</body>
</html>