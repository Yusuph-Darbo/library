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
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="reserve.php" class="nav-link">See reserved books</a>
                <a href="logout.php" class="nav-link">Logout</a>
            <?php else: ?>
                <a href="login.php" class="nav-link">Login</a>
                <a href="register.php" class="nav-link">Register</a>
            <?php endif; ?>
        </div>
    </header>


    <footer>
        <p>© 2025 Book Bank — All Rights Reserved</p>
    </footer>

</body>

</html>