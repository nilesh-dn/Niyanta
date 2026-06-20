-- Demo table created when the Hello Example plugin is installed.
CREATE TABLE IF NOT EXISTS hello_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO hello_messages (message) VALUES ('Hello from the example plugin!');
