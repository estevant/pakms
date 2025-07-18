<?php
include_once('includes/admin_header.php');
include_once('includes/api_path.php');
?>

<div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-2xl font-bold text-[#212121] mb-6">Gestion des livreurs</h2>

    <div class="flex items-center gap-4 mb-4">
        <input id="search" type="text"
               placeholder="Rechercher nom, prénom, email, adresse ou téléphone…"
               class="border rounded-lg px-4 py-2 w-full max-w-sm focus:outline-none focus:ring-2 focus:ring-[#1976D2]"/>
        <button id="reset" class="px-4 py-2 bg-gray-200 rounded-lg">×</button>
    </div>

    <div class="overflow-x-auto shadow rounded-lg border border-gray-200 bg-white">
        <table id="table-livreurs" class="min-w-full table-auto text-left">
            <thead class="bg-[#1976D2] text-white">
                <tr>
                    <th class="px-6 py-3">Nom</th>
                    <th class="px-6 py-3">Prénom</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Adresse</th>
                    <th class="px-6 py-3">CP</th>
                    <th class="px-6 py-3">Statut</th>
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
    chargerLivreurs();
});

document.getElementById('reset').addEventListener('click', () => {
    document.getElementById('search').value = '';
    currentSearch = '';
    chargerLivreurs();
});

async function chargerLivreurs() {
    const res = await fetch(`${API_BASE}/admin/livreurs?q=${encodeURIComponent(currentSearch)}`, {
        credentials: 'include'
    });
    const data = await res.json();
    const tbody = document.querySelector('#table-livreurs tbody');
    tbody.innerHTML = '';

    if (data.success) {
        if (data.livreurs.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-gray-500">Aucun livreur trouvé.</td></tr>`;
            return;
        }

        data.livreurs.forEach(l => {
            const statut = l.statut_validation == 1
                ? '<span class="text-green-600 font-semibold">✔️ Validé</span>'
                : '<span class="text-yellow-600 font-semibold">⏳ En attente</span>';

            tbody.innerHTML += `
                <tr class="hover:bg-gray-100">
                    <td class="px-6 py-4">${l.nom}</td>
                    <td class="px-6 py-4">${l.prenom}</td>
                    <td class="px-6 py-4">${l.email}</td>
                    <td class="px-6 py-4">${l.adresse ?? '-'}</td>
                    <td class="px-6 py-4">${l.cp ?? '-'}</td>
                    <td class="px-6 py-4">${statut}</td>
                    <td class="px-6 py-4 space-x-2">
                        ${l.statut_validation == 0
                            ? `<button onclick="validerLivreur(${l.id_utilisateur})" class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-3 py-1 rounded shadow">Valider</button>`
                            : `<button onclick="annulerValidation(${l.id_utilisateur})" class="bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-semibold px-3 py-1 rounded shadow">Annuler</button>`
                        }
                        <button onclick="modifier(${l.id_utilisateur})" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-3 py-1 rounded shadow">Modifier</button>
                        <button onclick="supprimer(${l.id_utilisateur})" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-3 py-1 rounded shadow">Supprimer</button>
                        <button onclick="voirJustificatifs(${l.id_utilisateur})" class="bg-gray-700 hover:bg-gray-800 text-white text-sm font-semibold px-3 py-1 rounded shadow">📁 Justificatifs</button>
                    </td>
                </tr>
            `;
        });
    } else {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-red-600 py-4">Erreur de chargement</td></tr>`;
    }
}

function voirJustificatifs(id) {
    window.location.href = 'admin_justificatifs.php?id=' + id;
}

async function validerLivreur(id) {
    const res = await fetch(`${API_BASE}/admin/livreurs/valider`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ id_utilisateur: id })
    });

    const result = await res.json();
    alert(result.message);
    if (result.success) chargerLivreurs();
}

async function annulerValidation(id) {
    const res = await fetch(`${API_BASE}/admin/livreurs/invalider`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ id_utilisateur: id })
    });

    const result = await res.json();
    alert(result.message);
    if (result.success) chargerLivreurs();
}

async function supprimer(id) {
    if (!confirm('Supprimer ce livreur ?')) return;

    const res = await fetch(`${API_BASE}/admin/livreurs/${id}`, {
        method: 'DELETE',
        credentials: 'include'
    });

    const result = await res.json();
    alert(result.message);
    if (result.success) chargerLivreurs();
}

function modifier(id) {
    window.location.href = 'edit_livreur.php?id=' + id;
}

chargerLivreurs();
</script>

<?php include_once('includes/admin_footer.php'); ?>
