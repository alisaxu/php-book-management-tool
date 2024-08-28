<?php
// Database configuration
$host = 'mysql'; // Docker Compose service name
$db   = 'bookdb'; // Database name
$user = 'user'; // Database username
$pass = 'userpassword'; // Database password
$charset = 'utf8mb4'; // Database charset

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Create a PDO instance
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}

// Function to add a new book
function addBook($postParams) {
    global $pdo;

    // Validate and sanitize input parameters
    list($title, $author, $genre, $price, $releaseDate, $reviewRating) = validateParams($postParams);

    // Prepare and execute the SQL query
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

    // Get the ID of the last inserted book
    $lastInsertId = $pdo->lastInsertId();
    return ['status' => 'success', 'message' => 'Book added successfully', 'id' => $lastInsertId];
}

// Function to edit an existing book
function editBook($postParams) {
    global $pdo;

    // Validate and sanitize input parameters
    $id = isset($postParams['id']) ? filter_var($postParams['id'], FILTER_VALIDATE_INT) : null;

    if ($id === null || $id <= 0) {
        throw new InvalidArgumentException("Invalid input data");
    }

    list($title, $author, $genre, $price, $releaseDate, $reviewRating) = validateParams($postParams);

    // Prepare and execute the SQL query
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

    return ['status' => 'success', 'message' => 'Book updated successfully'];
}

// Function to delete a book
function deleteBook($postParams) {
    global $pdo;

    // Validate and sanitize input parameters
    $id = isset($postParams['id']) ? filter_var($postParams['id'], FILTER_VALIDATE_INT) : null;
    if ($id === null || $id <= 0) {
        throw new InvalidArgumentException("Invalid ID");
    }

    // Prepare and execute the SQL query
    $query = "DELETE FROM books WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $id]);

    return ['status' => 'success', 'message' => 'Book deleted successfully'];
}

// Function to get books with pagination and sorting
function getBooks($postParams) {
    global $pdo;

    $page = isset($postParams['page']) ? filter_var($postParams['page'], FILTER_VALIDATE_INT) : 1;
    $sortBy = isset($postParams['sort_by']) ? filter_var($postParams['sort_by'], FILTER_SANITIZE_STRING) : 'title';
    $sortOrder = isset($postParams['sort_order']) ? filter_var($postParams['sort_order'], FILTER_SANITIZE_STRING) : 'ASC';

    $itemsPerPage = 10;
    $offset = ($page - 1) * $itemsPerPage;

    // Ensure valid sorting column
    $validColumns = ['title', 'author', 'genre', 'price', 'release_date', 'review_rating'];
    if (!in_array($sortBy, $validColumns)) {
        $sortBy = 'title';
    }

    $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

    // Prepare and execute the SQL query
    $query = "
        SELECT id, title, author, genre, price, release_date, review_rating
        FROM books
        ORDER BY $sortBy $sortOrder
        LIMIT :offset, :itemsPerPage
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
    $stmt->execute();

    // Return results
    return ['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
}

// Function to validate and sanitize input parameters
function validateParams($postParams) {
    $errors = [];

    // Validate title
    $title = isset($postParams['title']) ? filter_var($postParams['title'], FILTER_SANITIZE_STRING) : null;
    if ($title === null) {
        $errors[] = "Title is required.";
    }

    // Validate author
    $author = isset($postParams['author']) ? filter_var($postParams['author'], FILTER_SANITIZE_STRING) : null;
    if ($author === null) {
        $errors[] = "Author is required.";
    }

    // Validate genre
    $genre = isset($postParams['genre']) ? filter_var($postParams['genre'], FILTER_SANITIZE_STRING) : null;
    if ($genre === null) {
        $errors[] = "Genre is required.";
    }

    // Validate price
    $price = isset($postParams['price']) ? filter_var($postParams['price'], FILTER_VALIDATE_FLOAT) : null;
    if ($price === false) {
        $errors[] = "Price must be a valid number.";
    } elseif ($price < 0) {
        $errors[] = "Price cannot be negative.";
    }

    // Validate release date
    $releaseDate = isset($postParams['release_date']) ? filter_var($postParams['release_date'], FILTER_SANITIZE_STRING) : null;
    if ($releaseDate === null) {
        $errors[] = "Release date is required.";
    }

    // Validate review rating
    $reviewRating = isset($postParams['review_rating']) ? filter_var($postParams['review_rating'], FILTER_VALIDATE_FLOAT) : null;
    if ($reviewRating === false) {
        $errors[] = "Review rating must be a valid number.";
    }

    // Throw exception if there are validation errors
    if (!empty($errors)) {
        throw new InvalidArgumentException("Invalid input data: " . implode(" ", $errors));
    }

    return [
        $title,
        $author,
        $genre,
        $price,
        $releaseDate,
        $reviewRating,
    ];
}

// Function to analyze the route and dispatch to the correct handler
function routeAnalyze() {
    global $pdo;

    // Get the requested URI
    $requestUri = $_SERVER['REQUEST_URI'];

    // Remove query string part
    $requestUri = strtok($requestUri, '?');

    // Define routing rules
    $routes = [
        '/addBook' => 'addBook',
        '/editBook' => 'editBook',
        '/deleteBook' => 'deleteBook',
        '/getBooks' => 'getBooks'
    ];

    // Route parsing and function dispatching
    if (array_key_exists($requestUri, $routes)) {
        $functionName = $routes[$requestUri];
        if (function_exists($functionName)) {
            // Call the matching function with parameters
            $postParams = $_POST;
            $result = call_user_func($functionName, $postParams);
            echo json_encode($result); // Return result as JSON
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Function not found!']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => '404 Not Found']);
    }
}

// Call routeAnalyze to handle routing
routeAnalyze();
