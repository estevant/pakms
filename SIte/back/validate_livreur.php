<?php
require_once 'includes/admin_header.php';
require_once 'includes/api_path.php';

if (!isset($_GET['id'])) {
    header('Location: livreurs.php?error=ID manquant');
    exit();
}

$id = intval($_GET['id']);
?>

<script>
const id = <?= $id ?>;
const API_BASE = "<?= BASE_API ?>";

(async () => {
    try {
        const res = await fetch(`${API_BASE}/admin/livreurs/valider`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id_utilisateur: id }),
            credentials: 'include'
        });

        const result = await res.json();
        if (result.success) {
            window.location.href = 'livreurs.php?success=' + encodeURIComponent(result.message);
        } else {
            window.location.href = 'livreurs.php?error=' + encodeURIComponent(result.message);
        }
    } catch (e) {
        window.location.href = 'livreurs.php?error=' + encodeURIComponent("Erreur lors de la validation.");
    }
})();
</script>
