-- ========================================
-- TABELLA USERS
-- ========================================
CREATE TABLE users (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email          VARCHAR(255) NOT NULL UNIQUE,
    username       VARCHAR(100) NOT NULL UNIQUE,
    password_hash  TEXT NOT NULL,
    is_premium     BOOLEAN NOT NULL DEFAULT FALSE,
    role           ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARSET=utf8mb4;

-- Add admin user
INSERT INTO users (email, username, password_hash, role) VALUES
('admin@example.com', 'admin', '$2y$10$MWizjnPiYnrx/VwDhV9mQuDJj.s3PlkyPxlHVCQlwii6kSE.HUb4q', 'admin');

-- ========================================
-- TABELLA NOTES
-- ========================================
CREATE TABLE notes (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       BIGINT UNSIGNED NOT NULL,
    title         VARCHAR(255) NOT NULL,
    content       VARCHAR(1500) NOT NULL,
    priority      ENUM('Bassa', 'Normale', 'Alta', 'Immediata') NOT NULL DEFAULT 'Normale',
    is_shared     BOOLEAN NOT NULL DEFAULT FALSE,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE INDEX idx_notes_user_id ON notes(user_id);

-- ========================================
-- TABELLA NOTE_SHARES
-- ========================================
CREATE TABLE note_shares (
    note_id      BIGINT UNSIGNED NOT NULL,
    user_id      BIGINT UNSIGNED NOT NULL,
    permission   ENUM('view','edit') NOT NULL DEFAULT 'view',
    shared_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (note_id, user_id),
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARSET=utf8mb4;

-- ========================================
-- TABELLA TAGS
-- ========================================
CREATE TABLE tags (
    note_id     BIGINT UNSIGNED NOT NULL,
    tag_name    VARCHAR(50) NOT NULL,
    PRIMARY KEY (note_id, tag_name),
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARSET=utf8mb4;
