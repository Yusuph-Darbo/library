<?php
session_start();

// ===== Database Connection =====
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library";

$conn = new mysqli($servername, $username, $password, $dbname);

// If connection fails → go to error page
if ($conn->connect_error) {
    header("Location: error.php");
    exit();
}

// ===== Handle Login Submission =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $inputUser = $_POST['username'];
    $inputPass = $_POST['password'];

    // Prepare secure SQL statement
    $stmt = $conn->prepare("SELECT id, password FROM user WHERE username = ?");
    $stmt->bind_param("s", $inputUser);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check username + hashed password
    if ($user && password_verify($inputPass, $user['password'])) {

        // Success → set session
        $_SESSION['user_id'] = $user['id'];

        // Redirect to your book dashboard page
        header("Location: dashboard.php");
        exit();

    } else {
        // Invalid login
        $loginError = "Incorrect username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Bank</title>
</head>

<body>

    <header>
        <img class="logo" src="images/libraryLogo.png" alt="A open book">
        <h2>Book Bank</h2>

        <div class="registerSection">
            <a href="index.php" class="nav-link active">Login</a>
            <a href="#" class="nav-link">Register</a>
        </div>
    </header>

    <main>
        <div class="formWrapper">
            <h1>Welcome Back</h1>
            <p class="subtitle">Login to access your Book Bank</p>

            <form method="POST" action="login.php" class="loginForm">
                <label for="userName">Username</label>
                <input type="text" id="userName" name="username" required placeholder="bobSmith123">

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="******">

                <input type="submit" value="Login" class="submitBtn">
            </form>
        </div>
    </main>

    <footer>
        <p>© 2025 Book Bank — All Rights Reserved</p>
    </footer>

</body>

</html>