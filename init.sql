CREATE DATABASE IF NOT EXISTS fugr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE fugr;

SET NAMES utf8;

CREATE TABLE IF NOT EXISTS tasks (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255) NOT NULL,
    description  TEXT,
    due_date     DATETIME,
    created_date DATETIME,
    status       ENUM('Выполнена', 'Не выполнена'),
    priority     ENUM('Низкий', 'Средний', 'Высокий'),
    category     VARCHAR(255)
);
