<?php
/**
 * Encerramento de Sessão (Logout) - HairMatch
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/backend/auth.php';

authLogout();
setFlash('info', 'Você saiu da sua conta com segurança. Até logo!');
header('Location: ' . BASE_URL . '/index.php');
exit;
