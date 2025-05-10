<?php
require_once '../config/db.php';
require_once '../utils/functions.php';

// Require admin privileges
requireAdmin();

// Get all users
$users = getAllUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard | Ricordella</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="../assets/style/dashboard.css">
    <link rel="stylesheet" href="../assets/style/admin.css">
    <link rel="stylesheet" href="../assets/style/font-general.css">
    <link rel="icon" href="../assets/img/logo-favicon.ico" type="image/x-icon">
</head>
<body>
    <header>
        <div class="logo">Ricordella Admin</div>
        <nav>
            <a href="dashboard.php" class="active">Users</a>
        </nav>
        <div class="user-info">
            <span>Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="logout">Logout</a>
        </div>
    </header>

    <main>
        <h1>User Management</h1>

        <?php if (isset($_SESSION['admin_message'])): ?>
            <div class="alert <?php echo $_SESSION['admin_message_type']; ?>">
                <?php
                    echo $_SESSION['admin_message'];
                    unset($_SESSION['admin_message']);
                    unset($_SESSION['admin_message_type']);
                ?>
            </div>
        <?php endif; ?>

        <div class="users-table-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Premium</th>
                        <th>Notes Count</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo $user['role']; ?></td>
                            <td><?php echo $user['is_premium'] ? 'Yes' : 'No'; ?></td>
                            <td><?php echo countUserNotes($user['id']); ?></td>
                            <td><?php echo formatDate($user['created_at']); ?></td>
                            <td class="actions">
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn edit">Edit</a>
                                <?php if ($user['role'] !== 'admin'): ?>
                                <a href="toggle_premium.php?id=<?php echo $user['id']; ?>&premium=<?php echo $user['is_premium'] ? '0' : '1'; ?>" class="btn <?php echo $user['is_premium'] ? 'remove' : 'add'; ?>">
                                    <?php echo $user['is_premium'] ? 'Remove Premium' : 'Add Premium'; ?>
                                </a>
                                <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn delete" onclick="return confirm('Are you sure you want to delete this user? All their notes will also be deleted.')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Ricordella - Admin Panel</p>
    </footer>
</body>
</html>