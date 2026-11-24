<?php
/**
 * Configurações Gerais da Aplicação HairMatch
 * Sessões nativas, helpers de segurança, mensagens Flash e autenticação
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações do Sistema
define('APP_NAME', 'HairMatch');
define('APP_TAGLINE', 'O match perfeito para a saúde e beleza do seu cabelo');
define('APP_VERSION', '1.0.0');

// Detecção de URL base
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = rtrim($protocol . $host . $scriptDir, '/');
if (substr($baseUrl, -8) === '/backend') {
    $baseUrl = substr($baseUrl, 0, -8);
}
define('BASE_URL', $baseUrl);

// Requisita a conexão com o banco
require_once __DIR__ . '/bd.php';

/**
 * Define mensagem flash para exibição em TOAST na interface
 * Tipos suportados: success, error, warning, info
 */
function setFlash(string $type, string $message): void {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Recupera e consome as mensagens flash da sessão
 * @return array
 */
function getFlashes(): array {
    $flashes = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $flashes;
}

/**
 * Verifica se há usuário logado na sessão
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

/**
 * Retorna dados do usuário autenticado ou null
 */
function getCurrentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Retorna dados do perfil capilar ativo (da sessão ou do banco se logado)
 */
function getCurrentHairProfile(): ?array {
    if (!empty($_SESSION['hair_profile'])) {
        return $_SESSION['hair_profile'];
    }
    if (isLoggedIn() && !empty($_SESSION['user']['id'])) {
        require_once dirname(__DIR__) . '/backend/quiz.php';
        $profile = getUserHairProfile((int)$_SESSION['user']['id']);
        if ($profile) {
            $_SESSION['hair_profile'] = $profile;
            return $profile;
        }
    }
    return null;
}

// Inicializa a variável global $hairProfile para todas as views e scripts
$hairProfile = getCurrentHairProfile();

/**
 * Força autenticação. Redireciona para o login com Toast de erro se não estiver logado.
 */
function requireAuth(): void {
    if (!isLoggedIn()) {
        setFlash('error', 'Por favor, faça login para acessar essa funcionalidade.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Sanitiza saída HTML contra XSS
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Formata valores em Reais (BRL)
 */
function formatMoney(float $value): string {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

/**
 * Gera token CSRF para formulários
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida o token CSRF
 */
function validateCsrfToken(?string $token): bool {
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
