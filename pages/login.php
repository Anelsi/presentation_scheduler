<?php
session_start();
$old_email = $_SESSION["old_input"]["email"] ?? "";
unset($_SESSION["old_input"]); // Изчистваме веднага
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
        .login-container{
            width:100%;
            max-width:450px;
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
            font-weight: bold;
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
        .login-btn{
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
        .login-btn:hover{
            background:#3730a3;
        }
        .register-link{
            margin-top:20px;
            text-align:center;
            color:#6b7280;
        }
        .register-link a{
            color:#4f46e5;
            text-decoration:none;
            font-weight:bold;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h1 class="page-title">Login</h1>

        <?php if (isset($_SESSION["error"])): ?>
            <div class="error-alert">
                <?php 
                    echo htmlspecialchars($_SESSION["error"]); 
                    unset($_SESSION["error"]); 
                ?>
            </div>
        <?php endif; ?>

        <form action="../actions/login_action.php" method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($old_email); ?>">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password">
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>

        <div class="register-link">
            Not registered? <a href="register.php">Click here to register</a>
        </div>
    </div>
</body>
</html>