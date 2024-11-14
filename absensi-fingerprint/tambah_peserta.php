<?php
include 'koneksi.php';

if (isset($_GET['nama']) && isset($_GET['id'])) {
    $nama = $_GET['nama'];
    $id = $_GET['id'];

    // Menyiapkan query untuk memasukkan data
    $query = "INSERT INTO peserta (nama, fingerprint_id) VALUES (?, ?)";
    
    // Menyiapkan statement
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        // Mengikat parameter
        $stmt->bind_param("si", $nama, $id);
        
        // Menjalankan query
        if ($stmt->execute()) {
            echo 'Data berhasil ditambahkan';
        } else {
            echo 'Gagal menambahkan data: ' . $stmt->error;
        }
        
        // Menutup statement
        $stmt->close();
    } else {
        echo 'Gagal menyiapkan query: ' . $conn->error;
    }
    
    // Menutup koneksi
    $conn->close();
} else {
    echo 'Parameter nama dan id harus disertakan.';
}
?>
