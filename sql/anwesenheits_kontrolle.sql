
CREATE TABLE anwesenheits_kontrolle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    status VARCHAR(10) NOT NULL,
    attended_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
