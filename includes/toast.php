<?php
/**
 * Renderizador de Toasts da Sessão - HairMatch
 * Executa as mensagens flash adicionadas via setFlash()
 */

$flashes = getFlashes();
?>
<div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full px-4"></div>

<?php if (!empty($flashes)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($flashes as $flash): ?>
    if (window.AppToast) {
        window.AppToast.show(<?= json_encode($flash['message']) ?>, <?= json_encode($flash['type']) ?>);
    }
    <?php endforeach; ?>
});
</script>
<?php endif; ?>
