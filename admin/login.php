<?php

session_start();

require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, username, password_hash
             FROM admin_users
             WHERE username = :username
             LIMIT 1'
        );

        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            header('Location: index.php');
            exit;
        }

        $error = 'Invalid username or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ShareTrack Admin Login</title>
    <!-- Add icon library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #10264f;
        }

        /* Layout the header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: white;
            color: #10264f;
            border: 1px solid #e5eaf0;
        }

        .logo {

            font-size: 30px;
            font-weight: 800;
            color: #10264f;
            letter-spacing: -1px;
        }

        .login-box {
            max-width: 380px;
            margin: 60px auto;
            padding: 28px;
            background: white;
            border-radius: 10px;
            text-align: center;
        }

        .login-box form {
            text-align: left;
            margin-top: 40px;
        }

        .login-box a {
            text-decoration: none;
        }

        input,
        button {
            width: 100%;
            padding: 11px;
            margin-top: 10px;
            margin-bottom: 20px;
            box-sizing: border-box;
        }

        .input-container {
            display: flex;
        }

        .icon {
            padding: 10px;
            min-width: 10px;
            text-align: center;
            margin-top: 10px;
        }

        button {
            cursor: pointer;
            background-color: #1877f2;
            border: none;
            color: white;
        }

        .error {
            color: #b00020;
        }
    </style>
</head>

<body>
    <header>
        <!-- Site Logo -->
        <div class="logo">ShareTrack</div>
    </header>

    <div class="login-box">
        <h1>Admin Login</h1>
        <p>Please enter your credentials to continue</p>

        <?php if ($error !== ''): ?>
            <p class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <form method="POST">
            <div class="input-container">
                <i class="fa fa-user icon"></i>
                <input type="text" id="username" name="username" maxlength="50" autocomplete="username"
                    placeholder="Username" required>
            </div>

            <div class="input-container">
                <i class="fa fa-lock icon"></i>
                <input type="password" id="password" name="password" autocomplete="current-password"
                    placeholder="Password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <a href="../">Back to Website</a>
    </div>

</body>

</html>