<?php
require_once '../config/db.php';
require_once '../utils/functions.php';


// Require user to be logged in
requireLogin();

// Get today's notes
$notes = getTodaysNotes($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Today's Notes | Ricordella</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="../assets/style/dashboard.css">
    <link rel="stylesheet" href="../assets/style/font-general.css">
    <link rel="icon" href="../assets/img/logo-favicon.ico" type="image/x-icon">
</head>
<body>
    <header>
        <div class="logo">Ricordella</div>
        <nav>
            <a href="dashboard.php">My Notes</a>
            <a href="daily_notes.php" class="active">Today's Notes</a>
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
        <h1>Today's Notes</h1>

        <div class="today-date">
            <p>Today: <?php echo date('l, d F Y'); ?></p>
        </div>

        <?php if (empty($notes)): ?>
            <div class="no-notes">
                <p>You don't have any notes for today. <a href="create_note.php">Create a new note!</a></p>
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