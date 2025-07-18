<?php
include_once('includes/admin_header.php');
include_once('includes/api_path.php');
?>

<div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-2xl font-bold text-[#212121] mb-6">Gestion des commerçants</h2>
<div class="p-6 bg-gray-50 min-h-screen">
  <div class="relative z-10 flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-[#212121]">Gestion des commerçants</h2>
    <a
      href="/admin/accept_contract.php"
      class="inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition"
    >
      Gérer les contrats
    </a>
  </div>

    <div class="flex items-center gap-4 mb-4">
        <input id="search" type="text"
               placeholder="Rechercher nom, prénom, email ou raison sociale…"
               class="border rounded-lg px-4 py-2 w-full max-w-sm focus:outline-none focus:ring-2 focus:ring-[#1976D2]"/>
        <button id="reset" class="px-4 py-2 bg-gray-200 rounded-lg">×</button>
    </div>

    <div class="overflow-x-auto shadow rounded-lg border border-gray-200 bg-white">
        <table id="table-commercants" class="min-w-full table-auto text-left">
            <thead class="bg-[#1976D2] text-white">
                <tr>
                    <th class="px-6 py-3">Nom</th>
                    <th class="px-6 py-3">Prénom</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Raison sociale</th>
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
    chargerCommercants();
});

document.getElementById('reset').addEventListener('click', () => {
    document.getElementById('search').value = '';
    currentSearch = '';
    chargerCommercants();
});

async function chargerCommercants() {
    const res = await fetch(`${API_BASE}/admin/commercants?q=${encodeURIComponent(currentSearch)}`, {
        credentials: 'include'
    });

    const data = await res.json();
    const tbody = document.querySelector('#table-commercants tbody');
    tbody.innerHTML = '';

    if (data.success) {
        if (data.commercants.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-gray-500">Aucun commerçant trouvé.</td></tr>`;
            return;
        }

        data.commercants.forEach(c => {
            tbody.innerHTML += `
                <tr class="hover:bg-gray-100">
                    <td class="px-6 py-4">${c.nom}</td>
                    <td class="px-6 py-4">${c.prenom}</td>
                    <td class="px-6 py-4">${c.email}</td>
                    <td class="px-6 py-4">${c.raison_sociale ?? '—'}</td>
                    <td class="px-6 py-4 space-x-2">
                        <button onclick="modifier(${c.id_utilisateur})" class="text-[#1976D2] font-semibold hover:underline">Modifier</button>
                        <button onclick="supprimer(${c.id_utilisateur})" class="text-red-600 font-semibold hover:underline">Supprimer</button>
                    </td>
                </tr>
            `;
        });
    } else {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-red-600 py-4">Erreur de chargement</td></tr>`;
    }
}

function modifier(id) {
    window.location.href = 'edit_commercant.php?id=' + id;
}

async function supprimer(id) {
    if (!confirm('Supprimer ce commerçant ?')) return;

    const res = await fetch(`${API_BASE}/admin/commercants/${id}`, {
        method: 'DELETE',
        credentials: 'include'
    });

    const result = await res.json();
    alert(result.message);
    if (result.success) chargerCommercants();
}

chargerCommercants();
</script>

<?php include_once('includes/admin_footer.php'); ?>
