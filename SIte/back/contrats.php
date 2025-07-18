<?php include_once('includes/admin_header.php'); ?>

<div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-2xl font-bold text-[#212121] mb-6">Contrats</h2>

    <!-- Filtres -->
    <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
        <input id="filtre-nom" type="text"
               placeholder="Filtrer par nom ou email"
               class="border rounded-lg px-4 py-2 w-full max-w-xs focus:outline-none focus:ring-2 focus:ring-[#1976D2]"/>

        <select id="filtre-statut" class="border rounded-lg px-4 py-2 w-full max-w-xs">
            <option value="">Tous les statuts</option>
            <option value="pending">🕓 En attente</option>
            <option value="future">📅 À venir</option>
            <option value="active">✅ Actif</option>
            <option value="rejected">❌ Refusé</option>
        </select>

        <button id="reset" class="bg-gray-200 px-4 py-2 rounded-lg">Réinitialiser</button>
    </div>

    <div class="overflow-x-auto bg-white shadow rounded-lg border border-gray-200">
        <table class="min-w-full table-auto text-left">
            <thead class="bg-[#1976D2] text-white">
                <tr>
                    <th class="px-6 py-3">Utilisateur</th>
                    <th class="px-6 py-3">Période</th>
                    <th class="px-6 py-3">Conditions</th>
                    <th class="px-6 py-3">Statut</th>
                    <th class="px-6 py-3">PDF</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody id="contracts-tbody" class="text-[#212121] text-sm divide-y divide-gray-200"></tbody>
        </table>
    </div>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";
let contrats = [];

async function chargerContrats() {
    const res = await fetch(`${API_BASE}/admin/contracts`, { credentials: 'include' });
    const data = await res.json();
    const tbody = document.getElementById('contracts-tbody');
    tbody.innerHTML = '';

    if (data.success && data.contracts.length) {
        contrats = data.contracts;
        afficherContrats();
    } else {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-6 text-gray-500">Aucun contrat trouvé</td></tr>`;
    }
}

function afficherContrats() {
    const nom = document.getElementById('filtre-nom').value.trim().toLowerCase();
    const statut = document.getElementById('filtre-statut').value;

    const tbody = document.getElementById('contracts-tbody');
    tbody.innerHTML = '';

    const filtres = contrats.filter(c => {
        const fullName = `${c.user?.first_name ?? ''} ${c.user?.last_name ?? ''}`.toLowerCase();
        const email = c.user?.email?.toLowerCase() ?? '';
        return (!nom || fullName.includes(nom) || email.includes(nom)) &&
               (!statut || c.status === statut);
    });

    if (!filtres.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-6 text-gray-500">Aucun contrat trouvé</td></tr>`;
        return;
    }

    filtres.forEach(c => {
        const badge = {
            pending:  '<span class="bg-yellow-200 text-yellow-700 text-xs px-2 py-1 rounded">🕓 En attente</span>',
            future:   '<span class="bg-blue-200 text-blue-800 text-xs px-2 py-1 rounded">📅 À venir</span>',
            active:   '<span class="bg-green-200 text-green-800 text-xs px-2 py-1 rounded">✅ Actif</span>',
            rejected: '<span class="bg-red-200 text-red-800 text-xs px-2 py-1 rounded">❌ Refusé</span>'
        }[c.status] ?? c.status;

        const nom = c.user?.first_name && c.user?.last_name
            ? `${c.user.first_name} ${c.user.last_name}<br><span class="text-xs text-gray-500">${c.user.email}</span>`
            : `ID: ${c.user_id}`;

        const actions = c.status === 'pending' ? `
            <button onclick="valider(${c.contract_id})" class="text-green-600 hover:underline font-semibold">Valider</button>
            <button onclick="refuser(${c.contract_id})" class="text-red-600 hover:underline font-semibold">Refuser</button>
        ` : '—';

        tbody.innerHTML += `
            <tr class="hover:bg-gray-100">
                <td class="px-6 py-4">${nom}</td>
                <td class="px-6 py-4">${formatDate(c.start_date)} → ${formatDate(c.end_date)}</td>
                <td class="px-6 py-4">${c.terms ?? '—'}</td>
                <td class="px-6 py-4">${badge}</td>
                <td class="px-6 py-4">
                    ${c.pdf_path 
                        ? `<a href="${API_BASE}/storage/${c.pdf_path}" target="_blank" class="text-blue-600 hover:underline">📄 Télécharger</a>` 
                        : '—'}
                </td>
                <td class="px-6 py-4 space-x-2">${actions}</td>
            </tr>
        `;
    });
}

function formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString("fr-FR");
}

async function valider(id) {
    if (!confirm("Valider ce contrat ?")) return;

    const res = await fetch(`${API_BASE}/admin/contracts/${id}/approve`, {
        method: 'POST',
        credentials: 'include'
    });

    const result = await res.json();
    alert(result.message || "Contrat validé.");
    if (result.success) chargerContrats();
}

async function refuser(id) {
    if (!confirm("Refuser ce contrat ?")) return;

    const res = await fetch(`${API_BASE}/admin/contracts/${id}/reject`, {
        method: 'POST',
        credentials: 'include'
    });

    const result = await res.json();
    alert(result.message || "Contrat refusé.");
    if (result.success) chargerContrats();
}

document.getElementById('filtre-nom').addEventListener('input', afficherContrats);
document.getElementById('filtre-statut').addEventListener('change', afficherContrats);
document.getElementById('reset').addEventListener('click', () => {
    document.getElementById('filtre-nom').value = '';
    document.getElementById('filtre-statut').value = '';
    afficherContrats();
});

chargerContrats();
</script>

<?php include_once('includes/admin_footer.php'); ?>
