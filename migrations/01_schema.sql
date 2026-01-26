CREATE DATABASE IF NOT EXISTS coding_day;
USE coding_day;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash CHAR(60) NULL,  -- NULL = login tanpa password
    role ENUM('PANITIA', 'PESERTA', 'JURI') NOT NULL DEFAULT 'PESERTA'
);

CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL,
    leader_id INT, -- Ketua Tim
    is_verified TINYINT(1) NOT NULL DEFAULT 0, 
    verified_by_user_id INT NULL, 
    FOREIGN KEY (leader_id) REFERENCES users(id),
    FOREIGN KEY (verified_by_user_id) REFERENCES users(id)
);

CREATE TABLE verification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT,
    admin_id INT,
    verified_at DATETIME NOT NULL,
    FOREIGN KEY (team_id) REFERENCES teams(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);