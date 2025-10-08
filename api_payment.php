<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']));
}

$data = json_decode(file_get_contents('php://input'), true);
$virtual_account = $data['virtual_account'] ?? '';
$api_key = $data['api_key'] ?? ''; 
$secret_api_key = "RAHASIA123";

if ($api_key !== $secret_api_key) {
    http_response_code(401);
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized: API Key tidak valid.']));
}

if (empty($virtual_account)) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'Nomor Virtual Account tidak boleh kosong.']));
}

$conn = new mysqli("localhost", "root", "", "mokobang");
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['status' => 'error', 'message' => 'Koneksi database gagal.']));
}
$conn->set_charset("utf8mb4");

// Fungsi addLog disalin ke sini untuk digunakan
function addLog($conn, $trxId, $sender, $message) {
    $stmt = $conn->prepare("INSERT INTO chats (transaction_id, sender_type, message, read_by_admin) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("iss", $trxId, $sender, $message);
    $stmt->execute();
}

$stmt = $conn->prepare("SELECT id, status, product_id, reserved_qty, payment_deadline FROM transactions WHERE virtual_account = ?");
$stmt->bind_param("s", $virtual_account);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Virtual Account tidak ditemukan.']);
} else {
    $transaction = $result->fetch_assoc();
    $transaction_id = $transaction['id'];

    if ($transaction['status'] !== 'awaiting_payment') {
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'Tagihan ini sudah dibayar atau dibatalkan.']);
    } else {
        // Cek apakah deadline sudah lewat
        if (strtotime($transaction['payment_deadline']) < time()) {
            $conn->begin_transaction();
            try {
                // Jika hangus, kembalikan stok jika ada yang direservasi
                if ($transaction['reserved_qty'] > 0) {
                    $stmt_stock = $conn->prepare("UPDATE katalog SET stock = stock + ? WHERE id = ?");
                    $stmt_stock->bind_param("ii", $transaction['reserved_qty'], $transaction['product_id']);
                    $stmt_stock->execute();
                }
                
                // Update status jadi cancelled dan reset reserved_qty
                $cancel_stmt = $conn->prepare("UPDATE transactions SET status = 'cancelled', reserved_qty = 0 WHERE id = ?");
                $cancel_stmt->bind_param("i", $transaction_id);
                $cancel_stmt->execute();
                
                addLog($conn, $transaction_id, 'system', 'Waktu pembayaran telah habis. Transaksi dibatalkan.');
                $conn->commit();
                
                http_response_code(410); // 410 Gone
                echo json_encode(['status' => 'error', 'message' => 'Tagihan telah hangus karena melewati batas waktu pembayaran.']);

            } catch (Exception $e) {
                $conn->rollback();
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Gagal membatalkan transaksi: ' . $e->getMessage()]);
            }

        } else {
            // Jika masih valid, update status jadi paid dan reset reserved_qty
            $update_stmt = $conn->prepare("UPDATE transactions SET status = 'paid', reserved_qty = 0 WHERE id = ?");
            $update_stmt->bind_param("i", $transaction_id);

            if ($update_stmt->execute()) {
                $pdf_link = "cetak-bukti.php?id=" . $transaction_id;
                addLog($conn, $transaction_id, 'system', $pdf_link);

                echo json_encode(['status' => 'success', 'message' => 'Pembayaran untuk VA ' . $virtual_account . ' berhasil.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui status pembayaran.']);
            }
        }
    }
}
$conn->close();
?>