<?php
require_once __DIR__ . '/../Config/koneksi.php';

$database = new Database();
$koneksi = $database->getConnection();

$id = $_GET['id'] ?? 0;

if ($id > 0) {
    $query = "DELETE FROM pembayaran WHERE id_pembayaran = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: index.php");
exit;
?>