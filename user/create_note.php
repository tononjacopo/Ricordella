<?php
require_once '../config/db.php';
require_once '../utils/functions.php';


// Require user to be logged in
requireLogin();

$error = '';
$success = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $priority = $_POST['priority'];
    $is_shared = isset($_POST['is_shared']) ? 1 : 0;

    // Premium features check
    if (!isPremium() && $priority === 'Alta') {
        $error = "High priority is a premium feature. Please upgrade your account.";
    } elseif (!isPremium() && $priority === 'Immediata') {
        $error = "Immediate priority is a premium feature. Please upgrade your account.";
    } elseif (!isPremium() && $is_shared) {
        $error = "Note sharing is a premium feature. Please upgrade your account.";
    } else {
        // Validate input
        if (empty($title) || empty($content)) {
            $error = "Title and content are required";
        } else {
            // Insert the note
            $stmt = $conn->prepare("INSERT INTO notes (user_id, title, content, priority, is_shared) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isssi", $_SESSION['user_id'], $title, $content, $priority, $is_shared);

            if ($stmt->execute()) {
                $success = "Note created successfully!";
                // Clear form data on success
                $title = $content = '';
                $priority = 'Normale';
                $is_shared = 0;
            } else {
                $error = "Error creating note. Please try again.";
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Create Note | Ricordella</title>
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
            <a href="daily_notes.php">Today's Notes</a>
            <a href="shared_notes.php">Shared Notes</a>
            <?php if (isPremium()): ?>
            <a href="create_note.php" class="premium active">Create Note</a>
            <?php else: ?>
            <a href="create_note.php" class="active">Create Note</a>
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
        <h1>Create New Note</h1>

        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" class="note-form">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" rows="6" required><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    <option value="Bassa" <?php echo (isset($priority) && $priority === 'Bassa') ? 'selected' : ''; ?>>Low</option>
                    <option value="Normale" <?php echo (!isset($priority) || $priority === 'Normale') ? 'selected' : ''; ?>>Normal</option>
                    <option value="Alta" <?php echo (isset($priority) && $priority === 'Alta') ? 'selected' : ''; ?> <?php echo !isPremium() ? 'disabled' : ''; ?>>High <?php echo !isPremium() ? '(Premium)' : ''; ?></option>
                    <option value="Immediata" <?php echo (isset($priority) && $priority === 'Immediata') ? 'selected' : ''; ?> <?php echo !isPremium() ? 'disabled' : ''; ?>>Immediate <?php echo !isPremium() ? '(Premium)' : ''; ?></option>
                </select>
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" id="is_shared" name="is_shared" <?php echo (isset($is_shared) && $is_shared) ? 'checked' : ''; ?> <?php echo !isPremium() ? 'disabled' : ''; ?>>
                <label for="is_shared">Share this note with other users <?php echo !isPremium() ? '(Premium feature)' : ''; ?></label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Create Note</button>
                <a href="dashboard.php" class="btn secondary">Cancel</a>
            </div>
        </form>

        <?php if (!isPremium()): ?>
        <div class="premium-notice">
            <p>Upgrade to Premium to unlock high priority notes and sharing features!</p>
        </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Ricordella - Your Notes App</p>
    </footer>
</body>
</html>