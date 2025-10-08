<?php
// File: config.php

// -- Konfigurasi Database Baru --
$db_host = 'db.fr-pari1.bengt.wasmernet.com';
$db_port = 10272;
$db_name = 'mokobang';
$db_user = '67cf073f7d048000d4a691b28792';
$db_pass = '068e67cf-073f-7e33-8000-c7299acc4133';

/**
 * Membuat koneksi ke database menggunakan mysqli.
 * @return mysqli Objek koneksi mysqli yang berhasil.
 */
function getMysqliConnection() {
    global $db_host, $db_user, $db_pass, $db_name, $db_port;
    
    // Buat koneksi dengan menyertakan port
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

    if ($conn->connect_error) {
        // Pada production, sebaiknya log error, bukan menampilkannya ke user.
        die("Koneksi mysqli gagal: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

/**
 * Membuat koneksi ke database menggunakan PDO.
 * @return PDO Objek koneksi PDO yang berhasil.
 */
function getPdoConnection() {
    global $db_host, $db_user, $db_pass, $db_name, $db_port;
    
    try {
        // Buat DSN dengan menyertakan port
        $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        // Pada production, sebaiknya log error, bukan menampilkannya ke user.
        die("Koneksi PDO gagal: " . $e->getMessage());
    }
}
?>