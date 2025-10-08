<?php
session_start();
// Pastikan path ke FPDF benar
require_once('fpdf.php');

// --- Kode otentikasi dan query database ---
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

$transaction_id = intval($_GET['id'] ?? 0);
if ($transaction_id === 0) {
    die("ID Transaksi tidak valid.");
}

// --- Koneksi Database menggunakan PDO ---
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

// --- Pengambilan data transaksi menggunakan PDO ---
$sql = "SELECT t.*, u.username, u.email, k.tipe as product_name
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        JOIN katalog k ON t.product_id = k.id
        WHERE t.id = :transaction_id";

$params = [':transaction_id' => $transaction_id];

if (!$is_admin) {
    $sql .= " AND t.user_id = :user_id";
    $params[':user_id'] = $user_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$trx = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trx || $trx['status'] !== 'paid') {
    die("Bukti pembayaran tidak ditemukan atau transaksi belum lunas.");
}

// --- Kelas PDF (FPDF) ---
class PDF extends FPDF
{
    function Header()
    {
        $this->Image('image/logo.png', 10, 6, 30);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 10, 'BUKTI PEMBAYARAN', 0, 0, 'C');
        $this->Ln(20);
        $this->Line(10, 30, 200, 30);
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Dokumen ini valid dan dicetak dari sistem Showcase Rumah Panggung Desa Mokobang.', 0, 0, 'C');
    }

    function InfoSection($title, $data)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, $title, 0, 1, 'L');
        $this->SetFont('Arial', '', 11);
        foreach ($data as $key => $value) {
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(50, 7, $key, 0, 0);
            $this->SetFont('Arial', '', 10);
            $this->MultiCell(0, 7, ': ' . $value, 0, 'L');
        }
        $this->Ln(4);
    }

    function BillingTable($header, $data)
    {
        $this->SetFillColor(230, 230, 230);
        $this->SetFont('Arial', 'B', 11);
        $w = array(130, 60);
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 8, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        $this->SetFont('Arial', '', 10);
        foreach ($data as $row) {
            $this->Cell($w[0], 7, $row[0], 'LR', 0, 'L');
            $this->Cell($w[1], 7, 'Rp ' . number_format($row[1], 0, ',', '.'), 'LR', 0, 'R');
            $this->Ln();
        }
        $this->Cell(array_sum($w), 0, '', 'T');
        $this->Ln();
    }
}

// --- Pembuatan PDF ---
$pdf = new PDF();
$pdf->AddPage();

// Informasi Pembayaran
$info_pembayaran = [
    'Nomor Tagihan' => $trx['billing_number'],
    'Nomor Virtual Account' => $trx['virtual_account'],
    'Tanggal Transaksi' => date('d F Y, H:i', strtotime($trx['updated_at'])),
];
$pdf->InfoSection('Detail Transaksi', $info_pembayaran);

// Informasi Pembeli
$info_pembeli = [
    'Nama Pelanggan' => $trx['username'],
    'Alamat Pengiriman' => $trx['shipping_address'] ?? 'Tidak ada data.'
];
$pdf->InfoSection('Data Pelanggan', $info_pembeli);

// Rincian Pembelian
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Rincian Pembayaran', 0, 1, 'L');
$header = array('Deskripsi', 'Jumlah');
$data_billing = [
    [$trx['product_name'], $trx['deal_price']],
    ['Ongkos Kirim', $trx['shipping_cost']]
];
$pdf->BillingTable($header, $data_billing);

// Total
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(130, 8, 'TOTAL PEMBAYARAN', 1, 0, 'R');
$pdf->Cell(60, 8, 'Rp ' . number_format($trx['total_bill'], 0, ',', '.'), 1, 1, 'R');
$pdf->Ln(5);

// Detail kustomisasi dan catatan
$custom_details = json_decode($trx['customization_details'] ?? '[]', true);
if (is_array($custom_details) && !empty($custom_details)) {
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 7, 'Detail Kustomisasi:', 0, 1);
    $pdf->SetFont('Arial', '', 10);
    $custom_str = [];
    foreach($custom_details as $item) {
        $custom_str[] = $item['name'];
    }
    $pdf->MultiCell(0, 5, '- ' . implode("\n- ", $custom_str), 0, 'L');
    $pdf->Ln(3);
}

if (!empty($trx['negotiation_notes'])) {
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 7, 'Catatan Tambahan:', 0, 1);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(0, 5, $trx['negotiation_notes'], 0, 'L');
    $pdf->Ln(5);
}

// Tanda LUNAS
$pdf->SetY(-80);
$pdf->SetFont('Arial', 'B', 48);
$pdf->SetTextColor(40, 167, 69);
$pdf->Cell(0,10,'LUNAS',0,1,'C');

// Output PDF
$pdf->Output('D', 'bukti_pembayaran_' . $trx['billing_number'] . '.pdf');
?>