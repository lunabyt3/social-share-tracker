<?php

session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

$maxAttempts = 5;
$lockoutMinutes = 15;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } else {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $now = date('Y-m-d H:i:s');

        // Check existing login attempt record
        $stmt = $pdo->prepare(
            'SELECT id, attempt_count, locked_until
             FROM login_attempts
             WHERE username = :username
               AND ip_address = :ip_address
             LIMIT 1'
        );

        $stmt->execute([
            ':username' => $username,
            ':ip_address' => $ipAddress
        ]);

        $attempt = $stmt->fetch();

        // Check whether the account is currently locked
        if ($attempt && $attempt['locked_until'] !== null) {
            $lockedUntil = strtotime($attempt['locked_until']);

            if ($lockedUntil !== false && $lockedUntil > time()) {
                $remainingMinutes = max(
                    1,
                    (int) ceil(($lockedUntil - time()) / 60)
                );

                $error = "Too many failed login attempts. Please try again in {$remainingMinutes} minute(s).";
            } else {
                // Lockout has expired, remove the old attempt record
                $stmt = $pdo->prepare(
                    'DELETE FROM login_attempts WHERE id = :id'
                );

                $stmt->execute([
                    ':id' => $attempt['id']
                ]);

                $attempt = null;
            }
        }

        // Continue login process if the user is not locked
        if ($error === '') {
            $stmt = $pdo->prepare(
                'SELECT id, username, password_hash
                 FROM admin_users
                 WHERE username = :username
                 LIMIT 1'
            );

            $stmt->execute([
                ':username' => $username
            ]);

            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Successful login: clear failed login attempts
                if ($attempt) {
                    $stmt = $pdo->prepare(
                        'DELETE FROM login_attempts
                         WHERE id = :id'
                    );

                    $stmt->execute([
                        ':id' => $attempt['id']
                    ]);
                }

                session_regenerate_id(true);

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];

                header('Location: index.php');
                exit;
            }

            // Failed login
            if ($attempt) {
                $newAttemptCount = (int) $attempt['attempt_count'] + 1;

                // Lock after reaching the maximum number of failed attempts
                if ($newAttemptCount >= $maxAttempts) {
                    $lockedUntil = date(
                        'Y-m-d H:i:s',
                        time() + ($lockoutMinutes * 60)
                    );

                    $stmt = $pdo->prepare(
                        'UPDATE login_attempts
                         SET attempt_count = :attempt_count,
                             last_attempt_at = :last_attempt_at,
                             locked_until = :locked_until
                         WHERE id = :id'
                    );

                    $stmt->execute([
                        ':attempt_count' => $newAttemptCount,
                        ':last_attempt_at' => $now,
                        ':locked_until' => $lockedUntil,
                        ':id' => $attempt['id']
                    ]);

                    $error = "Too many failed login attempts. Please try again in {$lockoutMinutes} minutes.";
                } else {
                    $stmt = $pdo->prepare(
                        'UPDATE login_attempts
                         SET attempt_count = :attempt_count,
                             last_attempt_at = :last_attempt_at
                         WHERE id = :id'
                    );

                    $stmt->execute([
                        ':attempt_count' => $newAttemptCount,
                        ':last_attempt_at' => $now,
                        ':id' => $attempt['id']
                    ]);

                    $remainingAttempts = $maxAttempts - $newAttemptCount;

                    $error = "Invalid username or password. {$remainingAttempts} attempt(s) remaining.";
                }
            } else {
                // First failed login attempt
                $stmt = $pdo->prepare(
                    'INSERT INTO login_attempts
                     (username, ip_address, attempt_count, last_attempt_at)
                     VALUES (:username, :ip_address, 1, :last_attempt_at)'
                );

                $stmt->execute([
                    ':username' => $username,
                    ':ip_address' => $ipAddress,
                    ':last_attempt_at' => $now
                ]);

                $remainingAttempts = $maxAttempts - 1;

                $error = "Invalid username or password. {$remainingAttempts} attempt(s) remaining.";
            }
        }
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

        #submit {
            cursor: pointer;
            background-color: #1877f2;
            border: none;
            color: white;
        }

        .error {
            color: #b00020;
        }

        .password-container {
            position: relative;
        }

        .password-container input {
            padding-right: 45px;
        }

        .password-toggle {
            position: absolute;
            right: 8px;
            top: 40%;
            transform: translateY(-50%);
            width: auto;
            margin: 0;
            padding: 5px;
            border: none;
            background: transparent;
            color: #10264f;
            cursor: pointer;
        }

        .password-toggle:hover {
            background: transparent;
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

            <div class="input-container password-container">
                <i class="fa fa-lock icon"></i>

                <input type="password" id="password" name="password" autocomplete="current-password"
                    placeholder="Password" required>

                <button type="button" id="togglePassword" class="password-toggle" aria-label="Show password">
                    <i class="fa fa-eye"></i>
                </button>
            </div>
            <button id="submit" type="submit">Login</button>
        </form>
        <a href="../">Back to Website</a>
    </div>

</body>

<script>
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    togglePassword.addEventListener('click', function () {
        const isPassword = passwordInput.type === 'password';

        passwordInput.type = isPassword ? 'text' : 'password';

        this.innerHTML = isPassword
            ? '<i class="fa fa-eye-slash"></i>'
            : '<i class="fa fa-eye"></i>';

        this.setAttribute(
            'aria-label',
            isPassword ? 'Hide password' : 'Show password'
        );
    });
</script>

</html>