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

// Process registration form
if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $email = htmlspecialchars(trim($_POST['email']));
    $username = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $accept_terms = isset($_POST['accept_terms']) ? true : false;

    // Validate username format (e.g., length, allowed characters)
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        logError("Invalid username format: $username");
        $error = "Username must be 3-20 characters long and contain only letters, numbers, and underscores.";
    }

    // Validate input
    if (empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (!$accept_terms) {
        $error = "You must accept the terms and conditions";
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username or email is already in use";
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (email, username, password_hash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $username, $password_hash);

            if ($stmt->execute()) {
                // Set session variables
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'user';
                $_SESSION['is_premium'] = false;

                // Redirect to user dashboard
                header("Location: user/dashboard.php");
                exit;
            } else {
                $error = "Registration failed. Please try again.";
            }
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign up | Ricordella</title>
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
            <div class="box">
                <h1>Ricordella</h1>
                <?php if (!empty($error)) echo "<p style='color: red;'>$error</p>"; ?>
                <form action="register.php" method="POST">
                    <input type="email" name="email" placeholder="Email" required/>
                    <input type="text" name="username" placeholder="Username" required/>
                    <input type="password" name="password" placeholder="Password" required/>
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required/>
                    <div class="accept-terms">
                        <input type="checkbox" id="accept_terms" name="accept_terms" required/>
                        <label for="accept_terms">I accept the Terms of Service and Privacy Policy</label>
                    </div>
                    <button type="submit">Sign up</button>
                </form>
                <p>Already a member? <a href="login.php" class="login">Sign in</a></p>
            </div>
        </div>
    </div>

    <script src="../script/check-same-password.js"></script>
</body>
</html>