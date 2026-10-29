<?php
function loginAkun(PDO $pdo, string $no_hp, string $password) {
    $sql = "SELECT * FROM pembeli WHERE no_hp = :no_hp";
    $params = [':no_hp' => $no_hp];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    
    $pembeli = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pembeli) {
        return false;
    }

    if (!password_verify($password, $pembeli['password_hash'])) {
        return false;
    }

    return $pembeli;
}