<?php
session_start();

// Menghancurkan session saat logout
session_unset();
session_destroy();

// Arahkan kembali ke halaman login atau halaman utama
header("Location: login"); 
exit();
?>
