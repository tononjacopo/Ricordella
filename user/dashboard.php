<?php
require_once '../config/db.php';
require_once '../utils/functions.php';

// Require user to be logged in
requireLogin();

// Redirect admin to admin dashboard
if (isAdmin()) {
    header("Location: ../admin/user_list.php");
    exit;
}

// Get user's notes, ordered by creation date by default
$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : "created_at DESC";

// Sanitize the orderBy parameter to prevent SQL injection
$allowedOrderBy = [
    "created_at DESC",
    "created_at ASC",
    "priority DESC",
    "priority ASC"
];

if (!in_array($orderBy, $allowedOrderBy)) {
    $orderBy = "created_at DESC"; // Default if invalid
}

$notes = getNotesByUserId($_SESSION['user_id'], $orderBy);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard | Ricordella</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="../assets/style/dashboard.css">
    <link rel="stylesheet" href="../assets/style/font-general.css">
        <link rel="stylesheet" href="../assets/style/default-user.css">
    <link rel="icon" href="../assets/img/logo-favicon.ico" type="image/x-icon">
</head>
<body>
    <header>
        <div class="logo">Ricordella</div>
        <nav>
            <a href="dashboard.php" class="active">My Notes</a>
            <a href="daily_notes.php">Today's Notes</a>
            <a href="shared_notes.php">Shared Notes</a>
            <?php if (isPremium()): ?>
            <a href="create_note.php" class="premium">Create Note</a>
            <?php else: ?>
            <a href="create_note.php">Create Note</a>
            <?php endif; ?>
        </nav>
        <div class="user-info">
            <span>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <?php if (isPremium()): ?>
            <span class="premium-badge">⭐ Premium</span>
            <?php endif; ?>
            <a href="../logout.php" class="logout">Logout</a>
        </div>
    </header>

    <main>
        <h1>My Notes</h1>

        <div class="sort-options">
            <span>Sort by:</span>
            <a href="?orderBy=created_at DESC" <?php echo $orderBy === "created_at DESC" ? 'class="active"' : ''; ?>>Newest</a>
            <a href="?orderBy=created_at ASC" <?php echo $orderBy === "created_at ASC" ? 'class="active"' : ''; ?>>Oldest</a>
            <a href="?orderBy=priority DESC" <?php echo $orderBy === "priority DESC" ? 'class="active"' : ''; ?>>Priority (High to Low)</a>
            <a href="?orderBy=priority ASC" <?php echo $orderBy === "priority ASC" ? 'class="active"' : ''; ?>>Priority (Low to High)</a>
        </div>

        <?php if (empty($notes)): ?>
            <div class="no-notes">
                <p>You don't have any notes yet. <a href="create_note.php">Create your first note!</a></p>
            </div>
        <?php else: ?>
            <div class="notes-grid">
                <?php foreach ($notes as $note): ?>
                    <div class="note priority-<?php echo strtolower($note['priority']); ?>">
                        <div class="note-header">
                            <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                            <div class="note-actions">
                                <a href="edit_note.php?id=<?php echo $note['id']; ?>" class="edit-btn">Edit</a>
                                <a href="delete_note.php?id=<?php echo $note['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this note?')">Delete</a>
                            </div>
                        </div>
                        <div class="note-content">
                            <p><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
                        </div>
                        <div class="note-footer">
                            <span class="priority"><?php echo $note['priority']; ?></span>
                            <span class="date"><?php echo formatDate($note['created_at']); ?></span>
                            <?php if ($note['is_shared']): ?>
                                <span class="shared-badge">Shared</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Ricordella - Your Notes App</p>
    </footer>
</body>
</html>