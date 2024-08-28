CREATE DATABASE IF NOT EXISTS bookdb;
USE bookdb;

CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    genre VARCHAR(100),
    price DECIMAL(10, 2),
    release_date DATE,
    review_rating FLOAT(2,1) CHECK (review_rating >= 0 AND review_rating <= 5),
    copies_sold INT DEFAULT 0
);

CREATE INDEX idx_copies_sold ON books (copies_sold);
