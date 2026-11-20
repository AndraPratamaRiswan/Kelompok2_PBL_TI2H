<?php

// detail_pesanan ikut terhapus otomatis (ON DELETE CASCADE)
function psnHapus(PDO $pdo, int $idPesanan): bool
{
    try {
        $pdo->prepare('CALL sp_hapus_pesanan(:id)')->execute([':id' => $idPesanan]);
        return true;
    } catch (PDOException $e) {
        if ($e->getCode() === 'P0002') { // dari RAISE EXCEPTION di prosedur
            return false;
        }
        throw $e;
    }
}