<?php
session_start();
header('Content-Type: application/json');

// Hanya untuk simulasi, di dunia nyata, ini akan divalidasi dengan payment gateway
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']));
}

$data = json_decode(file_get_contents('php://input'), true);
$billing_number = $data['billing_number'] ?? '';
$api_key = $data['api_key'] ?? ''; // Kunci rahasia untuk otorisasi

// Kunci API statis untuk contoh ini
$secret_api_key = "RAHASIA123";

if ($api_key !== $secret_api_key) {
    http_response_code(401);
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized: API Key tidak valid.']));
}

if (empty($billing_number)) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'Nomor billing tidak boleh kosong.']));
}

$conn = new mysqli("localhost", "root", "", "mokobang");
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['status' => 'error', 'message' => 'Koneksi database gagal.']));
}

// Cari transaksi berdasarkan nomor billing
$stmt = $conn->prepare("SELECT id, status, payment_deadline FROM transactions WHERE billing_number = ?");
$stmt->bind_param("s", $billing_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Billing tidak ditemukan.']);
} else {
    $transaction = $result->fetch_assoc();
    if ($transaction['status'] !== 'awaiting_payment') {
        http_response_code(409); // Conflict
        echo json_encode(['status' => 'error', 'message' => 'Billing ini sudah dibayar atau dibatalkan.']);
    } elseif (new DateTime() > new DateTime($transaction['payment_deadline'])) {
        // Tandai sebagai expired jika sudah lewat waktu
        $update_stmt = $conn->prepare("UPDATE transactions SET status = 'expired' WHERE id = ?");
        $update_stmt->bind_param("i", $transaction['id']);
        $update_stmt->execute();
        http_response_code(410); // Gone
        echo json_encode(['status' => 'error', 'message' => 'Billing sudah kedaluwarsa.']);
    } else {
        // Jika valid, ubah status menjadi 'paid'
        $update_stmt = $conn->prepare("UPDATE transactions SET status = 'paid' WHERE id = ?");
        $update_stmt->bind_param("i", $transaction['id']);
        if ($update_stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Pembayaran untuk billing ' . $billing_number . ' berhasil.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui status pembayaran.']);
        }
    }
}
$conn->close();
?>