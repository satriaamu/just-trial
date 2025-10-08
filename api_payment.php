<?php
session_start();
header('Content-Type: application/json');

// Menghentikan output buffer untuk mencegah kerusakan format JSON
ob_start();

$response = [];

try {
    // --- Pengaturan Koneksi Database ---
    $db_host = 'db.fr-pari1.bengt.wasmernet.com';
    $db_port = 10272;
    $db_name = 'mokobang';
    $db_user = '67cf073f7d048000d4a691b28792';
    $db_pass = '068e67cf-073f-7e33-8000-c7299acc4133';

    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- Validasi Permintaan ---
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metode tidak diizinkan.', 405);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $virtual_account = $data['virtual_account'] ?? '';
    $api_key = $data['api_key'] ?? '';
    $secret_api_key = "RAHASIA123";

    if ($api_key !== $secret_api_key) {
        throw new Exception('Unauthorized: API Key tidak valid.', 401);
    }

    if (empty($virtual_account)) {
        throw new Exception('Nomor Virtual Account tidak boleh kosong.', 400);
    }

    // --- Fungsi Log (versi PDO) ---
    function addLog($pdo, $trxId, $sender, $message) {
        $stmt = $pdo->prepare("INSERT INTO chats (transaction_id, sender_type, message, read_by_admin) VALUES (?, ?, ?, 1)");
        $stmt->execute([$trxId, $sender, $message]);
    }

    // --- Logika Utama ---
    $stmt = $pdo->prepare("SELECT id, status, product_id, reserved_qty, payment_deadline FROM transactions WHERE virtual_account = ?");
    $stmt->execute([$virtual_account]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        throw new Exception('Virtual Account tidak ditemukan.', 404);
    }

    $transaction_id = $transaction['id'];

    if ($transaction['status'] !== 'awaiting_payment') {
        throw new Exception('Tagihan ini sudah dibayar atau dibatalkan.', 409);
    }

    // Cek apakah deadline sudah lewat
    if (strtotime($transaction['payment_deadline']) < time()) {
        $pdo->beginTransaction();
        try {
            // Jika hangus, kembalikan stok jika ada yang direservasi
            if ($transaction['reserved_qty'] > 0) {
                $stmt_stock = $pdo->prepare("UPDATE katalog SET stock = stock + ? WHERE id = ?");
                $stmt_stock->execute([$transaction['reserved_qty'], $transaction['product_id']]);
            }
            
            // Update status jadi cancelled dan reset reserved_qty
            $cancel_stmt = $pdo->prepare("UPDATE transactions SET status = 'cancelled', reserved_qty = 0 WHERE id = ?");
            $cancel_stmt->execute([$transaction_id]);
            
            addLog($pdo, $transaction_id, 'system', 'Waktu pembayaran telah habis. Transaksi dibatalkan.');
            $pdo->commit();
            
            throw new Exception('Tagihan telah hangus karena melewati batas waktu pembayaran.', 410);

        } catch (Exception $e) {
            $pdo->rollBack();
            // Lemparkan kembali pengecualian asli jika itu bukan yang kita buat
            if ($e->getCode() !== 410) {
                throw new Exception('Gagal membatalkan transaksi: ' . $e->getMessage(), 500);
            }
            throw $e;
        }

    } else {
        // Jika masih valid, update status jadi paid dan reset reserved_qty
        $update_stmt = $pdo->prepare("UPDATE transactions SET status = 'paid', reserved_qty = 0 WHERE id = ?");

        if ($update_stmt->execute([$transaction_id])) {
            $pdf_link = "cetak-bukti.php?id=" . $transaction_id;
            addLog($pdo, $transaction_id, 'system', $pdf_link);

            $response = ['status' => 'success', 'message' => 'Pembayaran untuk VA ' . $virtual_account . ' berhasil.'];
        } else {
            throw new Exception('Gagal memperbarui status pembayaran.', 500);
        }
    }

} catch (Exception $e) {
    $code = $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($code);
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

// Membersihkan buffer sebelum mengirim output JSON
ob_end_clean();
echo json_encode($response);
?>