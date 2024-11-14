<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berhasil Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">

<div class="w-full max-w-md p-8 space-y-3 bg-white rounded-lg shadow-lg text-center">
    <h1 class="text-2xl font-bold">Berhasil Login</h1>
    <a href="log_absensi.php" class="mt-4 w-full p-2 text-white bg-blue-600 rounded-lg text-center block">Log Absensi</a>
    <a href="peserta.php" class="mt-4 w-full p-2 text-white bg-green-600 rounded-lg text-center block">Daftar Peserta</a>
    <a href="logout.php" class="mt-4 w-full p-2 text-white bg-red-600 rounded-lg text-center block">Logout</a>
   
</div>

</body>
</html>
