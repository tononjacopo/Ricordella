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
    <link rel="stylesheet" href="../assets/style/user.css">
    <link rel="stylesheet" href="../assets/style/font-general.css">
    <link rel="stylesheet" href="../assets/style/default-user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/img/logo-favicon.ico" type="image/x-icon">
</head>
<body>
<header>
    <div class="logo">Ricordella</div>
        <nav>
            <a href="user.php" <?php echo basename($_SERVER['PHP_SELF']) === 'user.php' ? 'class="active"' : ''; ?>>
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
            <input type="text" id="title" name="title" class="form-control" required value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" class="form-control" rows="8" required><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
        </div>

        <div class="form-group">
            <label for="priority">Priority</label>
            <div class="dropdown-select">
                <select id="priority" name="priority" class="form-control">
                    <option value="Bassa" <?php echo (isset($priority) && $priority === 'Bassa') ? 'selected' : ''; ?>>Low</option>
                    <option value="Normale" <?php echo (!isset($priority) || $priority === 'Normale') ? 'selected' : ''; ?>>Normal</option>
                    <option value="Alta" <?php echo (isset($priority) && $priority === 'Alta') ? 'selected' : ''; ?> <?php echo !isPremium() ? 'disabled' : ''; ?> class="<?php echo !isPremium() ? 'premium-feature' : ''; ?>">
                        High
                    </option>
                    <option value="Immediata" <?php echo (isset($priority) && $priority === 'Immediata') ? 'selected' : ''; ?> <?php echo !isPremium() ? 'disabled' : ''; ?> class="<?php echo !isPremium() ? 'premium-feature' : ''; ?>">
                        Immediate
                    </option>
                </select>
            </div>
        </div>

        <?php if (isPremium()): ?>
        <div class="form-group">
            <div class="checkbox-group">
                <input type="checkbox" id="share-option" name="is_shared" <?php echo (isset($is_shared) && $is_shared) ? 'checked' : ''; ?>>
                <label for="share-option">Share this note with other users</label>
            </div>

            <div id="permission-options" style="display:none;">
                <div class="form-group">
                    <label for="permission">Permission level</label>
                    <div class="dropdown-select">
                        <select id="permission" name="permission" class="form-control">
                            <option value="view">View only</option>
                            <option value="edit">Allow editing</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="share_with">Share with (username)</label>
                    <input type="text" id="share_with" name="share_with" class="form-control" placeholder="Enter username to share with">
                    <p class="note">You can manage sharing settings later from the "Manage Sharing" page.</p>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="form-group premium-feature">
            <div class="checkbox-group">
                <input type="checkbox" id="share-option" name="is_shared" disabled>
                <label for="share-option">Share this note with other users</label>
            </div>
        </div>
        <?php endif; ?>

        <div class="form-actions">
            <button type="submit" class="btn primary">
                <i class="fas fa-save"></i> Create Note
            </button>
            <a href="user.php" class="btn secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>

    <?php if (!isPremium()): ?>
    <div class="alert info" style="margin-top: 20px;">
        <i class="fas fa-info-circle"></i> Upgrade to Premium to unlock high priority notes and sharing features!
    </div>
    <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Ricordella - Your Notes App</p>
    </footer>
</body>
</html>