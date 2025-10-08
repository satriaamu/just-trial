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
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => "Koneksi gagal: " . $e->getMessage()]);
    exit();
}

$action = $_GET['action'] ?? '';
$is_admin = isset($_SESSION['admin_id']);

try {
    switch ($action) {
        case 'get_all_customs':
            if (!$is_admin) throw new Exception("Unauthorized", 403);
            
            $types_sql = "SELECT * FROM custom_types ORDER BY name ASC";
            $types_stmt = $pdo->query($types_sql);
            $types = [];
            while ($type = $types_stmt->fetch(PDO::FETCH_ASSOC)) {
                $options_sql = "SELECT * FROM custom_options WHERE type_id = :type_id ORDER BY name ASC";
                $options_stmt = $pdo->prepare($options_sql);
                $options_stmt->execute([':type_id' => $type['id']]);
                $options = $options_stmt->fetchAll(PDO::FETCH_ASSOC);
                $type['options'] = $options;
                $types[] = $type;
            }
            $response = ['status' => 'success', 'types' => $types];
            break;

        case 'save_customs':
            if (!$is_admin) throw new Exception("Unauthorized", 403);

            $data = json_decode(file_get_contents('php://input'), true);
            $pdo->beginTransaction();
            try {
                foreach($data['types'] as $type) {
                    $current_type_id = $type['id'];
                    if ($type['id'] < 0) { // Tipe baru
                        $stmt = $pdo->prepare("INSERT INTO custom_types (name) VALUES (?)");
                        $stmt->execute([$type['name']]);
                        $current_type_id = $pdo->lastInsertId();
                    } else { // Tipe yang sudah ada
                        $stmt = $pdo->prepare("UPDATE custom_types SET name = ? WHERE id = ?");
                        $stmt->execute([$type['name'], $type['id']]);
                    }
                    
                    if (isset($type['options'])) {
                        foreach($type['options'] as $option) {
                            if ($option['id'] < 0) { // Opsi baru
                                $stmt = $pdo->prepare("INSERT INTO custom_options (type_id, name, price) VALUES (?, ?, ?)");
                                $stmt->execute([$current_type_id, $option['name'], $option['price']]);
                            } else { // Opsi yang sudah ada
                                $stmt = $pdo->prepare("UPDATE custom_options SET name = ?, price = ? WHERE id = ?");
                                $stmt->execute([$option['name'], $option['price'], $option['id']]);
                            }
                        }
                    }
                }
                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Kustomisasi berhasil disimpan.'];
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'get_product_customs':
            $product_id = intval($_GET['product_id'] ?? 0);
            if ($product_id === 0) throw new Exception("Invalid product ID", 400);

            $sql = "SELECT t.id as type_id, t.name as type_name, o.id as option_id, o.name as option_name, o.price
                    FROM custom_types t
                    JOIN custom_options o ON t.id = o.type_id
                    JOIN product_custom_options pco ON o.id = pco.option_id
                    WHERE pco.product_id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$product_id]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $custom_data = [];
            foreach($result as $row) {
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
            if (!$is_admin) throw new Exception("Unauthorized", 403);

            $option_id = intval($_GET['option_id'] ?? 0);
            if ($option_id <= 0) {
                throw new Exception("Option ID tidak valid", 400);
            }

            // Ambil semua produk
            $products_sql = "SELECT id, tipe FROM katalog ORDER BY tipe ASC";
            $all_products = $pdo->query($products_sql)->fetchAll(PDO::FETCH_ASSOC);

            // Ambil ID produk yang sudah terkait dengan opsi ini
            $assoc_sql = "SELECT product_id FROM product_custom_options WHERE option_id = ?";
            $stmt = $pdo->prepare($assoc_sql);
            $stmt->execute([$option_id]);
            $associated_product_ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            $response = [
                'status' => 'success',
                'all_products' => $all_products,
                'associated_ids' => $associated_product_ids
            ];
            break;
            
        case 'save_products_for_option':
            if (!$is_admin) throw new Exception("Unauthorized", 403);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $option_id = intval($data['option_id'] ?? 0);
            $product_ids = $data['product_ids'] ?? [];

            if ($option_id <= 0) {
                throw new Exception("Option ID tidak valid", 400);
            }

            $pdo->beginTransaction();
            try {
                // Hapus semua asosiasi lama untuk opsi ini
                $stmt_del = $pdo->prepare("DELETE FROM product_custom_options WHERE option_id = ?");
                $stmt_del->execute([$option_id]);

                // Masukkan asosiasi baru yang dipilih
                if (!empty($product_ids)) {
                    $stmt_ins = $pdo->prepare("INSERT INTO product_custom_options (product_id, option_id) VALUES (?, ?)");
                    foreach ($product_ids as $product_id) {
                        $stmt_ins->execute([$product_id, $option_id]);
                    }
                }
                $pdo->commit();
                $response = ['status' => 'success', 'message' => 'Asosiasi produk berhasil diperbarui.'];
            } catch (Exception $e) {
                $pdo->rollBack();
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
// Tidak perlu close koneksi PDO secara eksplisit
?>