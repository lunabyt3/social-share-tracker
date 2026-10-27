<?php

require_once __DIR__ . '/../config/database.php';

// Allow account creation only when no admin account exists.
try {
    $stmt = $pdo->query('SELECT COUNT(*) FROM admin_users');
    $adminCount = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Database error. Please check your database configuration.');
}

if ($adminCount > 0) {
    exit('Admin setup is already completed. Account creation is disabled.');
}

$username = 'admin';
$password = ''; // Set your own password before running this script.

// Check if the password is still empty or just spaces
if (trim($password) === '') {
    exit('Please set a password in create-admin.php before running this script.');
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare(
        'INSERT INTO admin_users (username, password_hash)
         VALUES (:username, :password_hash)'
    );

    $stmt->execute([
        ':username' => $username,
        ':password_hash' => $passwordHash
    ]);

    echo 'Admin account created successfully. ';
    echo '<a href="login.php">Go to login</a>.';
    echo '<p>For security, delete or disable create-admin.php now.</p>';

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Unable to create the admin account. Please check the server error log.');
}