-- Custom tables for the Hostinger mirroring setup.
-- Applied automatically on first DB init (via docker-entrypoint-initdb.d)
-- and defensively re-checked by account-manager / sync-daemon on startup.

CREATE TABLE IF NOT EXISTS mirror_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    hostinger_password VARCHAR(255) NOT NULL,
    local_password VARCHAR(255) NOT NULL,
    imap_host VARCHAR(255) DEFAULT 'imap.hostinger.com',
    imap_port INT DEFAULT 993,
    active TINYINT DEFAULT 1,
    last_synced_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS email_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_email VARCHAR(255) NOT NULL,
    message_uid VARCHAR(64),
    from_addr VARCHAR(255),
    to_addr VARCHAR(255),
    subject VARCHAR(500),
    sent_date DATETIME,
    synced_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_account (account_email),
    INDEX idx_date (sent_date)
);

CREATE TABLE IF NOT EXISTS dovecot_users (
    email VARCHAR(255) PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    home VARCHAR(255) NOT NULL
);
