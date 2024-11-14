<?php
include 'koneksi.php';
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

// Fungsi untuk menambah peserta
if (isset($_POST['add_peserta'])) {
    $nama = $_POST['nama'];
    $fingerprint_id = $_POST['fingerprint_id'];

    // Cek apakah fingerprint_id atau nama sudah ada di database
    $check_query = "SELECT * FROM peserta WHERE nama = ? OR fingerprint_id = ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("si", $nama, $fingerprint_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $error_message = "Nama atau Fingerprint ID sudah terdaftar.";
    } else {
        // Jika tidak ada duplikasi, tambahkan peserta baru
        $insert_query = "INSERT INTO peserta (nama, fingerprint_id) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($insert_query);
        $stmt_insert->bind_param("si", $nama, $fingerprint_id);

        if ($stmt_insert->execute()) {
            $success_message = "Peserta berhasil ditambahkan.";
        } else {
            $error_message = "Gagal menambahkan peserta.";
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
}

// Fungsi untuk menghapus peserta
if (isset($_POST['delete_peserta'])) {
    $peserta_id = $_POST['peserta_id'];

    // Cek apakah peserta memiliki entri di log_absensi
    $check_log_query = "SELECT * FROM log_absensi WHERE fingerprint_id = (SELECT fingerprint_id FROM peserta WHERE id = ?)";
    $stmt_check_log = $conn->prepare($check_log_query);
    $stmt_check_log->bind_param("i", $peserta_id);
    $stmt_check_log->execute();
    $result_check_log = $stmt_check_log->get_result();

    if ($result_check_log->num_rows > 0) {
        $error_message = "Peserta tidak dapat dihapus karena memiliki catatan absensi.";
    } else {
        $delete_query = "DELETE FROM peserta WHERE id = ?";
        $stmt_delete = $conn->prepare($delete_query);
        $stmt_delete->bind_param("i", $peserta_id);

        if ($stmt_delete->execute()) {
            $success_message = "Peserta berhasil dihapus.";
        } else {
            $error_message = "Gagal menghapus peserta.";
        }
        $stmt_delete->close();
    }
    $stmt_check_log->close();
}

// Fungsi pencarian peserta
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

$query = "SELECT * FROM peserta WHERE nama LIKE ? ORDER BY nama ASC";
$stmt = $conn->prepare($query);
$search_param = "%" . $search . "%";
$stmt->bind_param("s", $search_param);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peserta</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<nav class="bg-gray-800 p-4">
    <div class="container mx-auto flex justify-between items-center">
        <div>
            <a href="log_absensi.php" class="text-white px-4 py-2 hover:bg-gray-700 rounded">Log Absensi</a>
            <a href="peserta.php" class="text-white px-4 py-2 hover:bg-gray-700 rounded">Peserta</a>
        </div>
        <div>
            <a href="logout.php" class="text-white px-4 py-2 hover:bg-red-700 rounded">Logout</a>
        </div>
    </div>
</nav>
<div class="container mx-auto">

    <!-- Tampilkan Pesan -->
    <?php if (isset($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $success_message; ?>
        </div>
    <?php elseif (isset($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    <br>
    <!-- Form Pencarian -->
    <form method="get" action="peserta.php" class="mb-6">
        <div class="flex justify-center">
            <input type="text" name="search" id="search" placeholder="Cari Berdasarkan Nama"
                   value="<?php echo htmlspecialchars($search); ?>"
                   class="w-1/3 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button type="submit"
                    class="ml-4 px-4 py-2 bg-indigo-600 text-white rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Cari
            </button>
        </div>
    </form>

    <!-- Tabel Daftar Peserta -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-3 px-6 text-left">No</th>
                    <th class="py-3 px-6 text-left">Nama</th>
                    <th class="py-3 px-6 text-left">Fingerprint ID</th>
                    <th class="py-3 px-6 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php
                $no = 1;
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr class='border-b'>";
                        echo "<td class='py-3 px-6'>" . $no++ . "</td>";
                        echo "<td class='py-3 px-6'>" . $row['nama'] . "</td>";
                        echo "<td class='py-3 px-6'>" . $row['fingerprint_id'] . "</td>";
                        echo "<td class='py-3 px-6'>
                                <form method='post' action='peserta.php'>
                                    <input type='hidden' name='peserta_id' value='" . $row['id'] . "'>
                                    <button type='submit' name='delete_peserta'
                                            class='px-4 py-2 bg-red-600 text-white rounded-lg shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500'
                                            onclick='return confirm(\"Yakin ingin menghapus peserta ini?\")'>
                                        Hapus
                                    </button>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='py-3 px-6 text-center'>Tidak ada data peserta</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Form Tambah Peserta -->
    <div class="mt-6">
        <h2 class="text-2xl font-bold mb-4">Tambah Peserta</h2>
        <form method="post" action="peserta.php">
            <div class="mb-4">
                <label for="nama" class="block text-gray-700">Nama</label>
                <input type="text" id="nama" name="nama" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="mb-4">
                <label for="fingerprint_id" class="block text-gray-700">Fingerprint ID</label>
                <input type="number" id="fingerprint_id" name="fingerprint_id" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" name="add_peserta"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                Tambah Peserta
            </button>
        </form>
    </div>

</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

