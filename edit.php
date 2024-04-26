<?php
session_start();
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}

require_once 'config.php';
$errors = [];
// Check if user ID is provided
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION['role'] == 'user' && $_GET['id'] != $_SESSION['user_id']) {
    header("Location: index.php");
    exit;
}

$user_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Fetch user data from the database based on ID
$sql = "SELECT * FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user exists
if (!$user) {
    echo "User not found.";
    exit;
}

$role =$user['role'];

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required.";
    }

    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    }

    if (empty($role)) {
        $errors[] = "Role is required.";
    }

    // If no validation errors, proceed with database update
    if (empty($errors)) {
        if (!empty($password)) {
            $sql = "UPDATE users SET username=:username, email=:email, password=:password, role=:role WHERE id=:id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $user_id, 'username' => $username, 'email' => $email, 'password' => $hashedPassword, 'role' => $role]);
        } else {
            $sql = "UPDATE users SET username=:username, email=:email, role=:role WHERE id=:id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $user_id, 'username' => $username, 'email' => $email, 'role' => $role]);
        }
        
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <style>
        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"],
        select {
            width: calc(100% - 12px);
            /* Calculate width to include padding and border */
            margin-bottom: 10px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        input[type="submit"] {
            padding: 8px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        select {
            /* Remove width for select to allow it to inherit the width from the input[type="text"] */
            padding: 8px;
        }

        .asterisk {
            color: red;
        }

        .error {
            color: red;
        }
    </style>
</head>

<body>
    <h2>Edit User</h2>

    <?php if (!empty($errors)) : ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error) : ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Form for editing user information -->
    <form action="edit.php?id=<?php echo $user_id; ?>" method="post">
        <label for="username">Username<span class="asterisk">*</span> :</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars(isset($username) ? $username : $user['username']); ?>">
        <label for="email">Email<span class="asterisk">*</span> :</label>
        <input type="text" id="email" name="email" value="<?php echo htmlspecialchars(isset($email) ? $email : $user['email']); ?>">
        <label for="password">New password :</label>
        <input type="password" id="password" name="password" value="">
        <label for="role">Role<span class="asterisk">*</span> :</label>
        <select id="role" name="role" required>
            <option <?php if($role=='user') echo 'selected'; ?> value="user">User</option>
            <option <?php if($role=='admin') echo 'selected' ?> value="admin">Admin</option>
        </select>
        <br /><br />
        <input type="submit" value="Update User">
    </form>
</body>

</html>