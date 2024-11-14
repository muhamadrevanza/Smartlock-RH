<?php
session_start();
include 'koneksi.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['loggedin'] = true;
            header('Location: index.php');
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">

<div class="w-full max-w-md p-8 space-y-3 bg-white rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold text-center">Login</h2>
    <?php if (isset($error)): ?>
        <p class="text-red-500"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST">
        <div>
            <label class="block mb-1 text-sm font-medium">Username</label>
            <input type="text" name="username" required class="w-full p-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block mb-1 text-sm font-medium">Password</label>
            <input type="password" name="password" required class="w-full p-2 border border-gray-300 rounded-lg">
        </div>
        <br>
        <button type="submit" class="w-full p-2 text-white bg-blue-600 rounded-lg">Login</button>
    </form>
</div>

</body>
</html>
