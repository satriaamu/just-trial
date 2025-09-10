<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "mokobang");
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Koneksi database gagal']));
}

$is_admin = isset($_SESSION['admin_id']);
$user_id = $_SESSION['user_id'] ?? 0;

if (!$is_admin && $user_id === 0) {
    die(json_encode(['status' => 'error', 'message' => 'Akses ditolak']));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $transaction_id = intval($_GET['transaction_id']);

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
    $messages = [];
    while($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    echo json_encode(['status' => 'success', 'messages' => $messages]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $transaction_id = intval($data['transaction_id']);
    $message = htmlspecialchars(trim($data['message']));
    
    $sender_type = $is_admin ? 'admin' : 'user';
    $read_by_admin = $is_admin ? 1 : 0;

    $sql = "INSERT INTO chats (transaction_id, sender_type, message, read_by_admin) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $transaction_id, $sender_type, $message, $read_by_admin);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengirim pesan']);
    }
}

$conn->close();
?>