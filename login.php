<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['signup'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $password]);
            echo "<p style='color: green;'>Signup successful! Please login.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        }
    } elseif (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit;
        } else {
            echo "<p style='color: red;'>Invalid credentials!</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Signup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .auth-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #202122;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #202122;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #a2a9b1;
            border-radius: 4px;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #36c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #2a4b8d;
        }
        .toggle {
            text-align: center;
            margin-top: 15px;
        }
        .toggle a {
            color: #0645ad;
            text-decoration: none;
        }
        .toggle a:hover {
            text-decoration: underline;
        }
        @media (max-width: 480px) {
            .auth-container {
                margin: 20px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h2 id="form-title">Login</h2>
        <form id="auth-form" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group" id="email-group" style="display: none;">
                <label for="email">Email</label>
                <input type="email" id="email" name="email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" id="submit-btn" name="login">Login</button>
        </form>
        <div class="toggle">
            <a href="#" onclick="toggleForm()">Switch to Signup</a>
        </div>
    </div>
    <script>
        function toggleForm() {
            const formTitle = document.getElementById('form-title');
            const emailGroup = document.getElementById('email-group');
            const submitBtn = document.getElementById('submit-btn');
            const toggleLink = document.querySelector('.toggle a');
            
            if (formTitle.textContent === 'Login') {
                formTitle.textContent = 'Signup';
                emailGroup.style.display = 'block';
                submitBtn.name = 'signup';
                submitBtn.textContent = 'Signup';
                toggleLink.textContent = 'Switch to Login';
                document.getElementById('email').required = true;
            } else {
                formTitle.textContent = 'Login';
                emailGroup.style.display = 'none';
                submitBtn.name = 'login';
                submitBtn.textContent = 'Login';
                toggleLink.textContent = 'Switch to Signup';
                document.getElementById('email').required = false;
            }
        }
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
