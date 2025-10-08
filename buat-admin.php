<?php
$pesan = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $username = $_POST['username'];
    $password = $_POST['password'];
    $full_name = $_POST['full_name'];

    // Validasi dasar
    if (empty($username) || empty($password) || empty($full_name)) {
        $pesan = '<div class="message error">Semua kolom wajib diisi!</div>';
    } else {
        // Enkripsi password dengan aman
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Koneksi ke database
        $db_host = 'db.fr-pari1.bengt.wasmernet.com';
        $db_port = 10272;
        $db_name = 'mokobang';
        $db_user = '67cf073f7d048000d4a691b28792';
        $db_pass = '068e67cf-073f-7e33-8000-c7299acc4133';

        try {
             $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Koneksi gagal: " . $e->getMessage());
        }   
            // Cek apakah username sudah ada
            $stmt_check = $conn->prepare("SELECT id FROM admins WHERE username = ?");
            $stmt_check->bind_param("s", $username);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                $pesan = '<div class="message error">Username "' . htmlspecialchars($username) . '" sudah digunakan. Silakan pilih yang lain.</div>';
            } else {
                // Masukkan admin baru ke database
                $stmt_insert = $conn->prepare("INSERT INTO admins (username, password, full_name) VALUES (?, ?, ?)");
                $stmt_insert->bind_param("sss", $username, $hashed_password, $full_name);
                
                if ($stmt_insert->execute()) {
                    $pesan = '<div class="message success">Admin baru dengan username "' . htmlspecialchars($username) . '" berhasil dibuat! Akun ini sekarang bisa digunakan untuk login.<br><strong>PERINGATAN: Demi keamanan, segera hapus file ini (buat-admin.php) dari server Anda.</strong></div>';
                } else {
                    $pesan = '<div class="message error">Gagal membuat admin baru: ' . $stmt_insert->error . '</div>';
                }
                $stmt_insert->close();
            }
            $stmt_check->close();
            $conn->close();
        }
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Admin Baru</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 12px;
            border: none;
            background-color: #28a745;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            line-height: 1.5;
            font-size: 14px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Registrasi Akun Admin Baru</h2>
        <?php if (!empty($pesan)) echo $pesan; ?>
        <form method="POST" action="buat-admin.php">
            <div class="form-group">
                <label for="full_name">Nama Lengkap</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            <div class="form-group">
                <label for="username">Username Baru</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password Baru</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Buat Akun Admin</button>
        </form>
    </div>
</body>
</html>