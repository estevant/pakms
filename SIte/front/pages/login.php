<?php
session_start();
require_once('../includes/header.php');
require_once('../includes/api_path.php');

if (isset($_SESSION['user_id'])) {
    $roles = $_SESSION['roles'] ?? [];
    if (in_array('Admin', $roles)) {
        header('Location: /PA2/SIte/back/dashboard_admin.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}
?> <h2 class="text-2xl font-bold mb-4">Connexion</h2>
<form id="login-form" class="space-y-4 max-w-md mx-auto">
    <label class="block">Email : <input type="email" name="email" required class="input w-full">
    </label>
    <label class="block">Mot de passe : <input type="password" name="password" required class="input w-full">
    </label>
    <div class="flex items-center space-x-2">
        <input type="checkbox" id="remember" name="remember" class="accent-blue-600">
        <label for="remember" class="text-sm">Se souvenir de moi</label>
    </div>
    <div class="text-sm">
        <a href="forgot_password.php" class="text-blue-600 hover:underline">Mot de passe oublié ?</a>
    </div>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded w-full">Se connecter</button>
</form>
<div id="login-response" class="mt-4 text-center text-sm font-medium"></div>
<script>
document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const payload = {
        email: form.email.value,
        password: form.password.value,
        remember: form.remember.checked
    };
    const API_URL = "<?= BASE_API ?>/login";
    try {
        const res = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload),
            credentials: 'include'
        });
        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const text = await res.text();
            console.warn('Réponse non JSON :', text);
            throw new Error('Réponse non JSON : ' + text);
        }
        const result = await res.json();
        const msg = document.getElementById('login-response');
        if (result.error) {
            msg.innerText = result.error;
            msg.className = 'text-red-600';
        } else if (result.success) {
            if (result.user.is_validated === 0) {
                msg.innerText = 'Votre compte est en attente de validation.';
                msg.className = 'text-yellow-600';
                setTimeout(() => window.location.href = 'en_attente_validation.php', 1000);
            } else {
                msg.innerText = 'Connexion réussie.';
                msg.className = 'text-green-600';
                const roles = result.user.roles || [];
                if (roles.includes('Admin')) {
                    setTimeout(() => window.location.href = '/PA2/SIte/back/dashboard_admin.php', 1000);
                } else {
                    setTimeout(() => window.location.href = 'dashboard.php', 1000);
                }
                setTimeout(() => window.location.href = 'dashboard.php', 1000);
            }
        } else {
            msg.innerText = result.message || 'Erreur inconnue.';
            msg.className = 'text-red-600';
        }
    } catch (err) {
        const msg = document.getElementById('login-response');
        msg.innerText = 'Erreur lors de la connexion.';
        msg.className = 'text-red-600';
        console.error(err);
    }
});
</script> <?php
            require_once('../includes/footer.php');
            ?>