<?php include_once('includes/admin_header.php'); 
include_once('../front/includes/api_path.php');
?>

<div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-2xl font-bold text-[#212121] mb-6">Liste des clients</h2>

    <div class="flex items-center gap-4 mb-4">
        <input id="search" type="text"
               placeholder="Rechercher nom, prénom ou e-mail…"
               class="border rounded-lg px-4 py-2 w-full max-w-sm focus:outline-none focus:ring-2 focus:ring-[#1976D2]"/>
        <button id="reset" class="px-4 py-2 bg-gray-200 rounded-lg">×</button>
    </div>

    <div class="overflow-x-auto shadow rounded-lg border border-gray-200 bg-white">
        <table id="clients-table" class="min-w-full table-auto text-left">
            <thead class="bg-[#1976D2] text-white">
                <tr>
                    <th class="px-6 py-3">Nom</th>
                    <th class="px-6 py-3">Prénom</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="text-[#212121] text-sm divide-y divide-gray-200"></tbody>
        </table>
    </div>
</div>

<script>
let currentSearch = '';

document.getElementById('search').addEventListener('input', (e) => {
    currentSearch = e.target.value.trim();
    chargerClients();
});

document.getElementById('reset').addEventListener('click', () => {
    document.getElementById('search').value = '';
    currentSearch = '';
    chargerClients();
});

async function chargerClients() {
    const API_BASE = "<?= BASE_API ?>";
    const res = await fetch(`${API_BASE}/admin/clients?q=${encodeURIComponent(currentSearch)}`, {
        credentials: 'include'
    });
    const data = await res.json();

    const tbody = document.querySelector('#clients-table tbody');
    tbody.innerHTML = '';

    if (data.success && data.clients.length > 0) {
        data.clients.forEach(client => {
            const tr = document.createElement('tr');
            tr.classList.add("hover:bg-gray-100");
            tr.innerHTML = `
                <td class="px-6 py-4">${client.nom}</td>
                <td class="px-6 py-4">${client.prenom}</td>
                <td class="px-6 py-4">${client.email}</td>
                <td class="px-6 py-4">
                    <a href="edit_client.php?id=${client.id_utilisateur}" class="text-[#1976D2] font-semibold hover:underline">Modifier</a>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } else {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-red-600 py-4">Aucun client trouvé</td>
            </tr>
        `;
    }
}

chargerClients();
</script>

<?php include_once('includes/admin_footer.php'); ?>
