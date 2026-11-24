<?php
/**
 * Backend de Autenticação - HairMatch
 * Login, Cadastro, Sessão Nativa e Logout
 */

require_once dirname(__DIR__) . '/config/config.php';

/**
 * Autentica o usuário com email e senha
 */
function authLogin(string $email, string $password): array {
    $email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));
    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Por favor, informe seu e-mail e senha.'];
    }

    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, name, email, password, phone FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'E-mail ou senha incorretos.'];
    }

    // Salva na sessão nativa do PHP
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'phone' => $user['phone'] ?? ''
    ];

    // Carrega perfil capilar na sessão se existir
    $stmtProfile = $pdo->prepare("SELECT * FROM hair_profiles WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmtProfile->execute([$user['id']]);
    $profile = $stmtProfile->fetch();
    if ($profile) {
        if (!is_array($profile['problems'])) {
            $profile['problems'] = json_decode($profile['problems'], true) ?: explode(',', $profile['problems']);
        }
        $_SESSION['hair_profile'] = $profile;
    }

    return ['success' => true, 'message' => 'Login realizado com sucesso! Seja bem-vindo(a), ' . $user['name'] . '.'];
}

/**
 * Registra novo usuário
 */
function authRegister(string $name, string $email, string $password, ?string $phone = null): array {
    $name = trim($name);
    $email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));

    if (empty($name) || mb_strlen($name) < 3) {
        return ['success' => false, 'message' => 'O nome deve ter no mínimo 3 caracteres.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Por favor, forneça um endereço de e-mail válido.'];
    }

    if (empty($password) || mb_strlen($password) < 6) {
        return ['success' => false, 'message' => 'A senha deve conter no mínimo 6 caracteres.'];
    }

    $pdo = getDBConnection();

    // Verifica duplicidade de e-mail
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $checkStmt->execute([$email]);
    if ($checkStmt->fetch()) {
        return ['success' => false, 'message' => 'Este e-mail já está cadastrado. Faça login ou use outro e-mail.'];
    }

    // Hash seguro com BCRYPT
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)");
    $insertStmt->execute([$name, $email, $hashedPassword, $phone]);

    $newUserId = (int)$pdo->lastInsertId();

    // Autentica automaticamente
    $_SESSION['user'] = [
        'id' => $newUserId,
        'name' => $name,
        'email' => $email,
        'phone' => $phone ?? ''
    ];

    return ['success' => true, 'message' => 'Conta criada com sucesso! Responda o questionário para achar o produto ideal.', 'userId' => $newUserId];
}

/**
 * Encerra a sessão
 */
function authLogout(): void {
    unset($_SESSION['user']);
    unset($_SESSION['hair_profile']);
    session_regenerate_id(true);
}
