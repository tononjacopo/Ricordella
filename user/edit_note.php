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
    <link rel="stylesheet" href="../assets/style/default-user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/img/logo-favicon.ico" type="image/x-icon">
</head>
<body>
<header>
    <div class="logo">Ricordella</div>
        <nav>
            <a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'class="active"' : ''; ?>>
                My Notes
            </a>
            <a href="daily_notes.php" <?php echo basename($_SERVER['PHP_SELF']) === 'daily_notes.php' ? 'class="active"' : ''; ?>>
                Today's Notes
            </a>
            <a href="shared_notes.php" <?php echo basename($_SERVER['PHP_SELF']) === 'shared_notes.php' ? 'class="active"' : ''; ?>>
                Shared Notes
            </a>
            <a href="create_note.php" <?php echo basename($_SERVER['PHP_SELF']) === 'create_note.php' ? 'class="active"' : ''; ?>>
                Create Note
            </a>
        </nav>
    <div class="user-info">
        <span>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <?php if (isPremium()): ?>
            <span class="premium-badge"><i class="fas fa-crown"></i> Premium</span>
        <?php endif; ?>
        <a href="../logout.php" class="logout" title="Logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
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
                <input type="text" id="title" name="title" class="form-control" required value="<?php echo htmlspecialchars($note['title']); ?>">
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" class="form-control" rows="8" required><?php echo htmlspecialchars($note['content']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="priority">Priority</label>
                <div class="dropdown-select">
                    <select id="priority" name="priority" class="form-control">
                        <option value="Bassa" <?php echo $note['priority'] === 'Bassa' ? 'selected' : ''; ?>>Low</option>
                        <option value="Normale" <?php echo $note['priority'] === 'Normale' ? 'selected' : ''; ?>>Normal</option>
                        <option value="Alta" <?php echo $note['priority'] === 'Alta' ? 'selected' : ''; ?> <?php echo !isPremium() ? 'disabled' : ''; ?> class="<?php echo !isPremium() ? 'premium-feature' : ''; ?>">
                            High
                        </option>
                        <option value="Immediata" <?php echo $note['priority'] === 'Immediata' ? 'selected' : ''; ?> <?php echo !isPremium() ? 'disabled' : ''; ?> class="<?php echo !isPremium() ? 'premium-feature' : ''; ?>">
                            Immediate
                        </option>
                    </select>
                </div>
            </div>

            <?php if (isPremium()): ?>
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="share-option" name="is_shared" <?php echo $note['is_shared'] ? 'checked' : ''; ?>>
                    <label for="share-option">Share this note with other users</label>
                </div>

                <div id="permission-options" style="display:<?php echo $note['is_shared'] ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label>Sharing Management</label>
                        <p class="note">You can manage who this note is shared with from the <a href="manage_shares.php">Manage Sharing</a> page.</p>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="form-group premium-feature">
                <div class="checkbox-group">
                    <input type="checkbox" id="share-option" name="is_shared" disabled <?php echo $note['is_shared'] ? 'checked' : ''; ?>>
                    <label for="share-option">Share this note with other users</label>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn primary">
                    <i class="fas fa-save"></i> Update Note
                </button>
                <a href="dashboard.php" class="btn secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>

        <?php if (!isPremium() && ($note['priority'] === 'Alta' || $note['priority'] === 'Immediata' || $note['is_shared'])): ?>
        <div class="alert info" style="margin-top: 20px;">
            <i class="fas fa-info-circle"></i> This note uses premium features. Upgrade to Premium to modify these settings!
        </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Ricordella - Your Notes App</p>
    </footer>
</body>
</html>