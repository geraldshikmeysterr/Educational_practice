-- Blog Database Schema
-- Run this file to initialize the database

CREATE DATABASE IF NOT EXISTS blog_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blog_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Posts table
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    author_id INT NOT NULL,
    likes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Comments table
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    likes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Post likes tracking (prevent double-liking)
CREATE TABLE IF NOT EXISTS post_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    UNIQUE KEY unique_like (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Comment likes tracking
CREATE TABLE IF NOT EXISTS comment_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comment_id INT NOT NULL,
    user_id INT NOT NULL,
    UNIQUE KEY unique_comment_like (comment_id, user_id),
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insert default admin user (password: admin123)
INSERT IGNORE INTO users (name, email, password, role) VALUES 
('Admin', 'admin@blog.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample posts
INSERT IGNORE INTO posts (title, content, author_id) VALUES
('Добро пожаловать в наш блог!', 'Это первый пост нашего замечательного блога. Здесь мы будем делиться интересными статьями, новостями и мыслями о самых разных темах. Присоединяйтесь к нашему сообществу, регистрируйтесь и оставляйте комментарии под понравившимися материалами. Мы рады каждому читателю и надеемся, что наш контент будет вам полезен и интересен. Не забудьте подписаться и возвращаться за новыми публикациями!', 1),
('Технологии будущего', 'Искусственный интеллект, квантовые компьютеры, биотехнологии — мир меняется быстрее, чем когда-либо. В этой статье мы рассмотрим ключевые тренды, которые определят наше завтра. Начнём с нейросетей: они уже сегодня помогают врачам ставить диагнозы, художникам создавать картины, а учёным — открывать новые молекулы. Что будет дальше? Эксперты считают, что к 2030 году ИИ станет неотъемлемой частью каждого рабочего места.', 1),
('Путешествие по России', 'От Калининграда до Владивостока — наша страна удивительно разнообразна. В этой статье мы расскажем о самых красивых и необычных местах, которые стоит посетить. Байкал — глубочайшее озеро планеты, хранящее 20% мировых запасов пресной воды. Алтайские горы, Карелия с её тысячами озёр, величественный Кавказ — каждый уголок России имеет свою неповторимую атмосферу и историю.', 1);
