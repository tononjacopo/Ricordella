<?php
require_once '../config/db.php';
require_once '../utils/functions.php';


// Require admin privileges
requireAdmin();

$error = '';
$success = '';

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: user_list.php");
    exit;
}

$user_id = (int)$_GET['id'];

// Get the user
$user = getUserById($user_id);

// Check if the user exists
if (!$user) {
    $_SESSION['admin_message'] = "User not found.";
    $_SESSION['admin_message_type'] = "error";
    header("Location: user_list.php");
    exit;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $is_premium = isset($_POST['is_premium']) ? 1 : 0;
    $role = $_POST['role'];
    $new_password = trim($_POST['new_password']);

    // Validate input
    if (empty($username) || empty($email)) {
        $error = "Username and email are required";
    } else {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Check if username or email already exists (for another user)
            $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->bind_param("ssi", $username, $email, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Username or email is already in use by another user";
                $conn->rollback();
            } else {
                // Update the user information
                if (!empty($new_password)) {
                    // Update with new password
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, is_premium = ?, role = ?, password_hash = ? WHERE id = ?");
                    $stmt->bind_param("ssissi", $username, $email, $is_premium, $role, $password_hash, $user_id);
                } else {
                    // Update without changing password
                    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, is_premium = ?, role = ? WHERE id = ?");
                    $stmt->bind_param("ssisi", $username, $email, $is_premium, $role, $user_id);
                }

                if ($stmt->execute()) {
                    $success = "User updated successfully!";
                    $user['username'] = $username;
                    $user['email'] = $email;
                    $user['is_premium'] = $is_premium;
                    $user['role'] = $role;
                    $conn->commit();

                     header("Location: user_list.php");
                } else {
                    $error = "Error updating user. Please try again.";
                    $conn->rollback();
                }
            }

            $stmt->close();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "An error occurred: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit User | Admin | Ricordella</title>
    <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="../assets/style/dashboard.css">
    <link rel="stylesheet" href="../assets/style/admin-users.css">
    <link rel="stylesheet" href="../assets/style/font-general.css">
    <link rel="icon" href="../assets/img/logo-favicon.ico" type="image/x-icon">
</head>
<body>
    <header>
        <div class="logo">Ricordella Admin</div>
        <nav>
            <a href="user_list.php">Users</a>
        </nav>
        <div class="user-info">
            <span>Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="logout">Logout</a>
        </div>
    </header>

    <main>
        <h1>Edit User</h1>

        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="admin-form-container">
            <form method="POST" class="admin-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password (leave blank to keep current)</label>
                    <input type="password" id="new_password" name="new_password">
                </div>

                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" <?php echo $user['role'] === 'admin' && $user['id'] === $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                        <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                    <?php if ($user['role'] === 'admin' && $user['id'] === $_SESSION['user_id']): ?>
                    <input type="hidden" name="role" value="admin">
                    <p class="note">You cannot change your own admin role.</p>
                    <?php endif; ?>
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="is_premium" name="is_premium" <?php echo $user['is_premium'] ? 'checked' : ''; ?>>
                    <label for="is_premium">Premium User</label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn primary">Save Changes</button>
                    <a href="user_list.php" class="btn secondary">Cancel</a>
                </div>
            </form>
        </div>

        <div class="user-stats">
            <h2>User Statistics</h2>
            <p><strong>Notes Count:</strong> <?php echo countUserNotes($user['id']); ?></p>
            <p><strong>Account Created:</strong> <?php echo formatDate($user['created_at']); ?></p>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Ricordella - Admin Panel</p>
    </footer>
</body>
</html>