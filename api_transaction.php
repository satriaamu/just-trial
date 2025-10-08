<?php
ob_start(); // Mencegah output error merusak format JSON
session_start();
header('Content-Type: application/json');

$response = [];

try {
    // --- Koneksi Database ---
    $conn = new mysqli("localhost", "root", "", "mokobang");
    if ($conn->connect_error) { throw new Exception('Koneksi Database Gagal', 500); }
    $conn->set_charset("utf8mb4");

    // --- Cek Autentikasi ---
    $is_admin = isset($_SESSION['admin_id']);
    $user_id = $_SESSION['user_id'] ?? 0;
    if (!$is_admin && $user_id === 0) { throw new Exception('Akses ditolak.', 403); }

    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? $_GET['action'] ?? '';
    if (empty($action)) throw new Exception('Aksi tidak dispesifikasikan.', 400);

    function addLog($conn, $trxId, $sender, $message, $isOffer = 0, $offerValue = null) {
        $stmt = $conn->prepare("INSERT INTO chats (transaction_id, sender_type, message, is_offer, offer_value, read_by_admin) VALUES (?, ?, ?, ?, ?, ?)");
        $read_by_admin = ($sender === 'admin' || $sender === 'system') ? 1 : 0;
        $stmt->bind_param("issidi", $trxId, $sender, $message, $isOffer, $offerValue, $read_by_admin);
        $stmt->execute();
    }
    
    function getTransactionDetails($conn, $transaction_id, $user_id, $is_admin) {
        $sql = "SELECT t.*, k.tipe as product_name, k.gambar as product_image, u.username as customer_name, k.stock FROM transactions t JOIN katalog k ON t.product_id = k.id JOIN users u ON t.user_id = u.id WHERE t.id = ?";
        if (!$is_admin) $sql .= " AND t.user_id = " . $user_id;
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();
        $details = $stmt->get_result()->fetch_assoc();
        if ($details) {
            $chat_stmt = $conn->prepare("SELECT * FROM chats WHERE transaction_id = ? ORDER BY created_at ASC");
            $chat_stmt->bind_param("i", $transaction_id);
            $chat_stmt->execute();
            $details['chat_logs'] = $chat_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return $details;
    }

    switch ($action) {
        case 'create_billing':
            if (!$is_admin) {
                throw new Exception('Hanya admin yang dapat membuat billing manual.', 403);
            }
            $billing_user_id = intval($data['user_id'] ?? 0);
            $billing_product_id = intval($data['product_id'] ?? 0);
            $deal_price = floatval($data['deal_price'] ?? 0);
            $shipping_cost = floatval($data['shipping_cost'] ?? 0);
            if ($billing_user_id <= 0 || $billing_product_id <= 0 || $deal_price <= 0) {
                throw new Exception('Data tidak lengkap atau tidak valid.', 400);
            }
            $total_bill = $deal_price + $shipping_cost;
            $virtual_account = '8' . str_pad(mt_rand(0, 999999999999999), 15, '0', STR_PAD_LEFT);
            $payment_deadline = date('Y-m-d H:i:s', strtotime('+7 days'));
            $billing_number = 'INV-' . time() . $billing_user_id;
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, product_id, harga_awal, deal_price, shipping_cost, total_bill, status, billing_number, virtual_account, payment_deadline) VALUES (?, ?, ?, ?, ?, ?, 'awaiting_payment', ?, ?, ?)");
            $stmt->bind_param("iidddisss", $billing_user_id, $billing_product_id, $deal_price, $deal_price, $shipping_cost, $total_bill, $billing_number, $virtual_account, $payment_deadline);
            if ($stmt->execute()) {
                $new_transaction_id = $stmt->insert_id;
                $logMessage = "Tagihan manual telah dibuat oleh Admin.\n\n"
                            . "Harga Produk: " . 'Rp ' . number_format($deal_price, 0, ',', '.') . "\n"
                            . "Ongkos Kirim: " . 'Rp ' . number_format($shipping_cost, 0, ',', '.') . "\n"
                            . "--------------------\n"
                            . "Total Tagihan: " . 'Rp ' . number_format($total_bill, 0, ',', '.') . "\n"
                            . "Nomor VA: " . $virtual_account . "\n"
                            . "Batas Pembayaran: " . date('d M Y, H:i', strtotime($payment_deadline)) . "\n\n"
                            . "Silakan lakukan pembayaran.";
                addLog($conn, $new_transaction_id, 'system', $logMessage);
                $response = ['status' => 'success', 'message' => 'Billing berhasil dibuat.', 'billing_number' => $billing_number];
            } else {
                throw new Exception('Gagal menyimpan billing ke database: ' . $stmt->error, 500);
            }
            break;

        case 'get_chat_list':
            if (!$is_admin) throw new Exception('Akses ditolak.', 403);
            $sql = "SELECT 
                        t.id as transaction_id,
                        k.tipe as product_name,
                        k.gambar as product_image,
                        u.username as customer_name,
                        (SELECT COUNT(*) FROM chats c WHERE c.transaction_id = t.id AND c.read_by_admin = 0 AND c.sender_type = 'user') as unread_count,
                        (SELECT MAX(c.created_at) FROM chats c WHERE c.transaction_id = t.id) as last_message_time 
                    FROM transactions t 
                    JOIN users u ON t.user_id = u.id
                    JOIN katalog k ON t.product_id = k.id
                    ORDER BY last_message_time DESC";
            $list = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
            $response = ['status' => 'success', 'list' => $list];
            break;

        case 'get_history':
            $sql = "SELECT t.id, t.total_bill, t.status, t.billing_number, t.created_at, k.tipe AS product_name, u.username, u.id as user_id 
                    FROM transactions t 
                    JOIN katalog k ON t.product_id = k.id 
                    JOIN users u ON t.user_id = u.id";
            if (!$is_admin) {
                $sql .= " WHERE t.user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
            } else {
                $sql .= " ORDER BY t.updated_at DESC";
                $stmt = $conn->prepare($sql);
            }
            $stmt->execute();
            $history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $response = ['status' => 'success', 'history' => $history];
            break;
            
        case 'initiate':
            if ($is_admin) throw new Exception('Admin tidak dapat memulai transaksi.', 403);
            $product_id = intval($data['product_id'] ?? 0);
            if ($product_id === 0) throw new Exception('Produk tidak valid.', 400);
            $prod_stmt = $conn->prepare("SELECT harga, stock FROM katalog WHERE id = ?");
            $prod_stmt->bind_param("i", $product_id);
            $prod_stmt->execute();
            $product = $prod_stmt->get_result()->fetch_assoc();
            if (!$product) throw new Exception('Produk tidak ditemukan.', 404);
            if ($product['stock'] <= 0) throw new Exception('Stok produk telah habis.', 400);
            
            $stmt_insert = $conn->prepare("INSERT INTO transactions (user_id, product_id, harga_awal, status) VALUES (?, ?, ?, 'negotiating')");
            $stmt_insert->bind_param("iid", $user_id, $product_id, $product['harga']);
            $stmt_insert->execute();
            $new_id = $stmt_insert->insert_id;
            addLog($conn, $new_id, 'system', 'Sesi negosiasi dimulai.');
            $response = ['status' => 'success', 'transaction_id' => $new_id];
            break;

        case 'submit_offer':
            $transaction_id = intval($data['transaction_id'] ?? 0);
            $offer_price = floatval(preg_replace('/[^0-9]/', '', $data['offer_price'] ?? '0'));
            $notes = $data['negotiation_notes'] ?? '';
            $custom_details = $data['customization_details'] ?? [];
            $sender_type = $is_admin ? 'admin' : 'user';

            $details_before = getTransactionDetails($conn, $transaction_id, $user_id, $is_admin);
            $is_revision = !empty($details_before['last_offer_by']);
            
            $stmt = $conn->prepare("UPDATE transactions SET status = 'negotiating', last_offer_price = ?, last_offer_by = ?, customization_details = ?, negotiation_notes = ? WHERE id = ?");
            $stmt->bind_param("dsssi", $offer_price, $sender_type, json_encode($custom_details), $notes, $transaction_id);
            $stmt->execute();
            
            $logMessage = $is_revision ? "Mengajukan Penawaran [Revisi]" : "Mengajukan Penawaran Awal";
            if (is_array($custom_details) && !empty($custom_details)) {
                $logMessage .= "\n- Custom:";
                foreach ($custom_details as $item) {
                    $logMessage .= " " . $item['name'] . ";";
                }
            }
            if (!empty($notes)) {
                $logMessage .= "\n- Catatan: " . $notes;
            }

            addLog($conn, $transaction_id, $sender_type, $logMessage, 1, $offer_price);
            $response = ['status' => 'success'];
            break;
        
        case 'admin_accept_offer':
        case 'user_accept_offer':
            $transaction_id = intval($data['transaction_id'] ?? 0);
            $details = getTransactionDetails($conn, $transaction_id, $user_id, $is_admin);
            if (!$details) throw new Exception('Transaksi tidak ditemukan.', 404);
            if ($details['stock'] <= 0) throw new Exception('Stok produk sudah habis, kesepakatan tidak dapat dilanjutkan.', 400);

            if ($action === 'admin_accept_offer') {
                if (!$is_admin) throw new Exception('Akses ditolak.', 403);
                if ($details['last_offer_by'] !== 'user') throw new Exception('Tidak ada penawaran dari user untuk diterima.', 400);
                $logMessage = 'Admin menyetujui penawaran. User diminta mengisi alamat.';
            } else {
                if ($is_admin) throw new Exception('Akses ditolak.', 403);
                if ($details['last_offer_by'] !== 'admin') throw new Exception('Tidak ada penawaran dari admin untuk diterima.', 400);
                $logMessage = 'Customer menyetujui penawaran. Silakan isi alamat pengiriman.';
            }
            
            $conn->begin_transaction();
            try {
                // Kurangi stok produk
                $stmt_stock = $conn->prepare("UPDATE katalog SET stock = stock - 1 WHERE id = ? AND stock > 0");
                $stmt_stock->bind_param("i", $details['product_id']);
                $stmt_stock->execute();
                if ($stmt_stock->affected_rows === 0) {
                    throw new Exception('Gagal mengurangi stok, mungkin sudah habis.', 500);
                }

                // Update transaksi
                $deal_price = $details['last_offer_price'];
                $stmt = $conn->prepare("UPDATE transactions SET status = 'deal', deal_price = ?, reserved_qty = 1, reserved_at = NOW() WHERE id = ?");
                $stmt->bind_param("di", $deal_price, $transaction_id);
                $stmt->execute();
                
                addLog($conn, $transaction_id, 'system', $logMessage);
                $conn->commit();
                $response = ['status' => 'success'];
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;

        case 'submit_shipping_address':
            if ($is_admin) throw new Exception('Akses ditolak.', 403);
            $transaction_id = intval($data['transaction_id'] ?? 0);
            $address = $data['address'] ?? '';
            if (empty($address)) throw new Exception('Alamat tidak boleh kosong.', 400);
            $stmt = $conn->prepare("UPDATE transactions SET shipping_address = ?, status = 'awaiting_shipping_cost' WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sii", $address, $transaction_id, $user_id);
            $stmt->execute();
            addLog($conn, $transaction_id, 'system', "User telah mengirimkan alamat pengiriman. Admin akan menghitung ongkir.");
            $response = ['status' => 'success'];
            break;

        case 'submit_shipping_cost':
            if (!$is_admin) throw new Exception('Akses ditolak.', 403);
            $transaction_id = intval($data['transaction_id'] ?? 0);
            
            if (!isset($data['shipping_cost']) || !is_numeric(preg_replace('/[^0-9]/', '', $data['shipping_cost']))) {
                throw new Exception('Ongkos kirim tidak valid.', 400);
            }
            $shipping_cost_raw = $data['shipping_cost'];
            $shipping_cost = floatval(preg_replace('/[^0-9]/', '', $shipping_cost_raw));

            $details = getTransactionDetails($conn, $transaction_id, $user_id, $is_admin);
            if (!$details || $details['status'] !== 'awaiting_shipping_cost') {
                throw new Exception('Transaksi tidak ditemukan atau statusnya salah (bukan `awaiting_shipping_cost`).', 404);
            }
            
            $deal_price = floatval($details['deal_price']);
            if($deal_price <= 0) {
                throw new Exception('Harga deal tidak valid, tidak bisa membuat tagihan.', 400);
            }

            $total_bill = $deal_price + $shipping_cost;
            $virtual_account = '8' . str_pad(mt_rand(0, 999999999999999), 15, '0', STR_PAD_LEFT);
            $payment_deadline = date('Y-m-d H:i:s', strtotime('+7 days'));
            $billing_number = 'INV-' . time() . $transaction_id;

            $stmt = $conn->prepare("UPDATE transactions SET shipping_cost = ?, total_bill = ?, status = 'awaiting_payment', billing_number = ?, virtual_account = ?, payment_deadline = ? WHERE id = ?");
            $stmt->bind_param("ddsssi", $shipping_cost, $total_bill, $billing_number, $virtual_account, $payment_deadline, $transaction_id);
            
            if ($stmt->execute()) {
                $logMessage = "Tagihan telah dibuat oleh Admin.\n\n"
                            . "Harga Deal: " . 'Rp ' . number_format($deal_price, 0, ',', '.') . "\n"
                            . "Ongkos Kirim: " . 'Rp ' . number_format($shipping_cost, 0, ',', '.') . "\n"
                            . "--------------------\n"
                            . "Total Tagihan: " . 'Rp ' . number_format($total_bill, 0, ',', '.') . "\n"
                            . "Nomor VA: " . $virtual_account . "\n"
                            . "Batas Pembayaran: " . date('d M Y, H:i', strtotime($payment_deadline)) . "\n\n"
                            . "Silakan lakukan pembayaran.";
                addLog($conn, $transaction_id, 'system', $logMessage);
                $response = ['status' => 'success', 'message' => 'Tagihan berhasil dibuat dan dikirim ke user.'];
            } else {
                throw new Exception('Gagal menyimpan ongkos kirim ke database: ' . $stmt->error, 500);
            }
            break;

        case 'admin_reject_offer':
        case 'user_reject_offer':
            $transaction_id = intval($data['transaction_id'] ?? 0);
            if (($action === 'admin_reject_offer' && !$is_admin) || ($action === 'user_reject_offer' && $is_admin)) {
                throw new Exception('Akses ditolak.', 403);
            }

            // Dapatkan detail transaksi untuk pengembalian stok
            $details = getTransactionDetails($conn, $transaction_id, $user_id, $is_admin);
            if (!$details) throw new Exception("Transaksi tidak ditemukan", 404);

            $conn->begin_transaction();
            try {
                // Kembalikan stok jika ada yang direservasi
                if ($details['reserved_qty'] > 0) {
                    $stmt_stock = $conn->prepare("UPDATE katalog SET stock = stock + ? WHERE id = ?");
                    $stmt_stock->bind_param("ii", $details['reserved_qty'], $details['product_id']);
                    $stmt_stock->execute();
                }

                // Update status transaksi menjadi cancelled dan reset qty reservasi
                $stmt = $conn->prepare("UPDATE transactions SET status = 'cancelled', reserved_qty = 0 WHERE id = ?");
                $stmt->bind_param("i", $transaction_id);
                $stmt->execute();

                $logMessage = ($is_admin ? 'Admin' : 'Customer') . ' menolak penawaran. Transaksi dibatalkan.';
                addLog($conn, $transaction_id, 'system', $logMessage);
                
                $conn->commit();
                $response = ['status' => 'success', 'message' => 'Penawaran ditolak.'];
            } catch(Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;
        
        case 'get_details':
            $transaction_id = intval($_GET['transaction_id'] ?? $data['transaction_id'] ?? 0);
            if (!$transaction_id) throw new Exception('ID Transaksi tidak valid', 400);

            if ($is_admin) {
                $stmt_read = $conn->prepare("UPDATE chats SET read_by_admin = 1 WHERE transaction_id = ? AND sender_type = 'user'");
                $stmt_read->bind_param("i", $transaction_id);
                $stmt_read->execute();
            }
            
            $details = getTransactionDetails($conn, $transaction_id, $user_id, $is_admin);
            if($details) {
                $response = ['status' => 'success', 'details' => $details];
            } else {
                 throw new Exception('Transaksi tidak ditemukan atau Anda tidak memiliki akses.', 404);
            }
            break;

        case 'send_chat':
            $transaction_id = intval($data['transaction_id'] ?? 0);
            $message = htmlspecialchars(trim($data['message'] ?? ''));
            if(empty($message)) throw new Exception('Pesan tidak boleh kosong', 400);
            
            $sender_type = $is_admin ? 'admin' : 'user';
            addLog($conn, $transaction_id, $sender_type, $message);
            $response = ['status' => 'success'];
            break;
            
        default:
            throw new Exception('Aksi tidak dikenal.', 400);
            break;
    }
    $conn->close();

} catch (Exception $e) {
    http_response_code($e->getCode() >= 400 ? $e->getCode() : 500);
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

ob_end_clean();
echo json_encode($response);
?>