<?php
/**
 * Página de Cadastro - HairMatch
 * Core Requirement: Fazer o login na plataforma (Registro)
 */

$pageTitle = 'Criar Nova Conta';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/backend/auth.php';

// Se já estiver logado, redireciona
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/perfil.php');
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $phone = $_POST['phone'] ?? '';

    $res = authRegister($name, $email, $password, $phone);

    if ($res['success']) {
        setFlash('success', $res['message']);

        // Se tinha questionário pendente na sessão, salva no banco
        if (!empty($_SESSION['hair_profile']) && !isset($_SESSION['hair_profile']['id'])) {
            require_once __DIR__ . '/backend/quiz.php';
            saveHairProfile((int)$res['userId'], $_SESSION['hair_profile']);
            header('Location: ' . BASE_URL . '/produtos.php');
            exit;
        }

        // Caso ainda não tenha feito o teste, convida para o quiz
        header('Location: ' . BASE_URL . '/quiz.php');
        exit;
    } else {
        setFlash('error', $res['message']);
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-16 bg-slate-50 min-h-[85vh] flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 sm:p-10 rounded-3xl border border-slate-200/80 shadow-xl">
        
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-hair-600 to-hair-400 text-white flex items-center justify-center text-xl mx-auto shadow-md shadow-hair-500/20">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Crie sua conta HairMatch</h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Personalize sua rotina capilar e descubra os produtos com o maior match.
            </p>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/cadastro.php" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nome Completo</label>
                <div class="relative">
                    <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>" placeholder="Ex: Juliana Mendes" class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-hair-500 focus:outline-none">
                    <i class="fa-solid fa-user absolute left-3 top-3.5 text-xs text-slate-400"></i>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">E-mail</label>
                <div class="relative">
                    <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="seu@email.com" class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-hair-500 focus:outline-none">
                    <i class="fa-solid fa-envelope absolute left-3 top-3.5 text-xs text-slate-400"></i>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Telefone / WhatsApp (Opcional)</label>
                <div class="relative">
                    <input type="tel" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="(11) 99999-9999" class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-hair-500 focus:outline-none">
                    <i class="fa-solid fa-phone absolute left-3 top-3.5 text-xs text-slate-400"></i>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Senha (Mínimo 6 dígitos)</label>
                <div class="relative">
                    <input type="password" name="password" required placeholder="••••••••" class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-hair-500 focus:outline-none">
                    <i class="fa-solid fa-key absolute left-3 top-3.5 text-xs text-slate-400"></i>
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-hair-600 hover:bg-hair-700 text-white font-bold text-sm shadow-lg shadow-hair-600/30 transition">
                Criar Minha Conta
            </button>
        </form>

        <div class="text-center pt-2 border-t border-slate-100 text-xs text-slate-500">
            Já possui uma conta?
            <a href="<?= BASE_URL ?>/login.php" class="font-bold text-hair-600 hover:text-hair-700 underline ml-1">
                Fazer login
            </a>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
