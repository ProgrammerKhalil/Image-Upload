<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'user_images');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Registration
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $check_query = $conn->query("SELECT * FROM users WHERE username = '$username'");
    if ($check_query->num_rows > 0) {
        $error_message = "Username already exists. Please choose another.";
    } else {
        $conn->query("INSERT INTO users (username, password) VALUES ('$username', '$password')");
        $success_message = "Registration successful! Please log in.";
    }
}

// Handle Login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username = '$username'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            header("Location: index.php");
        } else {
            $error_message = "Invalid password.";
        }
    } else {
        $error_message = "User not found.";
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
}

// Handle File Upload
if (isset($_POST['upload'])) {
    $name = $_POST['name'];
    $image = $_FILES['image']['name'];
    $target = "uploads/" . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $conn->query("INSERT INTO user_data (name, image) VALUES ('$name', '$image')");
        $success_message = "Image uploaded successfully.";
    } else {
        $error_message = "Image upload failed.";
    }
}

// Handle Search
$image_result = '';
if (isset($_POST['search'])) {
    $search_name = $_POST['search_name'];
    $result = $conn->query("SELECT * FROM user_data WHERE name = '$search_name'");
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $image_result = "<img src='uploads/" . $data['image'] . "' alt='" . $data['name'] . "' class='result-image'>";
    } else {
        $image_result = "<p class='error'>No image found for this name.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Upload and Search</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #343a40;
            padding: 1rem;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-right: 1rem;
        }
        .content {
            padding: 2rem;
        }
        .result-image {
            max-width: 100%;
            height: auto;
        }
        footer {
            text-align: center;
            padding: 1rem;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <header class="navbar">
        <a href="index.php" class="navbar-brand">KHALIL's SITE</a>
        <nav>
            <?php if (isset($_SESSION['username'])): ?>
                <a href="?action=upload">Upload</a>
                <a href="?action=search">Search</a>
                <a href="?logout=true">Logout</a>
            <?php else: ?>
                <a href="?action=login">Login</a>
                <a href="?action=register">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="content">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['username'])): ?>
            <?php if (isset($_GET['action']) && $_GET['action'] == 'register'): ?>
                <h2>Register</h2>
                <form method="post">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" name="register" class="btn btn-primary">Register</button>
                </form>
            <?php else: ?>
                <h2>Login</h2>
                <form method="post">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" name="login" class="btn btn-primary">Login</button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <?php if (isset($_GET['action']) && $_GET['action'] == 'upload'): ?>
                <h2>Upload Image</h2>
                <form method="post" enctype="multipart/form-data">
                    <input type="text" name="name" placeholder="Enter your name" required>
                    <input type="file" name="image" required>
                    <button type="submit" name="upload" class="btn btn-success">Upload</button>
                </form>
            <?php elseif (isset($_GET['action']) && $_GET['action'] == 'search'): ?>
                <h2>Search Image</h2>
                <form method="post">
                    <input type="text" name="search_name" placeholder="Enter name to search" required>
                    <button type="submit" name="search" class="btn btn-info">Search</button>
                </form>
                <div class="result mt-3">
                    <?= $image_result ?>
                </div>
            <?php else: ?>
                <h2>Welcome, <?= $_SESSION['username'] ?>!</h2>
                <p>Select an option from the menu above.</p>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 KHALIL's SITE. All Rights Reserved.</p>
    </footer>
</body>
</html>
