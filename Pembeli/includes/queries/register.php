<?php
function registerAkun(PDO $pdo, string $no_hp, string $password) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO pembeli( password_hash, no_hp) VALUES ( :password_hash, :no_hp)";
    $params = [];

    
    $params[":password_hash"] = $password_hash;
    $params[":no_hp"] = $no_hp;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return true;
        } 
    catch (PDOException $e) {
        if ($e->getCode() === '23505') {
        return false; // username sudah dipakai
    }
        throw $e; // error lain, biarkan naik ke atas
    }
}