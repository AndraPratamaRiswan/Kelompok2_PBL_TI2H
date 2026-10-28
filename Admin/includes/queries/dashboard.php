<?php
function tampilDaftarPesanan(PDO $pdo){
    $sql = "SELECT id_pesanan, 
    'Pesanan '|| nama_pembeli as nama_pemesan, 
    tanggal_dipesan, 
    tanggal_pengiriman, 
    status_pesanan, 
    status_pembayaran FROM pesanan
    ORDER BY tanggal_dipesan DESC";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}