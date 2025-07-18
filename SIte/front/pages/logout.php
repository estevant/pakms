<?php
include_once('../includes/api_path.php');
?>
<!DOCTYPE html>
<html lang="fr">

<script>
fetch("<?= BASE_API ?>/logout", {
    method: 'POST',
    credentials: 'include'
}).then(() => {
    document.cookie = 'laravel_session=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    document.cookie = 'XSRF-TOKEN=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    window.location.href = 'index.php';
});
</script>

<body>
    <script>
        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
            return match ? decodeURIComponent(match[1]) : null;
        }
        (async () => {
            const API_BASE = "<?= BASE_API ?>";
            const xsrfToken = getCookie('XSRF-TOKEN');
            try {
                const res = await fetch(`${API_BASE}/logout`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'X-XSRF-TOKEN': xsrfToken
                    }
                });
                if (res.ok) {
                    window.location.href = 'index.php';
                } else {
                    console.error('Échec de la déconnexion, statut HTTP', res.status);
                }
            } catch (err) {
                console.error('Erreur réseau lors de la déconnexion :', err);
            }
        })();
    </script>
</body>

</html>