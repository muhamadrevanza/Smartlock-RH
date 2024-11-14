<?php
include 'koneksi.php';

if (isset($_GET['fingerprint_id'])) {
    $fingerprint_id = $_GET['fingerprint_id'];

    // Validasi fingerprint_id, harus integer
    if (!filter_var($fingerprint_id, FILTER_VALIDATE_INT)) {
        echo 'Fingerprint ID tidak valid.';
        exit;
    }

    // Mencari nama peserta berdasarkan fingerprint_id
    $query = "SELECT nama FROM peserta WHERE fingerprint_id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $fingerprint_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $nama = $row['nama'];

            // Menambahkan log ke tabel log_absensi (hanya simpan fingerprint_id)
            $log_query = "INSERT INTO log_absensi (fingerprint_id) VALUES (?)";
            $log_stmt = $conn->prepare($log_query);

            if ($log_stmt) {
                $log_stmt->bind_param("i", $fingerprint_id);
                $log_stmt->execute();
                $log_stmt->close();
            } else {
                echo 'Gagal menyiapkan query log: ' . $conn->error;
            }

            // Hitung berapa kali peserta ini telah melakukan absensi
            $count_query = "SELECT COUNT(*) as count FROM log_absensi WHERE fingerprint_id = ?";
            $count_stmt = $conn->prepare($count_query);
            $count_stmt->bind_param("i", $fingerprint_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count_row = $count_result->fetch_assoc();
            $total_absensi = $count_row['count'];
            $count_stmt->close();

            // Tampilkan nama peserta dan jumlah absensi
            echo "$nama\n";
            echo "<br>Absen ke-$total_absensi";

            // Kirim notifikasi jika absensi mencapai kelipatan 5
            if ($total_absensi % 5 === 0) {
                sendTelegramNotification($nama, $total_absensi);
            }
        } else {
            echo 'Fingerprint ID tidak ditemukan.';
        }

        $stmt->close();
    } else {
        echo 'Gagal menyiapkan query peserta: ' . $conn->error;
    }

    $conn->close();
} else {
    echo 'Parameter fingerprint_id harus disertakan.';
}

function sendTelegramNotification($nama, $kehadiran_ke) {
    $token = "7250946744:AAGyh4b1OBzVIlvhrlPfNpAvVjZ6mQqoLCg";  // Ganti dengan API token dari BotFather
    $chat_id = "1534199546";  // Ganti dengan chat_id yang didapat
    $message = "$nama telah absen ke $kehadiran_ke kali";

    $url = "https://api.telegram.org/bot$token/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $message
    ];

    // Inisialisasi CURL
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    curl_close($curl);

    return $response;
}
?>

