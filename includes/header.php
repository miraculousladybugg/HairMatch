<?php
/**
 * Header Global - HairMatch
 * MobileFirst, Tailwind CSS v3, FontAwesome, Fonts e Scripts Globais
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/backend/cart.php';

$pageTitle = isset($pageTitle) ? $pageTitle . ' | ' . APP_NAME : APP_NAME . ' — Descubra o Produto Ideal para o Seu Cabelo';
$cartCount = getCartTotalItemCount();
$user = getCurrentUser();
$hairProfile = $hairProfile ?? getCurrentHairProfile();
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="Descubra os produtos ideais para o seu tipo de cabelo através do nosso diagnóstico capilar inteligente baseado em avaliações de consumidores reais e especificações de especialistas.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        hair: {
                            50: '#fdf2f8',
                            100: '#fce7f3',
                            200: '#fbcfe8',
                            300: '#f472b6',
                            400: '#ec4899',
                            500: '#db2777',
                            600: '#be185d',
                            700: '#9d174d',
                            800: '#831843',
                            900: '#500724',
                        },
                        amber: {
                            500: '#f59e0b',
                            600: '#d97706',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                    }
                }
            }
        }
    </script>

    <!-- Estilos Customizados -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">

    <!-- Scripts Globais de Notificação e Modais Nativos -->
    <script src="<?= BASE_URL ?>/assets/js/toast.js" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/modal.js" defer></script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-full flex flex-col font-sans antialiased selection:bg-pink-100 selection:text-pink-900">

<?php 
// Inclui o container de toasts e renderiza mensagens da sessão
require_once __DIR__ . '/toast.php';
// Inclui a navbar principal
require_once __DIR__ . '/navbar.php';
?>
<main class="flex-grow">
