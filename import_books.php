<?php

// ===== Database connection =====
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// ===== Open Library API fetch =====
// Example: Fetch books by subject "science_fiction"
$subject = "science_fiction";
$api_url = "https://openlibrary.org/subjects/$subject.json?limit=25";

$response = file_get_contents($api_url);
if ($response === false) {
    die("Failed to fetch data from Open Library API.");
}

$data = json_decode($response, true);
if (!isset($data['works'])) {
    die("No books found in API response.");
}

// ===== Prepare insert statement =====
$stmt = $conn->prepare("
    INSERT IGNORE INTO book (isbn, bookTitle, author, edition, year, genre, isReserved, coverImage)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// ===== Loop through books and insert =====
$inserted = 0;
foreach ($data['works'] as $book) {
    $title = $book['title'] ?? 'Unknown';

    // Author
    $author = isset($book['authors'][0]['name']) ? $book['authors'][0]['name'] : 'Unknown';

    // Edition key as a proxy for edition/ISBN
    $isbn = isset($book['cover_edition_key']) ? $book['cover_edition_key'] : null;
    $edition = $isbn; // Using Open Library edition key as edition identifier

    // Publish year
    $publish_year = $book['first_publish_year'] ?? null;

    // Genre / subject
    $genre = isset($book['subject']) ? implode(', ', array_slice($book['subject'], 0, 3)) : 'Unknown';

    // Reserved (default false)
    $reserved = 0;

    $coverId = $book['cover_edition_key'] ?? null;

    if ($coverId) {
        $coverUrl = "https://covers.openlibrary.org/b/olid/" . $coverId . "-M.jpg";
    } else {
        $coverUrl = null; // or a default placeholder image
    }

    $stmt->bind_param(
        "ssssisis",
        $isbn,
        $title,
        $author,
        $edition,
        $publish_year,
        $genre,
        $reserved,
        $coverUrl
    );

    if ($stmt->execute()) {
        $inserted++;
    }
}

$stmt->close();
$conn->close();

echo "Import completed! $inserted books inserted into database.";