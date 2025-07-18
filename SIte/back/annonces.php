<?php
include_once('includes/admin_header.php');
include_once('includes/api_path.php');
?>

<div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-2xl font-bold text-[#212121] mb-6">Gestion des annonces</h2>

    <div class="flex items-center gap-4 mb-4">
        <input id="search" type="text"
               placeholder="Rechercher par ville, code postal ou nom du livreur…"
               class="border rounded-lg px-4 py-2 w-full max-w-sm focus:outline-none focus:ring-2 focus:ring-[#1976D2]"/>
        <button id="reset" class="px-4 py-2 bg-gray-200 rounded-lg">×</button>
    </div>

    <div class="overflow-x-auto shadow rounded-lg border border-gray-200 bg-white">
        <table id="annonces-table" class="min-w-full table-auto text-left">
            <thead class="bg-[#1976D2] text-white">
                <tr>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Départ</th>
                    <th class="px-6 py-3">Arrivée</th>
                    <th class="px-6 py-3">Livreur</th>
                    <th class="px-6 py-3">Statut livraison</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="text-[#212121] text-sm divide-y divide-gray-200"></tbody>
        </table>
    </div>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";
let currentSearch = '';

document.getElementById('search').addEventListener('input', (e) => {
    currentSearch = e.target.value.trim();
    chargerAnnonces();
});

document.getElementById('reset').addEventListener('click', () => {
    document.getElementById('search').value = '';
    currentSearch = '';
    chargerAnnonces();
});

async function chargerAnnonces() {
    try {
        const res = await fetch(`${API_BASE}/admin/annonces?q=${encodeURIComponent(currentSearch)}`, {
            credentials: 'include'
        });

        const data = await res.json();
        const tbody = document.querySelector('#annonces-table tbody');
        tbody.innerHTML = '';

        if (data.success && data.annonces) {
            if (data.annonces.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-gray-500">Aucune annonce trouvée.</td></tr>`;
                return;
            }

            data.annonces.forEach(a => {
                const livreur = a.livreur_prenom ? `${a.livreur_prenom} ${a.livreur_nom}` : '—';
                const statut = a.statut_livraison ?? 'Non assignée';

                let badgeClass = 'bg-gray-400';
                if (statut === 'En attente') badgeClass = 'bg-yellow-500';
                else if (statut === 'Acceptée') badgeClass = 'bg-blue-500';
                else if (statut === 'En cours') badgeClass = 'bg-indigo-600';
                else if (statut === 'Livrée') badgeClass = 'bg-green-600';
                else if (statut === 'Annulée') badgeClass = 'bg-red-500';

                tbody.innerHTML += `
                    <tr class="hover:bg-gray-100">
                        <td class="px-6 py-4">${a.id_annonce}</td>
                        <td class="px-6 py-4">${a.depart} (${a.depart_code})</td>
                        <td class="px-6 py-4">${a.arrivee} (${a.arrivee_code})</td>
                        <td class="px-6 py-4">${livreur}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 text-white rounded text-xs ${badgeClass}">
                                ${statut}
                            </span>
                        </td>
                        <td class="px-6 py-4 space-x-2">
                            <a href="view_annonce.php?id=${a.id_annonce}" class="text-[#4CAF50] font-semibold hover:underline">Voir</a>
                            <a href="edit_annonce.php?id=${a.id_annonce}" class="text-[#1976D2] font-semibold hover:underline">Modifier</a>
                            <button onclick="supprimerAnnonce(${a.id_annonce})" class="text-red-600 font-semibold hover:underline">Supprimer</button>
                        </td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-red-600 py-4">Aucune annonce trouvée.</td></tr>`;
        }
    } catch (err) {
        console.error('Erreur chargement annonces :', err);
        document.querySelector('#annonces-table tbody').innerHTML = `<tr><td colspan="6" class="text-center text-red-600 py-4">Erreur serveur</td></tr>`;
    }
}

async function supprimerAnnonce(id) {
    if (!confirm("Supprimer cette annonce ?")) return;

    try {
        const res = await fetch(`${API_BASE}/admin/annonces/${id}`, {
            method: 'DELETE',
            credentials: 'include',
        });
        const result = await res.json();
        alert(result.message);
        if (result.success) chargerAnnonces();
    } catch (err) {
        console.error("Erreur suppression :", err);
        alert("Erreur serveur lors de la suppression.");
    }
}

chargerAnnonces();
</script>

<?php include_once('includes/admin_footer.php'); ?>
