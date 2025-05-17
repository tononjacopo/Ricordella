<?php
require_once '../config/db.php';
require_once 'functions.php';

// Require user to be logged in
requireLogin();

$error = '';
$success = '';

// Check if note ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../user/shared_notes.php");
    exit;
}

$note_id = (int)$_GET['id'];

// Check if the note exists and user has edit permission
$sql = "SELECT n.*, ns.permission, u.username as owner_username
        FROM notes n 
        JOIN note_shares ns ON n.id = ns.note_id
        JOIN users u ON n.user_id = u.id
        WHERE n.id = ? AND ns.user_id = ? AND ns.permission = 'edit'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $note_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$note = $result->fetch_assoc();
$stmt->close();

// If note doesn't exist or user doesn't have edit permission
if (!$note) {
    header("Location: ../user/shared_notes.php");
    exit;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    // Validate input
    if (empty($title) || empty($content)) {
        $error = "Title and content are required";
    } else {
        // We don't allow changing the priority or sharing settings for shared notes
        // Only title and content can be modified
        $stmt = $conn->prepare("UPDATE notes SET title = ?, content = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $content, $note_id);

        if ($stmt->execute()) {
            $success = "Note updated successfully!";
            // Update note data
            $note['title'] = $title;
            $note['content'] = $content;
        } else {
            $error = "Error updating note. Please try again.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Shared Note | Ricordella</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="../assets/style/user.css">
    <link rel="stylesheet" href="../assets/style/default-user.css">
    <link rel="stylesheet" href="../assets/style/font-general.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/img/logo-favicon.ico" type="image/x-icon">
</head>
<body>
    <style>
        .read-only-field {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            background-color: #f5f5f5;
        }

        .priority {
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .priority-bassa {
            background-color: rgba(33, 150, 243, 0.1);
            color: #2196f3; /* Light blue for Low */
        }

        .priority-normale {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4CAF50; /* Green for Normal */
        }

        .priority-alta {
            background-color: rgba(255, 152, 0, 0.1);
            color: #FF9800; /* Orange for High */
        }

        .priority-immediata {
            background-color: rgba(244, 67, 54, 0.1);
            color: #F44336; /* Red for Immediate */
        }
    </style>
    <header>
        <div class="logo">Ricordella</div>
        <nav>
            <a href="../user/user.php">My Notes</a>
            <a href="../user/daily_notes.php">Today's Notes</a>
            <a href="../user/shared_notes.php">Shared Notes</a>
            <a href="../user/create_note.php">Create Note</a>
        </nav>
        <div class="user-info">
            <span>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <?php if (isPremium()): ?>
            <span class="premium-badge"><i class="fas fa-crown"></i> Premium</span>
            <?php endif; ?>
            <a href="../logout.php" class="logout" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </header>

    <main>
        <h1>Edit Shared Note</h1>

        <div class="alert info">
            <i class="fas fa-info-circle"></i> You are editing a note shared by <?php echo htmlspecialchars($note['owner_username']); ?>
        </div>

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
                <label>Priority (cannot be changed)</label>
                <div class="read-only-field">
                    <?php
                    $priorityLabels = [
                        'Bassa' => 'Low',
                        'Normale' => 'Normal',
                        'Alta' => 'High',
                        'Immediata' => 'Immediate'
                    ];
                    ?>
                    <span class="priority priority-<?php echo strtolower($note['priority']); ?>">
                        <?php echo $priorityLabels[$note['priority']] ?? $note['priority']; ?>
                    </span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">
                    <i class="fas fa-save"></i> Update Note
                </button>
                <a href="../user/shared_notes.php" class="btn secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Ricordella - Your Notes App</p>
    </footer>


</body>
</html>