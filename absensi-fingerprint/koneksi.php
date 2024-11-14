<?php
$date = new DateTime();
$date->setTimezone(new DateTimeZone('Asia/Jakarta'));
// Mengatur parameter koneksi
$host = "mysql8.serv00.com"; // biasanya localhost
$username = "m6780_absensi"; // username default untuk XAMPP
$password = "Absensi123"; // password default untuk XAMPP, biasanya kosong
$database = "m6780_project-absensi"; // ganti dengan nama database yang kamu gunakan
// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
?>
