<?php
session_start();

// Check if the user is already logged in, redirect to index.php if yes
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate username
    if (empty($_POST['username'])) {
        $error = "Username is required";
    } else {
        $username = $_POST['username'];
    }

    // Validate password
    if (empty($_POST['password'])) {
        $error = "Password is required";
    } else {
        $password = $_POST['password'];
    }

    if (empty($error)) {
        // Validate input and authenticate user
        $sql = "SELECT id, role, username, password FROM users WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Authentication successful, store user ID and role in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid username or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 300px;
            max-width: 80%;
        }

        h2 {
            margin-top: 0;
            text-align: center;
            color: #333;
        }

        form {
            margin-top: 20px;
            text-align: center;
        }

        label {
            display: inline-block;
            margin-bottom: 5px;
            color: #666;
            width: 100px;
            /* Adjust width as needed */
            text-align: right;
            /* Align text to the right */
        }

        input[type="text"],
        input[type="password"] {
            width: calc(100% - 110px);
            /* Adjust width to fill remaining space */
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 77px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            float: right;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .error {
            color: #ff0000;
            margin-top: 10px;
        }

        .asterisk {
            color: red;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <center><?php if (isset($error)) : ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
        </center>
        <form action="login.php" method="post">
            <label for="username">Username<span class="asterisk">*</span> :</label>
            <input type="text" id="username" name="username" required><br>
            <label for="password">Password<span class="asterisk">*</span> :</label>
            <input type="password" id="password" name="password" required><br>
            <input type="submit" value="Login">
        </form>
    </div>
</body>

</html>