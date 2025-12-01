<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Bank - Register</title>
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
            <h1>Register</h1>
            <p class="subtitle">Register to create an account</p>

            <form method="POST" action="index.php" class="loginForm">
                <label for="Fname">First name</label>
                <input type="text" id="fName" name="Fname" required placeholder="Bob">

                <label for="Lname">Last name</label>
                <input type="text" id="Lname" name="Lname" required placeholder="Smith">

                <label for="userName">Username</label>
                <input type="text" id="userName" name="username" required placeholder="bobSmith123">

                <label for="phone">Phone number</label>
                <input type="number" id="phone" name="phone" required placeholder="0123456789">

                <label for="address">Address</label>
                <input type="text" id="address" name="address" required placeholder="31 street">

                <label for="city">City</label>
                <input type="text" id="city" name="city" required placeholder="Dublin">

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