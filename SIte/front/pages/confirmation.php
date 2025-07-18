<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?>

<div id="confirmation" class="max-w-2xl mx-auto text-center py-20 px-4 hidden">
    <h1 class="text-3xl font-bold text-[#4CAF50] mb-4">
        🎉 Annonce publiée avec succès !
    </h1>
    <p class="text-[#212121] text-lg mb-6">
        Merci d’avoir utilisé notre service. Votre annonce est maintenant en ligne.
    </p>
    <a href="dashboard.php"
       class="inline-block bg-[#1976D2] hover:bg-blue-700 text-white font-medium px-6 py-3 rounded shadow">
        Retour au tableau de bord
    </a>
</div>

<div id="unauthorized"
     class="text-red-600 font-semibold text-center mt-12 hidden">
    Accès refusé. Cette page est réservée aux clients connectés.
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const API_BASE = "<?= BASE_API ?>";

    try {
        const res = await fetch(`${API_BASE}/get_session`, {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });
        const { user } = await res.json();

if (user && Array.isArray(user.roles) &&
    (user.roles.includes('Customer') || user.roles.includes('Seller'))) {
    document.getElementById('confirmation').classList.remove('hidden');
} else {
    document.getElementById('unauthorized').classList.remove('hidden');
}
    } catch (err) {
        console.error('Erreur API get_session :', err);
        document.getElementById('unauthorized').classList.remove('hidden');
    }
});
</script>

<?php include_once('../includes/footer.php'); ?>
