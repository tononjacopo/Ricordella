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
        <h1>Today's Notes</h1>

        <div class="today-date">
            <p>Today: <?php echo date('l, d F Y'); ?></p>
        </div>

        <?php if (empty($notes)): ?>
            <div class="no-notes">
                <p>You don't have any notes for today.</p>
                <p><a href="create_note.php">Create a new note!</a></p>
            </div>
        <?php else: ?>
            <div class="notes-grid">
                <?php foreach ($notes as $note): ?>
                    <div class="note priority-<?php echo strtolower($note['priority']); ?>">
                        <div class="priority-indicator"></div>
                        <div class="note-header">
                            <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                            <div class="note-actions">
                                <a href="edit_note.php?id=<?php echo $note['id']; ?>" class="action-btn edit-btn" title="Edit Note">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="action-btn delete-note-btn" title="Delete Note" data-id="<?php echo $note['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <div class="note-content">
                            <p data-full-content="<?php echo htmlspecialchars($note['content']); ?>"><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
                        </div>
                        <div class="note-footer">
                            <span class="priority">
                                <?php
                                    $priorityLabels = [
                                        'Bassa' => 'Low',
                                        'Normale' => 'Normal',
                                        'Alta' => 'High',
                                        'Immediata' => 'Immediate'
                                    ];
                                    echo $priorityLabels[$note['priority']] ?? $note['priority'];
                                ?>
                            </span>
                            <?php if ($note['is_shared']): ?>
                                <span class="shared-badge">
                                    <i class="fas fa-share-alt"></i> Shared
                                </span>
                            <?php else: ?>
                                <span class="shared-badge" style="visibility: hidden;"></span>
                            <?php endif; ?>
                            <span class="date"><?php echo formatDate($note['created_at']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Ricordella - Your Notes App</p>
    </footer>

    <div id="confirm-popup">
        <p>Delete this Note?</p>
        <div>
            <button id="confirm-yes">Yes</button>
            <button id="confirm-no">No</button>
        </div>
    </div>

    <script src="../assets/script/note-actions.js"></script>
</body>
</html>