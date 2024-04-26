<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}

// Check user's role
$userRole = $_SESSION['role'];


require_once 'config.php';

// Define the number of users per page
$usersPerPage = 3;

// Get the current page number
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset for SQL query
$offset = ($page - 1) * $usersPerPage;

// Define an empty variable to store search query
$searchQuery = '';

// Check if the search form is submitted
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    // Retrieve the search query from the form
    $searchQuery = $_GET['search'];
    $searchTerm = '%' . $searchQuery . '%';

    $sql = "SELECT COUNT(*) as count FROM users WHERE username LIKE :search OR email LIKE :search";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
    $stmt->execute();

    // Fetch the count
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $totalUsers = $result['count'];

    // Calculate total number of pages
    $totalPages = ceil($totalUsers / $usersPerPage);

    $sql = "SELECT * FROM users WHERE username LIKE :search OR email LIKE :search LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $usersPerPage, PDO::PARAM_INT);
    $stmt->execute();
} else if ($_SESSION['role'] == 'user') {
    $sql = "SELECT * FROM users where id=:id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
} else {
    // Fetch total number of users
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // Calculate total number of pages
    $totalPages = ceil($totalUsers / $usersPerPage);

    $sql = "SELECT * FROM users LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $usersPerPage, PDO::PARAM_INT);
    $stmt->execute();
}

$errors = [];

if ($_SESSION['role'] == 'admin' && $_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required.";
    }

    $sql = "SELECT * FROM users where username=:username";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if (!empty($stmt->fetch(PDO::FETCH_ASSOC))) {
        $errors[] = "Username already exists.";
    }


    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    $sql = "SELECT * FROM users where email=:email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    if (!empty($stmt->fetch(PDO::FETCH_ASSOC))) {
        $errors[] = "Email already exists.";
    }

    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    // Validate role
    if (empty($role)) {
        $errors[] = "Role is required.";
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // If no validation errors, proceed with database insertion
    if (empty($errors)) {
        $sql = "INSERT INTO users (username, email, password,role) VALUES (:username, :email, :password, :role)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $username, 'email' => $email, 'password' => $hashedPassword, 'role' => $role]);

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
    <title>User Management</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .asterisk {
            color: red;
        }

        .error {
            color: red;
        }

        .pagination {
            margin-top: 20px;
        }

        .pagination a {
            padding: 8px 16px;
            text-decoration: none;
            color: black;
            border: 1px solid #ddd;
            margin: 0 4px;
        }

        .pagination a.active {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>

<body>
    <h2>User Management</h2>
    <h3 style="color:blue">Welcome <?php echo $_SESSION['username']; ?>, your are now logged in. <a href='javascript:void(0);' onclick='confirmLogout()'><span style="color:red;">Logout</span></a></h3>
    <?php if ($_SESSION['role'] == 'admin') { ?>
        <!-- Form for adding a new user -->
        <form action="" method="post">
            <h3>Add New User</h3>
            <?php if (!empty($errors)) : ?>
                <div class="error">
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <label for="username">Username<span class="asterisk">*</span> :</label>
            <input type="text" id="username" name="username" required>
            <label for="email">Email<span class="asterisk">*</span> :</label>
            <input type="text" id="email" name="email" required>
            <label for="password">Password<span class="asterisk">*</span> :</label>
            <input type="password" id="password" name="password" required>
            <label for="role">Role<span class="asterisk">*</span> :</label>
            <select id="role" name="role" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
            <br /><br />
            <input type="submit" value="Add User">
        </form>
    <?php } ?>

    <!-- Table to display existing users -->
    <br />
    <h3><?php echo ($userRole == 'user') ? 'Your info' : 'Existing Users' ?></h3>

    <?php if ($userRole === 'admin') { ?>
        <!-- Search form -->
        <form action="" method="get">
            <label for="search">Search by Username or Email:</label>
            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <input type="submit" value="Search">
        </form>
    <?php } ?>

    <table>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
        <?php
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['role']) . "</td>";
            echo "<td><a href='edit.php?id=" . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . "'>Edit</a> | <a href='javascript:void(0);' onclick='confirmDelete(" . htmlspecialchars(json_encode($row['id']), ENT_QUOTES, 'UTF-8') . ")'>Delete</a></td>";
            echo "</tr>";
        }
        ?>
    </table>

    <?php if ($userRole === 'admin') { ?>
        <!-- Pagination links -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                <a href="?search=<?php echo $searchQuery; ?>&&page=<?php echo $i; ?>" <?php if ($i === $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    <?php } ?>

    <script>
        // JavaScript function to prompt confirmation before deleting a user
        function confirmDelete(userId) {
            var confirmDelete = confirm("Are you sure you want to delete this user?");
            if (confirmDelete) {
                window.location.href = "delete.php?id=" + userId;
            }
        }

        // JavaScript function to prompt confirmation before logging out
        function confirmLogout(userId) {
            var confirmLogout = confirm("Are you sure you want to log out?");
            if (confirmLogout) {
                window.location.href = "logout.php";
            }
        }
    </script>
</body>

</html>