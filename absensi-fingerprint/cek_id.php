<?php
include 'koneksi.php';

if (isset($_GET['fingerprint_id'])) {
    $fingerprint_id = $_GET['fingerprint_id'];

    // Menyiapkan query untuk mencari peserta berdasarkan fingerprint_id
    $query = "SELECT nama FROM peserta WHERE fingerprint_id = ?";
    
    // Menyiapkan statement
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        // Mengikat parameter
        $stmt->bind_param("i", $fingerprint_id);
        
        // Menjalankan query
        $stmt->execute();
        
        // Mendapatkan hasil
        $result = $stmt->get_result();
        
        // Memeriksa apakah ada data yang ditemukan
        if ($result->num_rows > 0) {
            // Mengambil data nama dari hasil
            $row = $result->fetch_assoc();
            echo $row['nama'];
        } else {
            echo 'kosong';
        }
        
        // Menutup statement
        $stmt->close();
    } else {
        echo 'Gagal menyiapkan query: ' . $conn->error;
    }
    
    // Menutup koneksi
    $conn->close();
} else {
    echo 'Parameter fingerprint_id harus disertakan.';
}
?>
