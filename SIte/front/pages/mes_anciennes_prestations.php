<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?>

<h2 class="text-2xl font-bold mb-4">Mes prestations terminées</h2>

<a href="mes_prestations.php" class="inline-block bg-blue-600 text-white px-4 py-2 rounded mb-4">
    ← Retour à mes prestations à venir
</a>

<table class="table-auto w-full border-collapse border border-gray-300">
    <thead class="bg-gray-100">
        <tr>
            <th class="border px-4 py-2">Prestation</th>
            <th class="border px-4 py-2">Date</th>
            <th class="border px-4 py-2">Heure</th>
            <th class="border px-4 py-2">Client</th>
        </tr>
    </thead>
    <tbody id="historique-body">
        <tr><td colspan="4" class="text-center py-4">Chargement...</td></tr>
    </tbody>
</table>

<div id="error-msg" class="text-red-600 text-center text-sm font-medium mt-6"></div>

<script>
const API_BASE = "<?= BASE_API ?>";

async function loadHistorique() {
    try {
        console.log('Chargement des prestations terminées...');
        
        const res = await fetch(`${API_BASE}/prestataire/historique-prestations`, {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });

        console.log('Statut de la réponse:', res.status);
        console.log('Headers de la réponse:', res.headers);

        if (!res.ok) {
            const errorData = await res.json().catch(() => ({}));
            console.error('Erreur serveur:', res.status, errorData);
            
            if (res.status === 403 && errorData.error && errorData.error.includes('banni')) {
                document.getElementById('error-msg').innerText = "Votre compte a été banni. Vous ne pouvez plus accéder à cette page.";
            } else if (res.status === 401) {
                document.getElementById('error-msg').innerText = "Vous devez être connecté pour voir vos prestations terminées.";
            } else {
                document.getElementById('error-msg').innerText = `Erreur serveur (${res.status}): ${errorData.error || 'Erreur inconnue'}`;
            }
            return;
        }

        const data = await res.json();
        console.log('Données reçues:', data); // Debug pour voir la structure
        
        const body = document.getElementById('historique-body');
        body.innerHTML = "";

        // Vérifier si data.prestations existe (comme dans mes_evaluations_prestataire.php)
        const prestations = data.prestations || data;
        
        if (!Array.isArray(prestations) || prestations.length === 0) {
            body.innerHTML = "<tr><td colspan='4' class='text-center py-4'>Aucune prestation terminée.</td></tr>";
            return;
        }

        prestations.forEach(item => {
            console.log('Item:', item); // Debug pour voir chaque item
            
            // Utiliser le même champ que mes_evaluations_prestataire.php
            const heure = item.heure || item.start_time || item.heure_debut || '—';
            
            const row = `
                <tr>
                    <td class="border px-4 py-2">${item.prestation || 'N/A'}</td>
                    <td class="border px-4 py-2">${item.date || 'N/A'}</td>
                    <td class="border px-4 py-2">${heure}</td>
                    <td class="border px-4 py-2">${item.client || 'N/A'}</td>
                </tr>`;
            body.innerHTML += row;
        });

    } catch (err) {
        console.error('Erreur lors du chargement:', err);
        document.getElementById('error-msg').innerText = "Erreur lors du chargement des prestations terminées: " + err.message;
    }
}

loadHistorique();
</script>

<?php include_once('../includes/footer.php'); ?>
