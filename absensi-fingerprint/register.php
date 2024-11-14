<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hash password sebelum menyimpannya
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);

    if ($stmt->execute()) {
        echo "Pengguna berhasil ditambahkan!";
    } else {
        echo "Kesalahan: " . $stmt->error;
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
    <title>Registrasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">

<div class="w-full max-w-md p-8 space-y-3 bg-white rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold text-center">Registrasi Pengguna Baru</h2>
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
        <button type="submit" class="w-full p-2 text-white bg-blue-600 rounded-lg">Daftar</button>
    </form>
</div>

</body>
</html>
