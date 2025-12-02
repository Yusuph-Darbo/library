<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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
$successMessage = "";
$errorMessage = "";
$reservedBooks = [];
$currentUsername = $_SESSION['user_id']; // This contains the username

// ===== Handle Unreserve =====
if (isset($_POST['unreserve_book'])) {
    $isbn = $_POST['isbn'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update book to available
        $updateStmt = $conn->prepare("UPDATE book SET isReserved = 0 WHERE isbn = ?");
        $updateStmt->bind_param("s", $isbn);
        $updateStmt->execute();
        $updateStmt->close();

        // Delete from reserved table
        $deleteStmt = $conn->prepare("DELETE FROM reserved WHERE isbn = ? AND username = ?");
        $deleteStmt->bind_param("ss", $isbn, $currentUsername);
        $deleteStmt->execute();
        $deleteStmt->close();

        // Commit transaction
        $conn->commit();
        $successMessage = "Book unreserved successfully!";
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $errorMessage = "Failed to unreserve the book. Please try again.";
    }
}

// ===== Fetch Reserved Books =====
$stmt = $conn->prepare("
    SELECT r.isbn, r.reservedDate, b.bookTitle, b.author, b.year, b.genre, b.coverImage
    FROM reserved r
    JOIN book b ON r.isbn = b.isbn
    WHERE r.username = ?
    ORDER BY r.reservedDate DESC
");

$stmt->bind_param("s", $currentUsername);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $reservedBooks[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Book Bank - Reserved books</title>
</head>

<body>

    <header>
        <img class="logo" src="images/libraryLogo.png" alt="A open book">
        <h2>Book Bank</h2>

        <div class="registerSection">
            <a href="home.php" class="nav-link">Home</a>
            <a href="reserve.php" class="nav-link active">My Reservations</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </div>
    </header>

    <main>
        <div class="searchSection">
            <h1>My Reserved Books</h1>

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

            <?php if (empty($reservedBooks)): ?>
                <p class="noResults">You haven't reserved any books yet. <a href="home.php"
                        style="color: #007bff; text-decoration: none; font-weight: 600;">Search for books</a> to get
                    started!</p>
            <?php else: ?>
                <p class="resultCount">You have <?php echo count($reservedBooks); ?> book(s) reserved</p>

                <div class="bookGrid">
                    <?php foreach ($reservedBooks as $book): ?>
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

                                <p class="year">Reserved on: <?php echo date('M d, Y', strtotime($book['reservedDate'])); ?></p>

                                <p class="status reserved">Reserved</p>

                                <!-- Unreserve Button -->
                                <form method="POST" action="reserve.php" class="reserveForm">
                                    <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>">
                                    <button type="submit" name="unreserve_book" class="reserveBtn"
                                        style="background-color: #dc3545;">Unreserve Book</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>© 2025 Book Bank — All Rights Reserved</p>
    </footer>

</body>

</html>