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
// Fungsi untuk menghapus semua data di tabel log_absensi
if (isset($_POST['delete_all'])) {
    $delete_query = "DELETE FROM log_absensi";
    if ($conn->query($delete_query) === TRUE) {
        echo "Semua data berhasil dihapus.";
    } else {
        echo "Error menghapus data: " . $conn->error;
    }
}

// Fungsi untuk mengekspor data ke CSV
if (isset($_POST['export_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="log_absensi.csv"');

    $output = fopen("php://output", "w");
    fputcsv($output, array('No', 'Nama', 'Fingerprint ID', 'Waktu')); // Header CSV

    $query = "SELECT peserta.nama, log_absensi.fingerprint_id, log_absensi.waktu 
              FROM log_absensi 
              JOIN peserta ON log_absensi.fingerprint_id = peserta.fingerprint_id 
              ORDER BY log_absensi.waktu DESC";
    $result = $conn->query($query);
    $no = 1;

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, array($no, $row['nama'], $row['fingerprint_id'], $row['waktu']));
            $no++;
        }
    }
    fclose($output);
    exit;
}

// Fungsi pencarian
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

$query = "SELECT peserta.nama, log_absensi.fingerprint_id, log_absensi.waktu 
          FROM log_absensi 
          JOIN peserta ON log_absensi.fingerprint_id = peserta.fingerprint_id 
          WHERE peserta.nama LIKE ? 
          ORDER BY log_absensi.waktu DESC";
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
    <title>Log Absensi</title>
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
        <br>
        <!-- Form Pencarian -->
        <form method="get" action="log_absensi.php" class="mb-6">
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

        <!-- Tabel Data Absensi -->

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="py-3 px-6 text-left">No</th>
                        <th class="py-3 px-6 text-left">Nama</th>
                        <th class="py-3 px-6 text-left">Fingerprint ID</th>
                        <th class="py-3 px-6 text-left">Waktu/Tanggal</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php
$no = 1;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Mengambil waktu dan mem-parsing-nya
        $original_time = $row['waktu'];
        $date_time = new DateTime($original_time);
        
        // Menambah jam
        $date_time->modify('+5 hours'); // Ubah angka ini sesuai kebutuhan

        // Mendapatkan waktu yang sudah dimodifikasi
        $modified_time = $date_time->format('Y-m-d H:i:s');

        echo "<tr class='border-b'>";
        echo "<td class='py-3 px-6'>" . $no++ . "</td>";
        echo "<td class='py-3 px-6'>" . $row['nama'] . "</td>";
        echo "<td class='py-3 px-6'>" . $row['fingerprint_id'] . "</td>";
        echo "<td class='py-3 px-6'>" . $modified_time . "</td>"; // Menampilkan waktu yang sudah diubah
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4' class='py-3 px-6 text-center'>Tidak ada data ditemukan</td></tr>";
}
?>
                </tbody>
            </table>
        </div>

        <!-- Form untuk Ekspor CSV dan Hapus Semua Data -->
        <div class="flex justify-between mt-6">
            <form method="post" action="log_absensi.php">
                <button type="submit" name="export_csv"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Export to CSV
                </button>
            </form>

            <form method="post" action="log_absensi.php">
                <button type="submit" name="delete_all"
                        class="px-6 py-2 bg-red-600 text-white rounded-lg shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                        onclick="return confirm('Yakin ingin menghapus semua data?')">
                    Hapus Semua Data
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
