<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===== Database Connection =====
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    header("Location: error.php");
    exit();
}

// ===== Initialize variables =====
$searchQuery = "";
$books = [];

// ===== Handle Search =====
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $searchQuery = trim($_GET['search']);

    // Prepare search statement (searches title, author, and genre)
    $stmt = $conn->prepare("
        SELECT isbn, bookTitle, author, edition, year, genre, isReserved, coverImage
        FROM book
        WHERE bookTitle LIKE ? OR author LIKE ? OR genre LIKE ?
        LIMIT 50
    ");

    $searchParam = "%{$searchQuery}%";
    $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Bank - Search Books</title>
</head>

<body>

    <header>
        <img class="logo" src="images/libraryLogo.png" alt="A open book">
        <h2>Book Bank</h2>

        <div class="registerSection">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="nav-link">Logout</a>
            <?php else: ?>
                <a href="login.php" class="nav-link">Login</a>
                <a href="register.php" class="nav-link">Register</a>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <div class="searchSection">
            <h1>Search for a Book</h1>

            <form method="GET" action="search_books.php" class="searchForm">
                <input type="text" name="search" placeholder="Search by title, author, or genre..."
                    value="<?php echo htmlspecialchars($searchQuery); ?>" required>
                <button type="submit" class="searchBtn">Search</button>
            </form>

            <?php if (!empty($searchQuery)): ?>
                <div class="resultsSection">
                    <h2>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h2>

                    <?php if (empty($books)): ?>
                        <p class="noResults">No books found. Try a different search term.</p>
                    <?php else: ?>
                        <p class="resultCount"><?php echo count($books); ?> book(s) found</p>

                        <div class="bookGrid">
                            <?php foreach ($books as $book): ?>
                                <div class="bookCard">
                                    <?php if (!empty($book['coverImage'])): ?>
                                        <img src="<?php echo htmlspecialchars($book['coverImage']); ?>"
                                            alt="<?php echo htmlspecialchars($book['bookTitle']); ?>" class="bookCover">
                                    <?php else: ?>
                                        <div class="noCover">No Cover</div>
                                    <?php endif; ?>

                                    <div class="bookInfo">
                                        <h3><?php echo htmlspecialchars($book['bookTitle']); ?></h3>
                                        <p class="author">by <?php echo htmlspecialchars($book['author']); ?></p>

                                        <?php if (!empty($book['year'])): ?>
                                            <p class="year">Year: <?php echo htmlspecialchars($book['year']); ?></p>
                                        <?php endif; ?>

                                        <?php if (!empty($book['genre'])): ?>
                                            <p class="genre"><?php echo htmlspecialchars($book['genre']); ?></p>
                                        <?php endif; ?>

                                        <p class="status <?php echo $book['isReserved'] ? 'reserved' : 'available'; ?>">
                                            <?php echo $book['isReserved'] ? 'Reserved' : 'Available'; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>© 2025 Book Bank – All Rights Reserved</p>
    </footer>

</body>