-- Brain Rot Submission Database Schema
-- This database stores anonymous submissions for educational ML training

CREATE DATABASE IF NOT EXISTS brainrot_submissions;
USE brainrot_submissions;

-- Table to store submissions (no personal data)
CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_type ENUM('text', 'image') NOT NULL,
    content_text TEXT,
    image_filename VARCHAR(255),
    image_mime_type VARCHAR(100),
    categories JSON NOT NULL,
    submission_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    session_hash VARCHAR(64), -- Anonymous session identifier for deduplication
    INDEX idx_timestamp (submission_timestamp),
    INDEX idx_type (submission_type)
);

-- Table to track categories for reference
CREATE TABLE IF NOT EXISTS brain_rot_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert the six brain rot categories
INSERT IGNORE INTO brain_rot_categories (category_name, description) VALUES
('Misinformation & Conspiracy', 'False information, conspiracy theories, and misleading claims that spread rapidly'),
('Toxic Negativity & Trolling', 'Hateful comments, cyberbullying, and deliberate inflammatory content'),
('Clickbait & Outrage Bait', 'Sensationalized headlines and content designed to provoke strong emotional reactions'),
('Superficial Trends & Vanity Loops', 'Appearance-focused content and shallow trend-chasing that promotes superficial values'),
('Meme Overload & Context Loss', 'Overwhelming volume of memes that lose original meaning and context'),
('Doomscrolling & Anxiety Loops', 'Content that promotes endless scrolling and increases anxiety or negative emotions');