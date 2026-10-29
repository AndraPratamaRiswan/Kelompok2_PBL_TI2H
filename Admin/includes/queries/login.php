<?php
function loginAkun(PDO $pdo, string $username, string $password) {
    $sql = "SELECT * FROM admin WHERE username = :username";
    $params = [':username' => $username];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        return false;
    }

    if (!password_verify($password, $admin['password_hash'])) {
        return false;
    }

    return $admin;
}