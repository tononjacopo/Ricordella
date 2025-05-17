<?php
require_once '../config/db.php';
require_once '../utils/functions.php';

// Require user to be logged in
requireLogin();

$error = '';
$success = '';

// Check if note ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: user.php");
    exit;
}

$note_id = (int)$_GET['id'];

// Get the note
$note = getNoteById($note_id);

// Check if the note exists and belongs to the user
if (!$note || $note['user_id'] != $_SESSION['user_id']) {
    header("Location: user.php");
    exit;
}

// Get users with whom this note is shared
$sharedUsers = [];
if (isPremium()) {
    $sharedUsers = getNoteSharedUsers($note_id);
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
    <link rel="stylesheet" href="../assets/style/user.css">
    <link rel="stylesheet" href="../assets/style/default-user.css">
    <link rel="stylesheet" href="../assets/style/font-general.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/img/logo-favicon.ico" type="image/x-icon">
    <style>
        /* Sharing styles */
        .sharing-container {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background-color: var(--surface);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .sharing-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .sharing-header h3 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-primary);
        }

        .share-icon {
            color: #4285F4; /* Google Drive blue */
        }

        .search-section {
            display: none;
            margin-bottom: 1.5rem;
        }

        .search-section.active {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }

        .search-input-wrapper {
            position: relative;
            margin-bottom: 0.5rem;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
        }

        .search-icon-wrapper {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        .search-results {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            max-height: 200px;
            overflow-y: auto;
            display: none;
        }

        .search-results.active {
            display: block;
            animation: fadeIn 0.2s ease-out;
        }

        .user-result {
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .user-result:last-child {
            border-bottom: none;
        }

        .user-result:hover {
            background-color: rgba(66, 133, 244, 0.05);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background-color: #E8EAF6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3949AB;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 500;
            color: var(--text-primary);
        }

        .user-email {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .add-user-btn {
            padding: 0.25rem 0.5rem;
            background-color: rgba(66, 133, 244, 0.1);
            color: #4285F4;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .add-user-btn:hover {
            background-color: rgba(66, 133, 244, 0.2);
        }

        .permission-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .permission-toggle select {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            border: 1px solid var(--border-color);
            font-size: 0.875rem;
            background-color: white;
        }

        .shared-users-list {
            margin-top: 1rem;
        }

        .shared-user {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .shared-user:last-child {
            border-bottom: none;
        }

        .shared-user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .shared-user-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .share-toggle-btn {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #4285F4;
            font-weight: 500;
            cursor: pointer;
            padding: 0.5rem 0.75rem;
            border-radius: 0.25rem;
            transition: background-color 0.2s ease;
        }

        .share-toggle-btn:hover {
            background-color: rgba(66, 133, 244, 0.05);
        }

        .remove-share-btn {
            color: var(--danger);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.25rem;
            transition: background-color 0.2s ease;
        }

        .remove-share-btn:hover {
            background-color: rgba(244, 67, 54, 0.05);
        }

        .no-shared-users {
            padding: 1rem;
            text-align: center;
            color: var(--text-secondary);
            font-style: italic;
        }

        .premium-badge-small {
            font-size: 0.75rem;
            display: inline-flex;
            margin-left: 0.5rem;
            vertical-align: middle;
        }

        .premium-badge-small i {
            font-size: 0.75rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Ricordella</div>
        <nav>
            <a href="user.php">My Notes</a>
            <a href="daily_notes.php">Today's Notes</a>
            <a href="shared_notes.php">Shared Notes</a>
            <a href="create_note.php">Create Note</a>
        </nav>
        <div class="user-info">
            <span>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <?php if (isPremium()): ?>
            <span class="premium-badge"><i class="fas fa-crown"></i> Premium</span>
            <?php endif; ?>
            <a href="../logout.php" class="logout" title="Logout">
                <i class="fas fa-sign-out-alt">Logout</i>
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
                    <label for="share-option">Enable sharing for this note</label>
                </div>
            </div>
            <?php else: ?>
            <div class="form-group premium-feature">
                <div class="checkbox-group">
                    <input type="checkbox" id="share-option" name="is_shared" disabled <?php echo $note['is_shared'] ? 'checked' : ''; ?>>
                    <label for="share-option">Enable sharing for this note</label>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn primary">
                    <i class="fas fa-save"></i> Update Note
                </button>
                <a href="user.php" class="btn secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>

        <?php if (isPremium()): ?>
        <div class="sharing-container">
            <div class="sharing-header">
                <h3><i class="fas fa-share-alt share-icon"></i> Share this note</h3>
                <button type="button" id="toggle-share-search" class="share-toggle-btn">
                    <i class="fas fa-plus"></i> Add people
                </button>
            </div>

            <div class="search-section" id="search-section">
                <div class="search-input-wrapper">
                    <div class="search-icon-wrapper">
                        <i class="fas fa-search"></i>
                    </div>
                    <input type="text" id="user-search" class="search-input" placeholder="Search users by email">
                </div>
                <div class="search-results" id="search-results"></div>
            </div>

            <div class="shared-users-list" id="shared-users-list">
                <?php if (empty($sharedUsers)): ?>
                    <div class="no-shared-users">This note isn't shared with anyone</div>
                <?php else: ?>
                    <?php foreach ($sharedUsers as $user): ?>
                        <div class="shared-user" data-user-id="<?php echo $user['id']; ?>">
                            <div class="shared-user-info">
                                <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
                                <div class="user-details">
                                    <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                                    <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                </div>
                            </div>
                            <div class="shared-user-controls">
                                <div class="permission-toggle">
                                    <select class="permission-select" data-user-id="<?php echo $user['id']; ?>">
                                        <option value="view" <?php echo $user['permission'] === 'view' ? 'selected' : ''; ?>>Can view</option>
                                        <option value="edit" <?php echo $user['permission'] === 'edit' ? 'selected' : ''; ?>>Can edit</option>
                                    </select>
                                </div>
                                <button type="button" class="remove-share-btn" data-user-id="<?php echo $user['id']; ?>" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php elseif ($note['is_shared']): ?>
        <div class="alert info" style="margin-top: 20px;">
            <i class="fas fa-info-circle"></i> This note is currently shared. Upgrade to Premium to manage sharing settings.
        </div>
        <?php endif; ?>

        <?php if (!isPremium() && ($note['priority'] === 'Alta' || $note['priority'] === 'Immediata' || $note['is_shared'])): ?>
        <div class="alert info" style="margin-top: 20px;">
            <i class="fas fa-info-circle"></i> This note uses premium features. Upgrade to Premium to modify these settings!
        </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Ricordella - Your Notes App</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const noteId = <?php echo $note_id; ?>;
            const shareOption = document.getElementById('share-option');
            const toggleShareSearchBtn = document.getElementById('toggle-share-search');
            const searchSection = document.getElementById('search-section');
            const userSearchInput = document.getElementById('user-search');
            const searchResults = document.getElementById('search-results');
            const sharedUsersList = document.getElementById('shared-users-list');

            // Share option toggle
            if (shareOption) {
                shareOption.addEventListener('change', function() {
                    toggleShareSearchBtn.style.display = this.checked ? 'flex' : 'none';

                    if (!this.checked) {
                        searchSection.classList.remove('active');
                    }
                });

                // Initialize
                toggleShareSearchBtn.style.display = shareOption.checked ? 'flex' : 'none';
            }

            // Toggle search section
            if (toggleShareSearchBtn) {
                toggleShareSearchBtn.addEventListener('click', function() {
                    searchSection.classList.toggle('active');
                    if (searchSection.classList.contains('active')) {
                        userSearchInput.focus();
                    }
                });
            }

            // User search functionality
            let searchTimeout;
            if (userSearchInput) {
                userSearchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    const query = this.value.trim();

                    if (query.length < 2) {
                        searchResults.classList.remove('active');
                        searchResults.innerHTML = '';
                        return;
                    }

                    //User search functionality timeout
                    searchTimeout = setTimeout(() => {
                        fetch(`../utils/search_users.php?email=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => {
                                searchResults.innerHTML = '';

                                if (data.length === 0) {
                                    searchResults.innerHTML = '<div class="user-result">No users found</div>';
                                    searchResults.classList.add('active');
                                    return;
                                }

                                data.forEach(user => {
                                    const userElement = document.createElement('div');
                                    userElement.className = 'user-result';
                                    userElement.dataset.id = user.id;
                                    userElement.dataset.username = user.username;
                                    userElement.dataset.email = user.email;

                                    const firstLetter = user.username.charAt(0).toUpperCase();

                                    userElement.innerHTML = `
                                        <div class="user-info">
                                            <div class="user-avatar">${firstLetter}</div>
                                            <div class="user-details">
                                                <div class="user-name">
                                                    ${user.username}
                                                    ${parseInt(user.is_premium) === 1 ?
                                                    '<span class="premium-badge-small"><i class="fas fa-crown" style="color:gold;"></i></span>' : ''}
                                                </div>
                                                <div class="user-email">${user.email}</div>
                                            </div>
                                        </div>
                                        <button type="button" class="add-user-btn">Add</button>
                                    `;

                                    searchResults.appendChild(userElement);
                                });

                                searchResults.classList.add('active');
                            })
                            .catch(error => {
                                console.error('Error searching users:', error);
                                searchResults.innerHTML = '<div class="user-result">Error searching users</div>';
                                searchResults.classList.add('active');
                            });
                    }, 300);
                });
            }

            // Handle add user click
            searchResults.addEventListener('click', function(e) {
                const addUserBtn = e.target.closest('.add-user-btn');
                if (addUserBtn) {
                    const userResult = addUserBtn.closest('.user-result');
                    const userId = userResult.dataset.id;
                    const username = userResult.dataset.username;
                    const email = userResult.dataset.email;
                    const firstLetter = username.charAt(0).toUpperCase();


                    // Share the note with this user
                    shareNoteWithUser(noteId, userId, username, email, firstLetter, 'view');

                    // Clear search
                    userSearchInput.value = '';
                    searchResults.classList.remove('active');
                    searchSection.classList.remove('active');
                }
            });

            // Permission change handler
            document.addEventListener('change', function(e) {
                const permissionSelect = e.target.closest('.permission-select');
                if (permissionSelect) {
                    const userId = permissionSelect.dataset.userId;
                    const permission = permissionSelect.value;

                    updatePermission(noteId, userId, permission);
                }
            });

            // Remove share handler
            document.addEventListener('click', function(e) {
                const removeBtn = e.target.closest('.remove-share-btn');
                if (removeBtn) {
                    const userId = removeBtn.dataset.userId;
                    removeShare(noteId, userId);
                }
            });

            // Function to share a note with a user
            function shareNoteWithUser(noteId, userId, username, email, firstLetter, permission) {
                fetch('../utils/share_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'share', // Add action parameter
                        note_id: noteId,
                        user_id: userId,
                        permission: permission
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateSharedUsersList(data.shared_users);
                    } else {
                        console.error('Error sharing note:', data.error);
                        alert('Error sharing note: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error sharing note:', error);
                    alert('Error sharing note. Please try again.');
                });
            }

            // Function to update permission
            function updatePermission(noteId, userId, permission) {
                fetch('../utils/share_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'share', // Use share action for updating permission
                        note_id: noteId,
                        user_id: userId,
                        permission: permission
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Error updating permission:', data.error);
                        alert('Error updating permission: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error updating permission:', error);
                    alert('Error updating permission. Please try again.');
                });
            }

            // Function to remove share
            function removeShare(noteId, userId) {
                if (confirm('Are you sure you want to remove this user from sharing?')) {
                    fetch('../utils/share_action.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'unshare', // Use unshare action
                            note_id: noteId,
                            user_id: userId
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateSharedUsersList(data.shared_users);
                        } else {
                            console.error('Error removing share:', data.error);
                            alert('Error removing share: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error removing share:', error);
                        alert('Error removing share. Please try again.');
                    });
                }
            }

            // Function to update shared users list
            function updateSharedUsersList(users) {
                sharedUsersList.innerHTML = '';

                if (!users || users.length === 0) {
                    sharedUsersList.innerHTML = '<div class="no-shared-users">This note isn\'t shared with anyone</div>';
                    return;
                }

                users.forEach(user => {
                    const firstLetter = user.username.charAt(0).toUpperCase();

                    const userElement = document.createElement('div');
                    userElement.className = 'shared-user';
                    userElement.dataset.userId = user.id;

                    userElement.innerHTML = `
                        <div class="shared-user-info">
                            <div class="user-avatar">${firstLetter}</div>
                            <div class="user-details">
                                <div class="user-name">${user.username}</div>
                                <div class="user-email">${user.email}</div>
                            </div>
                        </div>
                        <div class="shared-user-controls">
                            <div class="permission-toggle">
                                <select class="permission-select" data-user-id="${user.id}">
                                    <option value="view" ${user.permission === 'view' ? 'selected' : ''}>Can view</option>
                                    <option value="edit" ${user.permission === 'edit' ? 'selected' : ''}>Can edit</option>
                                </select>
                            </div>
                            <button type="button" class="remove-share-btn" data-user-id="${user.id}" title="Remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;

                    sharedUsersList.appendChild(userElement);
                });
            }
        });
    </script>
</body>
</html>