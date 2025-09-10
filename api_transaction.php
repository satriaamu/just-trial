<?php
// FIX: Memulai output buffering untuk menangkap output yang tidak diinginkan (spt error PHP)
ob_start();

session_start();

// Set header di awal, tapi buffer akan menahan outputnya
header('Content-Type: application/json');

$response = []; // Siapkan array untuk respons final

try {
    $conn = new mysqli("localhost", "root", "", "mokobang");
    if ($conn->connect_error) {
        // Lemparkan exception jika koneksi gagal
        throw new Exception('Koneksi ke database gagal: ' . $conn->connect_error, 500);
    }
    $conn->set_charset("utf8mb4");

    $is_admin = isset($_SESSION['admin_id']);
    $user_id = $_SESSION['user_id'] ?? 0;
    $admin_id = $_SESSION['admin_id'] ?? 0;

    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? $_GET['action'] ?? '';

    if (empty($action)) {
        throw new Exception('Aksi tidak valid atau tidak disediakan.', 400);
    }
    
    function addLog($conn, $trxId, $sender, $message) {
        $stmt = $conn->prepare("INSERT INTO chats (transaction_id, sender_type, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $trxId, $sender, $message);
        $stmt->execute();
    }

    switch ($action) {
        case 'create_billing':
            if (!$is_admin) throw new Exception('Hanya admin yang bisa membuat billing.', 403);

            $target_user_id = intval($data['user_id'] ?? 0);
            $product_id = intval($data['product_id'] ?? 0);
            $deal_price = floatval($data['deal_price'] ?? 0);
            $shipping_cost = floatval($data['shipping_cost'] ?? 0);

            if ($target_user_id === 0 || $product_id === 0 || $deal_price <= 0) {
                throw new Exception('Data tidak lengkap atau tidak valid. Pastikan semua field terisi.', 400);
            }

            $total_bill = $deal_price + $shipping_cost;
            $billing_number = 'INV-' . time() . '-' . $target_user_id;
            $virtual_account = '8808' . rand(1000000000, 9999999999);
            $payment_deadline = date('Y-m-d H:i:s', strtotime('+14 days'));
            $status = 'awaiting_payment';
            
            $sql = "INSERT INTO transactions (user_id, product_id, admin_id, status, deal_price, shipping_cost, total_bill, billing_number, virtual_account, payment_deadline) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            if ($stmt === false) throw new Exception('Gagal menyiapkan statement SQL: ' . $conn->error, 500);

            $stmt->bind_param("iiisdddsss", $target_user_id, $product_id, $admin_id, $status, $deal_price, $shipping_cost, $total_bill, $billing_number, $virtual_account, $payment_deadline);

            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Billing berhasil dibuat.', 'billing_number' => $billing_number];
            } else {
                throw new Exception('Gagal menyimpan billing ke database: ' . $stmt->error, 500);
            }
            $stmt->close();
            break;

        // --- Kasus lainnya tetap di sini ---
        default:
            throw new Exception('Aksi tidak dikenal.', 400);
            break;
    }
    $conn->close();

} catch (Exception $e) {
    // Menangkap semua error dan memformatnya sebagai JSON
    $code = $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($code);
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

// FIX: Membersihkan semua output yang mungkin sudah ada (termasuk error HTML)
ob_end_clean();

// Mencetak respons JSON yang bersih
echo json_encode($response);