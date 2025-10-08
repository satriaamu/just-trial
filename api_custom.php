<?php
session_start();
header('Content-Type: application/json');

$response = [];
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

$action = $_GET['action'] ?? '';
$is_admin = isset($_SESSION['admin_id']);

try {
    switch ($action) {
        case 'get_all_customs':
            // Aksi ini hanya untuk admin
            if (!$is_admin) throw new Exception("Unauthorized", 403);
            
            $types_sql = "SELECT * FROM custom_types ORDER BY name ASC";
            $types_result = $conn->query($types_sql);
            $types = [];
            while ($type = $types_result->fetch_assoc()) {
                $options_sql = "SELECT * FROM custom_options WHERE type_id = {$type['id']} ORDER BY name ASC";
                $options_result = $conn->query($options_sql);
                $options = [];
                while ($option = $options_result->fetch_assoc()) {
                    $options[] = $option;
                }
                $type['options'] = $options;
                $types[] = $type;
            }
            $response = ['status' => 'success', 'types' => $types];
            break;

        case 'save_customs':
            // Aksi ini hanya untuk admin
            if (!$is_admin) throw new Exception("Unauthorized", 403);

            $data = json_decode(file_get_contents('php://input'), true);
            $conn->begin_transaction();
            try {
                foreach($data['types'] as $type) {
                    $current_type_id = $type['id'];
                    if ($type['id'] < 0) { // Tipe baru
                        $stmt = $conn->prepare("INSERT INTO custom_types (name) VALUES (?)");
                        $stmt->bind_param("s", $type['name']);
                        $stmt->execute();
                        $current_type_id = $stmt->insert_id;
                    } else { // Tipe yang sudah ada
                        $stmt = $conn->prepare("UPDATE custom_types SET name = ? WHERE id = ?");
                        $stmt->bind_param("si", $type['name'], $type['id']);
                        $stmt->execute();
                    }
                    
                    if (isset($type['options'])) {
                        foreach($type['options'] as $option) {
                            if ($option['id'] < 0) { // Opsi baru
                                $stmt = $conn->prepare("INSERT INTO custom_options (type_id, name, price) VALUES (?, ?, ?)");
                                $stmt->bind_param("isd", $current_type_id, $option['name'], $option['price']);
                            } else { // Opsi yang sudah ada
                                $stmt = $conn->prepare("UPDATE custom_options SET name = ?, price = ? WHERE id = ?");
                                $stmt->bind_param("sdi", $option['name'], $option['price'], $option['id']);
                            }
                            $stmt->execute();
                        }
                    }
                }
                $conn->commit();
                $response = ['status' => 'success', 'message' => 'Kustomisasi berhasil disimpan.'];
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;

        case 'get_product_customs':
            // Aksi ini dapat diakses oleh siapa saja (termasuk pelanggan untuk simulasi)
            $product_id = intval($_GET['product_id'] ?? 0);
            if ($product_id === 0) throw new Exception("Invalid product ID", 400);

            $sql = "SELECT t.id as type_id, t.name as type_name, o.id as option_id, o.name as option_name, o.price
                    FROM custom_types t
                    JOIN custom_options o ON t.id = o.type_id
                    JOIN product_custom_options pco ON o.id = pco.option_id
                    WHERE pco.product_id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $custom_data = [];
            while($row = $result->fetch_assoc()) {
                if (!isset($custom_data[$row['type_id']])) {
                    $custom_data[$row['type_id']] = [
                        'id' => $row['type_id'],
                        'name' => $row['type_name'],
                        'options' => []
                    ];
                }
                $custom_data[$row['type_id']]['options'][] = [
                    'id' => $row['option_id'],
                    'name' => $row['option_name'],
                    'price' => $row['price']
                ];
            }
            $response = ['status' => 'success', 'customizations' => array_values($custom_data)];
            break;
            
        case 'get_products_for_option':
            // Aksi ini hanya untuk admin
            if (!$is_admin) throw new Exception("Unauthorized", 403);

            $option_id = intval($_GET['option_id'] ?? 0);
            if ($option_id <= 0) {
                throw new Exception("Option ID tidak valid", 400);
            }

            // Ambil semua produk
            $products_sql = "SELECT id, tipe FROM katalog ORDER BY tipe ASC";
            $products_result = $conn->query($products_sql);
            $all_products = $products_result->fetch_all(MYSQLI_ASSOC);

            // Ambil ID produk yang sudah terkait dengan opsi ini
            $assoc_sql = "SELECT product_id FROM product_custom_options WHERE option_id = ?";
            $stmt = $conn->prepare($assoc_sql);
            $stmt->bind_param("i", $option_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $associated_product_ids = [];
            while ($row = $result->fetch_assoc()) {
                $associated_product_ids[] = $row['product_id'];
            }

            $response = [
                'status' => 'success',
                'all_products' => $all_products,
                'associated_ids' => $associated_product_ids
            ];
            break;
            
        case 'save_products_for_option':
            // Aksi ini hanya untuk admin
            if (!$is_admin) throw new Exception("Unauthorized", 403);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $option_id = intval($data['option_id'] ?? 0);
            $product_ids = $data['product_ids'] ?? [];

            if ($option_id <= 0) {
                throw new Exception("Option ID tidak valid", 400);
            }

            $conn->begin_transaction();
            try {
                // Hapus semua asosiasi lama untuk opsi ini
                $stmt_del = $conn->prepare("DELETE FROM product_custom_options WHERE option_id = ?");
                $stmt_del->bind_param("i", $option_id);
                $stmt_del->execute();

                // Masukkan asosiasi baru yang dipilih
                if (!empty($product_ids)) {
                    $stmt_ins = $conn->prepare("INSERT INTO product_custom_options (product_id, option_id) VALUES (?, ?)");
                    foreach ($product_ids as $product_id) {
                        $stmt_ins->bind_param("ii", $product_id, $option_id);
                        $stmt_ins->execute();
                    }
                }
                $conn->commit();
                $response = ['status' => 'success', 'message' => 'Asosiasi produk berhasil diperbarui.'];
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;
            
        default:
            throw new Exception("Invalid action", 400);
    }
} catch (Exception $e) {
    $code = $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($code);
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

echo json_encode($response);
$conn->close();
?>