<?php
/**
 * Página de Login - HairMatch
 * Core Requirement: Fazer o login na plataforma
 */

$pageTitle = 'Entrar na Conta';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/backend/auth.php';

// Se já estiver logado, redireciona
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/perfil.php');
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $res = authLogin($email, $password);

    if ($res['success']) {
        setFlash('success', $res['message']);
        
        // Se tinha questionário pendente na sessão, salva no banco do usuário
        if (!empty($_SESSION['hair_profile']) && !isset($_SESSION['hair_profile']['id'])) {
            require_once __DIR__ . '/backend/quiz.php';
            saveHairProfile((int)$_SESSION['user']['id'], $_SESSION['hair_profile']);
        }

        header('Location: ' . BASE_URL . '/produtos.php');
        exit;
    } else {
        setFlash('error', $res['message']);
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-16 bg-slate-50 min-h-[80vh] flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 sm:p-10 rounded-3xl border border-slate-200/80 shadow-xl">
        
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-hair-600 to-hair-400 text-white flex items-center justify-center text-xl mx-auto shadow-md shadow-hair-500/20">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Acesse sua conta</h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Entre para salvar seu perfil capilar, acessar histórico e avaliar produtos.
            </p>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/login.php" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">E-mail</label>
                <div class="relative">
                    <input type="email" name="email" id="login-email" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="seu@email.com" class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-hair-500 focus:outline-none">
                    <i class="fa-solid fa-envelope absolute left-3 top-3.5 text-xs text-slate-400"></i>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Senha</label>
                <div class="relative">
                    <input type="password" name="password" id="login-password" required placeholder="••••••••" class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-hair-500 focus:outline-none">
                    <i class="fa-solid fa-key absolute left-3 top-3.5 text-xs text-slate-400"></i>
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-hair-600 hover:bg-hair-700 text-white font-bold text-sm shadow-lg shadow-hair-600/30 transition">
                Entrar
            </button>
        </form>

        <!-- Botão Rápido de Demonstração (Para testes fáceis pelo usuário/professor) -->
        <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-2">
            <div class="flex items-center justify-between font-bold">
                <span><i class="fa-solid fa-user-astronaut text-amber-600 mr-1"></i> Usuária de Teste Rápido:</span>
                <button type="button" onclick="document.getElementById('login-email').value='maria@hairmatch.com'; document.getElementById('login-password').value='senha123';" class="text-xs bg-amber-200 hover:bg-amber-300 px-2 py-0.5 rounded font-bold transition">
                    Preencher Dados
                </button>
            </div>
            <p class="text-[11px] text-amber-800">
                E-mail: <code>maria@hairmatch.com</code> | Senha: <code>senha123</code> (com perfil 3B Cacheado já ativo)
            </p>
        </div>

        <div class="text-center pt-2 border-t border-slate-100 text-xs text-slate-500">
            Ainda não tem conta?
            <a href="<?= BASE_URL ?>/cadastro.php" class="font-bold text-hair-600 hover:text-hair-700 underline ml-1">
                Cadastre-se grátis
            </a>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
