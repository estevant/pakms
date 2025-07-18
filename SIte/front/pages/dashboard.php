<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?> <h2 id="welcome" class="text-2xl font-bold mb-4"></h2>
<div id="dashboard-content"></div>
<div id="error-msg" class="text-red-600 text-center text-sm font-medium mt-6"></div>


<div id="tutorial-overlay" class="fixed inset-0 bg-black bg-opacity-70 z-50 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded-xl shadow-xl max-w-xl text-center">
        <h2 class="text-2xl font-bold mb-4">Bienvenue sur EcoDeli !</h2>
        <p class="mb-4">Voici un rapide tutoriel pour découvrir votre espace personnel.</p>
        <ul class="text-left list-disc list-inside mb-4">
            <li>Créez vos annonces de livraison ou de prestation.</li>
            <li>Suivez vos commandes et livraisons.</li>
            <li>Gérez vos paiements et justificatifs.</li>
        </ul>
        <button onclick="closeTutorial()" class="bg-blue-600 text-white px-4 py-2 rounded">Terminer le tutoriel</button>
    </div>
</div>
<script>
const API_BASE = "<?= BASE_API ?>";

// Fonctions pour le formlaire de nouveau type de prestation supprimées

async function closeTutorial() {
    try {
        await fetch(`${API_BASE}/mark_tutorial_done`, {
            method: 'POST',
            credentials: 'include'
        });
    } catch (e) {
        console.error('Erreur lors de la désactivation du tutoriel');
    }
    document.getElementById('tutorial-overlay').classList.add('hidden');
}
async function maybeShowTutorial() {
    try {
        const res = await fetch(`${API_BASE}/should_show_tutorial`, {
            method: 'GET',
            credentials: 'include'
        });
        const data = await res.json();
        if (data.show === true) {
            document.getElementById('tutorial-overlay').classList.remove('hidden');
        }
    } catch (e) {
        console.error('Erreur lors de la vérification du tutoriel');
    }
}
async function loadDashboard() {
    try {
        const res = await fetch(`${API_BASE}/get_session`, {
            method: 'GET',
            credentials: 'include',
            headers: {
                'Accept': 'application/json'
            }
        });
        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            document.getElementById('error-msg').innerText = "Réponse API inattendue.";
            return;
        }
        const result = await res.json();
        const user = result.user;
        if (!user || !user.user_id) {
            window.location.replace('index.php');
            return;
        }
        const {
            first_name,
            last_name,
            email,
            roles
        } = user;
        document.getElementById('welcome').innerText = `Bienvenue, ${first_name} ${last_name}`;
        let content = '';
        if (roles.includes('Admin')) {
            window.location.href = '/PA2/SIte/back/dashboard_admin.php';
            return;
        }
        if (roles.includes('Customer')) {
            content += `
            <div class="bg-white rounded shadow p-4 mb-4">
                <h3 class="text-xl font-semibold mb-2">Espace client</h3>
                <ul class="list-disc list-inside">
                    <li><a href="create_annonce.php" class="text-blue-600">➕ Créer une annonce</a></li>
                    <li><a href="mes_annonces.php" class="text-blue-600">📋 Mes annonces</a></li>
                    <li><a href="mes_annonces_terminees.php" class="text-blue-600">✅ Annonces terminées</a></li>
                    <li><a href="liste_prestation.php" class="text-blue-600">💬 Prestations Disponibles</a></li>
                    <li><a href="mes_reservations_client.php" class="text-blue-600">💬 Prestations réservées</a></li>
                    <li><a href="mes_factures.php" class="text-blue-600">💳 Mes factures</a></li>
                </ul>
            </div>`;
        }
        if (roles.includes('Deliverer')) {
            content += `
            <div class="bg-white rounded shadow p-4 mb-4">
                <h3 class="text-xl font-semibold mb-2">Espace livreur</h3>
                <ul class="list-disc list-inside">
                    <li><a href="livraisons.php" class="text-green-600">📦 Livraisons disponibles</a></li>
                    <li><a href="mes_livraisons.php" class="text-green-600">🚚 Mes livraisons en cours</a></li>
                    <li><a href="livraisons_terminees.php" class="text-green-600">📁 Livraisons terminées</a></li>
                    <li><a href="mes_justificatifs.php" class="text-green-600">🧾 Mes justificatifs</a></li>
                    <li><a href="mes_trajets.php" class="text-green-600">🛣️ Mes trajets</a></li>
                    <li><a href="notifications.php" class="text-green-600">🔔 Mes notifications</a></li>
                    <li><a href="calendrier_livreur.php" class="text-green-600">🗓 Mon planning</a></li>
                    <li><a href="wallet.php" class="text-green-600">💰 Mon portefeuille</a></li>
                    <li><a href="mon_qrcode.php" class="text-green-600">🆔 Mon QR code livreur</a></li>
                </ul>
            </div>`;
        }
        if (roles.includes('Seller')) {
            content += `
            <div class="bg-white rounded shadow p-4 mb-4">
                <h3 class="text-xl font-semibold mb-2">Espace commerçant</h3>
                <ul class="list-disc list-inside">
                    <li><a href="create_annonce.php" class="text-blue-600">➕ Créer une annonce</a></li>
                    <li><a href="mes_annonces.php" class="text-blue-600">📋 Mes annonces</a></li>
                    <li><a href="mes_factures.php" class="text-purple-600">🧾 Mes factures</a></li>
                    <li><a href="mes_contrats.php" class="text-purple-600">📋 Mes contrats</a></li>
                </ul>
            </div>`;
        }
        if (roles.includes('ServiceProvider')) {
            content += `
            <div class="bg-white rounded shadow p-4 mb-4">
                <h3 class="text-xl font-semibold mb-2">Espace prestataire</h3>
                <div class="mb-4">
                    <!-- Je supprime tout bouton ou lien qui permettait d'afficher le formulaire de proposition de nouveau type de prestation -->
                    <!-- (aucun appel à showPrestationForm ou bouton 'Proposer un nouveau type' ne doit rester) -->
                </div>
                <ul class="list-disc list-inside">
                    <li><a href="mes_prestations.php" class="text-blue-600"> Mes prestations</a></li>
                    <li><a href="paiement_prestataires.php" class="text-blue-600"> Mes paiements</a></li>
                    <li><a href="mes_evaluations_prestataire.php" class="text-blue-600"> Mes évaluations</a></li>
                    <li><a href="mes_justificatifs.php" class="text-blue-600"> Mes justificatifs</a></li>
                    <li><a href="suivi_types_prestations.php" class="text-blue-600"> Suivi de mes demandes de type</a></li>
                    <li><a href="wallet.php" class="text-blue-600">💰 Mon portefeuille</a></li>
                </ul>
            </div>`;
        }
        if (roles.includes('Customer') || roles.includes('Deliverer')) {
            content += `
            <div class="bg-white rounded shadow p-4 mb-4">
                <h3 class="text-xl font-semibold mb-2">🎭 Gestion des rôles</h3>
                <ul class="list-disc list-inside">
                    <li><a href="demande_role.php" class="text-blue-600">➕ Demander un rôle supplémentaire</a></li>
                    <li><span class="text-gray-600">Devenez ${roles.includes('Customer') ? 'livreur' : 'client'}</span></li>
                </ul>
            </div>`;
        }

        document.getElementById('dashboard-content').innerHTML = content;
        await maybeShowTutorial();
    } catch (err) {
        document.getElementById('error-msg').innerText = 'Erreur lors du chargement de votre tableau de bord.';
        console.error(err);
    }
}
loadDashboard();
</script> <?php include_once('../includes/footer.php'); ?>