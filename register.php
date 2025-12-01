<?php
// Run this ONLY when the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Database connection
    $conn = new mysqli("localhost", "root", "", "library");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get form data safely
    $fname = $_POST['Fname'];
    $lname = $_POST['Lname'];
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Prepare SQL statement
    $stmt = $conn->prepare("
        INSERT INTO user (Fname, Lname, username, phone, address, city, password)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("sssssss", $fname, $lname, $username, $phone, $address, $city, $password);

    if ($stmt->execute()) {
        // Redirect to login page after successful registration
        header("Location: login.php?registered=1");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Bank - Register</title>
</head>

<body>

    <header>
        <img class="logo" src="images/libraryLogo.png" alt="A open book">
        <h2>Book Bank</h2>

        <div class="registerSection">
            <a href="login.php" class="nav-link">Login</a>
            <a href="register.php" class="nav-link active">Register</a>

        </div>
    </header>

    <main>
        <div class="formWrapper">
            <h1>Register</h1>
            <p class="subtitle">Register to create an account</p>

            <form method="POST" action="register.php" class="loginForm">
                <label for="Fname">First name</label>
                <input type="text" id="fName" name="Fname" required placeholder="Bob">

                <label for="Lname">Last name</label>
                <input type="text" id="Lname" name="Lname" required placeholder="Smith">

                <label for="userName">Username</label>
                <input type="text" id="userName" name="username" required placeholder="bobSmith123">

                <label for="phone">Phone number</label>
                <input type="tel" id="phone" name="phone" required placeholder="0123456789">

                <label for="address">Address</label>
                <input type="text" id="address" name="address" required placeholder="31 street">

                <label for="city">City</label>
                <input type="text" id="city" name="city" required placeholder="Dublin">

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="******">

                <input type="submit" value="Register" class="submitBtn">
            </form>
        </div>
    </main>

    <footer>
        <p>© 2025 Book Bank — All Rights Reserved</p>
    </footer>

</body>

</html>