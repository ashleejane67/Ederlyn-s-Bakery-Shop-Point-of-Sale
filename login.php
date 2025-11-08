<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT admin_id, password FROM admin WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($admin_id, $hash);
    if ($stmt->fetch() && password_verify($password, $hash)) {
        $_SESSION['admin_id'] = $admin_id;
        $_SESSION['username'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid login.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <style>
        #loginPage {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            width: 100vw;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            background-color: #eaeff5;
        }

        .container {
            display: flex;
            width: 900px;
            height: 500px;
            max-width: 95vw;
            max-height: 90vh;
            background-color: white;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            overflow: hidden;
        }

        .login-section {
            width: 50%;
            background-color: #b4d4ff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
        }

        .logo img {
            width: 150px;
            margin-bottom: 10px;
        }

        h2 {
            font-size: 22px;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        .input-group {
            width: 100%;
            max-width: 300px;
            margin-bottom: 15px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            background-color: #f0f0f0;
        }

        button {
            width: 100%;
            max-width: 300px;
            padding: 10px;
            background-color: #4a90e2;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }

        button:hover {
            background-color: #2c6db5;
        }

        .image-section {
            width: 50%;
            background-color: #ddd;
        }

        .image-section img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .error-message {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            text-align: center;
            width: 100%;
            max-width: 300px;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .container {
                width: 95%;
                flex-direction: column;
                height: auto;
            }

            .login-section,
            .image-section {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div id="loginPage">
        <div class="container">
            <div class="login-section">
                <div class="logo">
                    <img src="logo.png" alt="Your Logo">
                </div>
                <h2>Admin Login</h2>
                <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>
                <form method="post">
                    <div class="input-group">
                        <input type="text" name="username" placeholder="Username" required>
                    </div>
                    <div class="input-group">
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit">Login</button>
                </form>
            </div>
            <div class="image-section">
                <img src="bakery.jfif" alt="Login Image">
            </div>
        </div>
    </div>
</body>

</html>