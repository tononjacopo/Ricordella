<?php
require_once 'config/db.php';
require_once 'utils/functions.php';

$error = '';

// Check if user is already logged in
if (isLoggedIn()) {
    // Redirect based on role
    if (isAdmin()) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit;
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Username and password are required";
    } else {
        // Query user
        $stmt = $conn->prepare("SELECT id, username, password_hash, role, is_premium FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['is_premium'] = (bool)$user['is_premium'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: user/dashboard.php");
                }
                exit;
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Invalid username or password";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Sign in | Ricordella</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="tonon">
    <link rel="stylesheet" href="assets/style/form-login-reg.css">
    <link rel="stylesheet" href="assets/style/font-general.css">
    <link rel="icon" href="assets/img/logo-favicon.ico" type="image/x-icon">
</head>

<body>
    <div class="vid-container">
        <div class="inner-container">
            <div class="light">
                <div class="box">
                    <h1>Ricordella</h1>
                    <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
                    <form method="POST">
                        <input type="text" name="username" placeholder="Username" required/>
                        <input type="password" name="password" placeholder="Password" required/>
                        <button type="submit">Sign in</button>
                    </form>
                    <p>Not a member? <a href="register.php" class="signup">Sign Up</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>