<?php
session_start();
header('Content-Type: application/json');

$response = [];

try {
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

    $is_admin = isset($_SESSION['admin_id']);
    $user_id = $_SESSION['user_id'] ?? 0;

    if (!$is_admin && $user_id === 0) {
        throw new Exception('Akses Ditolak', 403);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $transaction_id = intval($_GET['transaction_id'] ?? 0);
        if ($transaction_id === 0) {
            throw new Exception('ID Transaksi tidak valid', 400);
        }

        // Tandai pesan sebagai sudah dibaca saat admin mengambilnya
        if ($is_admin) {
            $stmt_update = $conn->prepare("UPDATE chats SET read_by_admin = 1 WHERE transaction_id = ? AND sender_type = 'user'");
            $stmt_update->bind_param("i", $transaction_id);
            $stmt_update->execute();
        }

        $sql = "SELECT * FROM chats WHERE transaction_id = ? ORDER BY created_at ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = $result->fetch_all(MYSQLI_ASSOC);
        
        $response = ['status' => 'success', 'messages' => $messages];
    } else {
        throw new Exception('Metode Request Tidak Valid', 405);
    }

} catch (Exception $e) {
    $code = $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($code);
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

echo json_encode($response);
$conn->close();