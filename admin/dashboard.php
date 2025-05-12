<?php
require_once '../config/db.php';
require_once '../utils/functions.php';

requireAdmin();

// Filtri ricerca
$filters = [
    'id' => $_GET['id'] ?? '',
    'username' => $_GET['username'] ?? '',
    'email' => $_GET['email'] ?? '',
    'is_premium' => $_GET['is_premium'] ?? ''
];

// Ordinamento (solo su role, premium, notes_count, created_at)
$sort_columns = ['role', 'is_premium', 'notes_count', 'created_at'];
$sort_column = $_GET['sort'] ?? 'created_at';
if (!in_array($sort_column, $sort_columns)) $sort_column = 'created_at';

$sort_order = $_GET['order'] ?? 'desc';
if (!in_array($sort_order, ['asc', 'desc'])) $sort_order = 'desc';

// Ricerca e ordinamento utenti
$users = getFilteredUsers($filters, $sort_column, $sort_order); // Implementa questa funzione in functions.php
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" href="../assets/img/logo-favicon.ico" type="image/x-icon">

</head>
<body>
    <header>
        <div class="logo">Ricordella Admin</div>
        <nav>
            <a href="dashboard.php" class="active">Users</a>
            <a href="logs.php">Logs</a>
            <a href="stats.php">Stats</a>
        </nav>
        <div class="user-info">
            <span>Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="logout">Logout</a>
        </div>
    </header>

    <main>
        <h1>User Management</h1>
        <!-- Ricerca avanzata -->
        <form class="search-bar-admin" method="get">
            <div class="search-fields">
                <div>
                    <label for="search_id">ID</label>
                     <div class="input-clearable">
                         <input type="text" name="id" id="search_id" value="<?php echo htmlspecialchars($filters['id']); ?>" />
                         <button type="button" class="clear-btn" onclick="clearInput(this)">×</button>
                     </div>
                </div>
                <div>
                    <label for="search_username">Username</label>
                    <div class="input-clearable">
                        <input type="text" name="username" id="search_username" value="<?php echo htmlspecialchars($filters['username']); ?>" />
                        <button type="button" class="clear-btn" onclick="clearInput(this)">×</button>
                    </div>
                </div>
                <div>
                    <label for="search_email">Email</label>
                    <div class="input-clearable">
                        <input type="text" name="email" id="search_email" value="<?php echo htmlspecialchars($filters['email']); ?>" />
                        <button type="button" class="clear-btn" onclick="clearInput(this)">×</button>
                    </div>
                </div>
                <div>
                    <label for="search_premium">Premium</label>
                    <div class="input-clearable">
                        <select name="is_premium" id="search_premium">
                            <option value="">All</option>
                            <option value="1" <?php if($filters['is_premium'] === '1') echo 'selected'; ?>>Yes</option>
                            <option value="0" <?php if($filters['is_premium'] === '0') echo 'selected'; ?>>No</option>
                        </select>
                     </div>
                </div>
            </div>
            <div class="search-actions">
                <button type="submit" class="btn-search"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                <button type="button" class="refresh-btn" id="refreshTable" title="Refresh">
                    <i class="fa-solid fa-rotate"></i>
                </button>
            </div>
        </form>

        <div class="users-table-container" style="position:relative;">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>
                          <a href="?<?php echo http_build_query(array_merge($_GET, ['sort'=>'role','order'=>($sort_column=='role' && $sort_order=='desc'?'asc':'desc')])); ?>">
                            Role
                            <!-- NUOVA FRECCIA DI ORDINAMENTO -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="lucide lucide-chevrons-up-down <?php echo $sort_column=='role' ? 'active' : ''; ?>">
                                <path d="m7 15 5 5 5-5"></path>
                                <path d="m7 9 5-5 5 5"></path>
                            </svg>
                          </a>
                        </th>
                        <th>
                          <a href="?<?php echo http_build_query(array_merge($_GET, ['sort'=>'is_premium','order'=>($sort_column=='is_premium'&&$sort_order=='desc'?'asc':'desc')])); ?>">
                            Premium
                            <!-- NUOVA FRECCIA DI ORDINAMENTO -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="lucide lucide-chevrons-up-down <?php echo $sort_column=='is_premium' ? 'active' : ''; ?>">
                                <path d="m7 15 5 5 5-5"></path>
                                <path d="m7 9 5-5 5 5"></path>
                            </svg>
                          </a>
                        </th>
                        <th>
                          <a href="?<?php echo http_build_query(array_merge($_GET, ['sort'=>'notes_count','order'=>($sort_column=='notes_count'&&$sort_order=='desc'?'asc':'desc')])); ?>">
                            Notes
                            <!-- NUOVA FRECCIA DI ORDINAMENTO -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="lucide lucide-chevrons-up-down <?php echo $sort_column=='notes_count' ? 'active' : ''; ?>">
                                <path d="m7 15 5 5 5-5"></path>
                                <path d="m7 9 5-5 5 5"></path>
                            </svg>
                          </a>
                        </th>
                        <th>
                          <a href="?<?php echo http_build_query(array_merge($_GET, ['sort'=>'created_at','order'=>($sort_column=='created_at'&&$sort_order=='desc'?'asc':'desc')])); ?>">
                            Created
                            <!-- NUOVA FRECCIA DI ORDINAMENTO -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="lucide lucide-chevrons-up-down <?php echo $sort_column=='created_at' ? 'active' : ''; ?>">
                                <path d="m7 15 5 5 5-5"></path>
                                <path d="m7 9 5-5 5 5"></path>
                            </svg>
                          </a>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="role-admin">admin</span>
                                <?php else: ?>
                                    <?php echo $user['role']; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($user['is_premium']): ?>
                                    <i class="fa-solid fa-crown crown-premium"></i>
                                <?php else: ?>
                                    <i class="fa-solid fa-crown crown-non-premium"></i>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $user['notes_count']; ?></td>
                            <td><?php echo formatDate($user['created_at']); ?></td>
                            <td class="actions">
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="action-btn" title="Edit"><i class="fa-solid fa-pen-to-square" style="color:#2196f3"></i></a>
                                <?php if ($user['role'] !== 'admin'): ?>
                                <a href="toggle_premium.php?id=<?php echo $user['id']; ?>&premium=<?php echo $user['is_premium'] ? '0' : '1'; ?>" class="action-btn toggle-premium-btn" title="<?php echo $user['is_premium'] ? 'Remove Premium' : 'Add Premium'; ?>">
                                    <i class="fa-solid fa-crown" style="color: gold;"></i>
                                    <span class="premium-icon <?php echo $user['is_premium'] ? 'premium-remove' : 'premium-add'; ?>">
                                        <i class="fa-solid <?php echo $user['is_premium'] ? 'fa-minus' : 'fa-plus'; ?>"></i>
                                    </span>
                                </a>
                                <a href="#" class="action-btn delete-user" title="Delete" data-id="<?php echo $user['id']; ?>">
                                    <i class="fa-solid fa-trash" style="color:#f44336"></i>
                                </a>
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

    <!-- Popup di conferma eliminazione -->
    <div id="confirm-popup">
        <p>Sei sicuro di voler eliminare questo utente?</p>
        <button id="confirm-yes">Sì</button>
        <button id="confirm-no">No</button>
    </div>

    <script src="../assets/script/admin.js"></script>
    <script src="../assets/script/sort-table-admin.js"></script>
</body>
</html>