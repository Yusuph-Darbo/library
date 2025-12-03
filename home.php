<?php
session_start();

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
$selectedCategory = "";
$books = [];
$categories = [];
$successMessage = "";
$errorMessage = "";

// ===== Fetch all categories =====
$categoryStmt = $conn->query("SELECT categoryId, categoryDesc FROM category ORDER BY categoryDesc");
while ($row = $categoryStmt->fetch_assoc()) {
    $categories[] = $row;
}

// ===== Handle Reservation =====
if (isset($_POST['reserve_book']) && isset($_SESSION['username'])) {
    $isbn = $_POST['isbn'];
    $username = $_SESSION['username'];

    // Check if book is still available
    $checkStmt = $conn->prepare("SELECT isReserved FROM book WHERE isbn = ?");
    $checkStmt->bind_param("s", $isbn);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $book = $result->fetch_assoc();

    if ($book && !$book['isReserved']) {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Update book to reserved
            $updateStmt = $conn->prepare("UPDATE book SET isReserved = 1 WHERE isbn = ?");
            $updateStmt->bind_param("s", $isbn);
            $updateStmt->execute();
            $updateStmt->close();

            // Insert into reserved table
            $reserveStmt = $conn->prepare("INSERT INTO reserved (isbn, username, reservedDate) VALUES (?, ?, NOW())");
            $reserveStmt->bind_param("ss", $isbn, $username);
            $reserveStmt->execute();
            $reserveStmt->close();

            // Commit transaction
            $conn->commit();

            // Redirect to prevent form resubmission and refresh the search results
            $redirectUrl = "home.php?search=" . urlencode($_GET['search'] ?? '') . "&category=" . urlencode($_GET['category'] ?? '') . "&success=reserved";
            header("Location: " . $redirectUrl);
            exit();
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $errorMessage = "Failed to reserve the book. Please try again.";
        }
    } else {
        $errorMessage = "This book is already reserved.";
    }
    $checkStmt->close();
}

// ===== Check for success message from redirect =====
if (isset($_GET['success']) && $_GET['success'] === 'reserved') {
    $successMessage = "Book reserved successfully!";
}

// ===== Handle Search and Category Filter =====
if ((isset($_GET['search']) && !empty(trim($_GET['search']))) || (isset($_GET['category']) && !empty($_GET['category']))) {
    $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : "";
    $selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : "";

    // Build dynamic query
    $sql = "SELECT isbn, bookTitle, author, edition, year, genre, isReserved, coverImage FROM book WHERE 1=1";
    $params = [];
    $types = "";

    // Add search condition
    if (!empty($searchQuery)) {
        $sql .= " AND (bookTitle LIKE ? OR author LIKE ? OR genre LIKE ?)";
        $searchParam = "%{$searchQuery}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "sss";
    }

    // Add category filter
    if (!empty($selectedCategory)) {
        $sql .= " AND genre = ?";
        $params[] = $selectedCategory;
        $types .= "s";
    }

    $sql .= " LIMIT 5";

    // Prepare and execute
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
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
    <title>Book Bank - Home Page</title>
</head>

<body>

    <header>
        <img class="logo" src="images/libraryLogo.png" alt="A open book">
        <h2>Book Bank</h2>

        <div class="registerSection">
            <?php if (isset($_SESSION['username'])): ?>
            <a href="reserve.php" class="nav-link">My Reservations</a>
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

            <!-- Success/Error Messages -->
            <?php if (!empty($successMessage)): ?>
            <div class="successBox">
                <p><?php echo htmlspecialchars($successMessage); ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($errorMessage)): ?>
            <div class="errorBox">
                <p><?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
            <?php endif; ?>

            <form method="GET" action="home.php" class="searchForm">
                <input type="text" name="search" placeholder="Search by title, author, or genre..."
                    value="<?php echo htmlspecialchars($searchQuery); ?>">

                <select name="category" class="categorySelect">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['categoryDesc']); ?>"
                        <?php echo ($selectedCategory === $cat['categoryDesc']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['categoryDesc']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="searchBtn">Search</button>
            </form>

            <?php if (!empty($searchQuery) || !empty($selectedCategory)): ?>
            <div class="resultsSection">
                <h2>Search Results
                    <?php if (!empty($searchQuery)): ?>
                    for "<?php echo htmlspecialchars($searchQuery); ?>"
                    <?php endif; ?>
                    <?php if (!empty($selectedCategory)): ?>
                    in <?php echo htmlspecialchars($selectedCategory); ?>
                    <?php endif; ?>
                </h2>

                <?php if (empty($books)): ?>
                <p class="noResults">No books found. Try a different search term or category.</p>
                <?php else: ?>
                <p class="resultCount"><?php echo count($books); ?> book(s) found (showing max 5 results)</p>

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

                            <!-- Reservation Button -->
                            <?php if (isset($_SESSION['username'])): ?>
                            <?php if (!$book['isReserved']): ?>
                            <form method="POST"
                                action="home.php?search=<?php echo urlencode($searchQuery); ?>&category=<?php echo urlencode($selectedCategory); ?>"
                                class="reserveForm">
                                <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>">
                                <button type="submit" name="reserve_book" class="reserveBtn">Reserve Book</button>
                            </form>
                            <?php else: ?>
                            <button class="reserveBtn disabled" disabled>Already Reserved</button>
                            <?php endif; ?>
                            <?php else: ?>
                            <p class="loginPrompt">
                                <a href="login.php">Login</a> to reserve this book
                            </p>
                            <?php endif; ?>
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

</html>