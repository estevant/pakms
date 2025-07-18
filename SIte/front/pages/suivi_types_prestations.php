<?php 
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?>

<div class="max-w-3xl mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6 text-blue-700">Suivi de mes demandes de type de prestation</h2>
    <div class="bg-white rounded-lg shadow-md p-6">
        <table class="min-w-full table-auto border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 border">Nom</th>
                    <th class="px-4 py-2 border">Description</th>
                    <th class="px-4 py-2 border">Date</th>
                    <th class="px-4 py-2 border">Statut</th>
                    <th class="px-4 py-2 border">Justificatif</th>
                </tr>
            </thead>
            <tbody id="types-body">
                <tr><td colspan="5" class="text-center py-4">Chargement...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";

async function loadTypes() {
    try {
        const res = await fetch(`${API_BASE}/prestataire/mes-demandes-types`, { credentials: 'include' });
        const data = await res.json();
        const body = document.getElementById('types-body');
        body.innerHTML = '';
        if (!data.data || data.data.length === 0) {
            body.innerHTML = '<tr><td colspan="5" class="text-center py-4">Aucune demande trouvée.</td></tr>';
            return;
        }
        data.data.forEach(type => {
            body.innerHTML += `
                <tr>
                    <td class="border px-4 py-2">${type.nom}</td>
                    <td class="border px-4 py-2">${type.description || ''}</td>
                    <td class="border px-4 py-2">${type.created_at ? type.created_at.substring(0, 10) : ''}</td>
                    <td class="border px-4 py-2">${type.statut}</td>
                    <td class="border px-4 py-2">${type.justificatif_url ? `<a href="${type.justificatif_url}" target="_blank" class="text-blue-600 underline">Voir</a>` : '-'}</td>
                </tr>
            `;
        });
    } catch (err) {
        document.getElementById('types-body').innerHTML = '<tr><td colspan="5" class="text-center text-red-600 py-4">Erreur de chargement</td></tr>';
    }
}

loadTypes();
</script>

<?php include_once('../includes/footer.php'); ?> 