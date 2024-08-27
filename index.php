<?php

// Database connection parameters
$host = 'localhost';
$db = 'library';
$user = 'your_username';
$pass = 'your_password';
$charset = 'utf8mb4';

// Create PDO instance
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Add a new book
function addBook($title, $author, $genre, $price, $releaseDate, $reviewRating) {
    global $dsn, $user, $pass, $options;
    $pdo = new PDO($dsn, $user, $pass, $options);

    $query = "INSERT INTO books (title, author, genre, price, release_date, review_rating) 
              VALUES (:title, :author, :genre, :price, :release_date, :review_rating)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':title' => $title,
        ':author' => $author,
        ':genre' => $genre,
        ':price' => $price,
        ':release_date' => $releaseDate,
        ':review_rating' => $reviewRating
    ]);
}

// Edit a book
function editBook($id, $title, $author, $genre, $price, $releaseDate, $reviewRating) {
    global $dsn, $user, $pass, $options;
    $pdo = new PDO($dsn, $user, $pass, $options);

    $query = "UPDATE books SET title = :title, author = :author, genre = :genre, price = :price, 
              release_date = :release_date, review_rating = :review_rating WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':id' => $id,
        ':title' => $title,
        ':author' => $author,
        ':genre' => $genre,
        ':price' => $price,
        ':release_date' => $releaseDate,
        ':review_rating' => $reviewRating
    ]);
}

// Delete a book
function deleteBook($id) {
    global $dsn, $user, $pass, $options;
    $pdo = new PDO($dsn, $user, $pass, $options);

    $query = "DELETE FROM books WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $id]);
}

function getBooks($page = 1, $sortBy = 'title', $sortOrder = 'ASC') {
    $itemsPerPage = 10;
    $offset = ($page - 1) * $itemsPerPage;

    // Ensure valid sorting
    $validColumns = ['title', 'author', 'genre', 'price', 'release_date', 'review_rating', 'copies_sold'];
    if (!in_array($sortBy, $validColumns)) {
        $sortBy = 'title';
    }

    $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

    // SQL Query
    $query = "
        SELECT id, title, author, genre, price, release_date, review_rating
        FROM books
        ORDER BY $sortBy $sortOrder
        LIMIT :offset, :itemsPerPage
    ";

    // Database connection and execution (using PDO)
    global $dsn, $user, $pass, $options;
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


