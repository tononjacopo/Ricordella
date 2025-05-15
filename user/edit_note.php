<?php
require_once '../config/db.php';
require_once '../utils/functions.php';


// Require user to be logged in
requireLogin();

$error = '';
$success = '';

// Check if note ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: user_list.php");
    exit;
}

$note_id = (int)$_GET['id'];

// Get the note
$note = getNoteById($note_id);

// Check if the note exists and belongs to the user
if (!$note || $note['user_id'] != $_SESSION['user_id']) {
    header("Location: user_list.php");
    exit;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $priority = $_POST['priority'];
    $is_shared = isset($_POST['is_shared']) ? 1 : 0;

    // Premium features check
    if (!isPremium() && ($priority === 'Alta' || $priority === 'Immediata')) {
        $error = "High priority is a premium feature. Please upgrade your account.";
    } elseif (!isPremium() && $is_shared) {
        $error = "Note sharing is a premium feature. Please upgrade your account.";
    } else {
        // Validate input
        if (empty($title) || empty($content)) {
            $error = "Title and content are required";
        } else {
            // Update the note
            $stmt = $conn->prepare("UPDATE notes SET title = ?, content = ?, priority = ?, is_shared = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sssiii", $title, $content, $priority, $is_shared, $note_id, $_SESSION['user_id']);

            if ($stmt->execute()) {
                $success = "Note updated successfully!";
                // Update note data
                $note['title'] = $title;
                $note['content'] = $content;
                $note['priority'] = $priority;
                $note['is_shared'] = $is_shared;
            } else {
                $error = "Error updating note. Please try again.";
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Note | Ricordella</title>
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
        <h1>Edit Note</h1>

        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" class="note-form">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($note['title']); ?>">
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" rows="6" required><?php echo htmlspecialchars($note['content']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    <option value="Bassa" <?php echo $note['priority'] === 'Bassa' ? 'selected' : ''; ?>>Low</option>
                    <option value="Normale" <?php echo $note['priority'] === 'Normale' ? 'selected' : ''; ?>>Normal</option>
                    <option value="Alta" <?php echo $note['priority'] === 'Alta' ? 'selected' : ''; ?> <?php echo !isPremium() ? 'disabled' : ''; ?>>High <?php echo !isPremium() ? '(Premium)' : ''; ?></option>
                    <option value="Immediata" <?php echo $note['priority'] === 'Immediata' ? 'selected' : ''; ?> <?php echo !isPremium() ? 'disabled' : ''; ?>>Immediate <?php echo !isPremium() ? '(Premium)' : ''; ?></option>
                </select>
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" id="is_shared" name="is_shared" <?php echo $note['is_shared'] ? 'checked' : ''; ?> <?php echo !isPremium() ? 'disabled' : ''; ?>>
                <label for="is_shared">Share this note with other users <?php echo !isPremium() ? '(Premium feature)' : ''; ?></label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Update Note</button>
                <a href="dashboard.php" class="btn secondary">Cancel</a>
            </div>
        </form>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Ricordella - Your Notes App</p>
    </footer>
</body>
</html>