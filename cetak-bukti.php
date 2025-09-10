<?php
session_start();
require('fpdf.php'); // Memuat library FPDF

// Cek jika user sudah login
if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

// Cek jika ID transaksi ada di URL
$transaction_id = intval($_GET['id'] ?? 0);
if ($transaction_id === 0) {
    die("ID Transaksi tidak valid.");
}

$conn = new mysqli("localhost", "root", "", "mokobang");
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Ambil data transaksi lengkap
$sql = "SELECT t.*, u.username, u.email, k.tipe as product_name
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        JOIN katalog k ON t.product_id = k.id
        WHERE t.id = ? AND t.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $transaction_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$trx = $result->fetch_assoc();

if (!$trx || $trx['status'] !== 'paid') {
    die("Bukti pembayaran tidak ditemukan atau transaksi belum lunas.");
}

// =================================================================
// MULAI MEMBUAT PDF
// =================================================================

class PDF extends FPDF
{
    // Header Halaman
    function Header()
    {
        // Logo
        $this->Image('image/logo.png', 10, 6, 30);
        // Font
        $this->SetFont('Arial', 'B', 15);
        // Pindah ke kanan
        $this->Cell(80);
        // Judul
        $this->Cell(30, 10, 'BUKTI PEMBAYARAN', 0, 0, 'C');
        // Pindah baris
        $this->Ln(20);
        // Garis bawah
        $this->Line(10, 30, 200, 30);
        $this->Ln(5);
    }

    // Footer Halaman
    function Footer()
    {
        // Posisi 1.5 cm dari bawah
        $this->SetY(-15);
        // Font
        $this->SetFont('Arial', 'I', 8);
        // Teks
        $this->Cell(0, 10, 'Dokumen ini dicetak dari sistem Showcase Rumah Panggung Desa Mokobang pada ' . date('d/m/Y H:i'), 0, 0, 'C');
    }

    // Tabel info
    function InfoSection($title, $data)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, $title, 0, 1, 'L');
        $this->SetFont('Arial', '', 11);
        foreach ($data as $key => $value) {
            $this->Cell(50, 7, $key, 0, 0);
            $this->Cell(5, 7, ':', 0, 0, 'C');
            $this->MultiCell(0, 7, $value, 0, 'L');
        }
        $this->Ln(5);
    }

    // Tabel Rincian Biaya
    function BillingTable($header, $data)
    {
        $this->SetFillColor(230, 230, 230);
        $this->SetFont('Arial', 'B', 12);
        // Header
        $w = array(130, 60);
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 8, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        // Data
        $this->SetFont('Arial', '', 12);
        foreach ($data as $row) {
            $this->Cell($w[0], 7, $row[0], 'LR', 0, 'L');
            $this->Cell($w[1], 7, 'Rp ' . number_format($row[1], 0, ',', '.'), 'LR', 0, 'R');
            $this->Ln();
        }
        // Garis penutup
        $this->Cell(array_sum($w), 0, '', 'T');
        $this->Ln();
    }
}

// Inisiasi objek PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 11);

// Informasi Pembayaran
$info_pembayaran = [
    'Nomor Billing' => $trx['billing_number'],
    'Tanggal Pembayaran' => date('d F Y', strtotime($trx['updated_at'])),
    'Status' => 'LUNAS'
];
$pdf->InfoSection('Detail Pembayaran', $info_pembayaran);

// Informasi Pembeli
$info_pembeli = [
    'Nama' => $trx['username'],
    'Email' => $trx['email']
];
$pdf->InfoSection('Dibayarkan Oleh', $info_pembeli);

// Rincian Pembelian
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Rincian Tagihan', 0, 1, 'L');
$header = array('Deskripsi', 'Jumlah');
$data_billing = [
    [$trx['product_name'], $trx['deal_price']],
    ['Ongkos Kirim', $trx['shipping_cost']],
];
$pdf->BillingTable($header, $data_billing);

// Total
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(130, 8, 'TOTAL PEMBAYARAN', 1, 0, 'R');
$pdf->Cell(60, 8, 'Rp ' . number_format($trx['total_bill'], 0, ',', '.'), 1, 1, 'R');

// Tanda LUNAS
$pdf->SetY(150);
$pdf->SetFont('Arial', 'B', 48);
$pdf->SetTextColor(40, 167, 69);
$pdf->Cell(0,10,'LUNAS',0,1,'C');


// *** PERUBAHAN DI SINI ***
// Output PDF ke browser untuk diunduh langsung
// Parameter pertama diubah dari 'I' (Inline/Preview) menjadi 'D' (Download)
$pdf->Output('D', 'bukti_pembayaran_' . $trx['billing_number'] . '.pdf');