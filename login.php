<?php
require_once 'config/db.php';
require_once 'utils/functions.php';

$error = '';

// Check if user is already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Rate limiting: block login attempts after 5 failed attempts within 15 minutes
$ip_address = $_SERVER['REMOTE_ADDR'];
$rate_limit_key = "login_attempts_$ip_address";
$max_attempts = 5;
$lockout_time = 15 * 60; // 15 minutes

if (isset($_SESSION[$rate_limit_key]) && $_SESSION[$rate_limit_key]['count'] >= $max_attempts) {
    $time_diff = time() - $_SESSION[$rate_limit_key]['last_attempt'];
    if ($time_diff < $lockout_time) {
        logError("Rate limit exceeded for IP: $ip_address");
        die("Too many login attempts. Please try again after " . (int)(($lockout_time - $time_diff) / 60) . " minutes.");
    } else {
        // Reset rate limit if lockout time has passed
        unset($_SESSION[$rate_limit_key]);
    }
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        logError("Login failed: Missing username or password for IP: $ip_address");
        $error = "Username and password are required";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password_hash, role, is_premium FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['is_premium'] = (bool)$user['is_premium'];

                // Reset rate limit on successful login
                unset($_SESSION[$rate_limit_key]);

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

    // Increment rate limit counter
    if (!isset($_SESSION[$rate_limit_key])) {
        $_SESSION[$rate_limit_key] = ['count' => 0, 'last_attempt' => time()];
    }
    $_SESSION[$rate_limit_key]['count']++;
    $_SESSION[$rate_limit_key]['last_attempt'] = time();
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