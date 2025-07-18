<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?>

<h2 class="text-2xl font-bold mb-4">Mes Evaluations</h2>

<a href="dashboard.php" class="inline-block bg-blue-600 text-white px-4 py-2 rounded mb-4">
    ← Retour à mon journal de bord
</a>

<div class="mb-4">
  <label for="type-filter" class="font-semibold mr-2">Filtrer par type de prestation :</label>
  <select id="type-filter" class="border px-2 py-1">
    <option value="">Toutes</option>
  </select>
</div>

<div id="note-moyenne" class="text-lg font-semibold text-blue-700 mb-4"></div>

<table class="table-auto w-full border-collapse border border-gray-300">
    <thead class="bg-gray-100">
        <tr>
            <th class="border px-4 py-2">Prestation</th>
            <th class="border px-4 py-2">Date</th>
            <th class="border px-4 py-2">Heure</th>
            <th class="border px-4 py-2">Client</th>
            <th class="border px-4 py-2">Note</th>
            <th class="border px-4 py-2">Commentaire</th>
        </tr>
    </thead>
    <tbody id="historique-body">
        <tr><td colspan="6" class="text-center py-4">Chargement...</td></tr>
    </tbody>
</table>

<div id="error-msg" class="text-red-600 text-center text-sm font-medium mt-6"></div>

<script>
const API_BASE = "<?= BASE_API ?>";

async function loadTypes() {
    const res = await fetch(`${API_BASE}/prestataire/mes-types-prestations`, { credentials: 'include' });
    const data = await res.json();
    const select = document.getElementById('type-filter');
    if (data.data) {
        data.data.forEach(t => {
            select.innerHTML += `<option value="${t.service_type_id}">${t.name}</option>`;
        });
    }
}

async function loadHistorique(serviceTypeId = "") {
    try {
        let url = `${API_BASE}/prestataire/historique-prestations?with_evaluations=1`;
        if (serviceTypeId) url += `&service_type_id=${serviceTypeId}`;
        const res = await fetch(url, {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) throw new Error('Erreur serveur');
        const data = await res.json();
        const body = document.getElementById('historique-body');
        body.innerHTML = "";
        if (!data.prestations || data.prestations.length === 0) {
            body.innerHTML = "<tr><td colspan='6' class='text-center py-4'>Aucune prestation terminée.</td></tr>";
            document.getElementById('note-moyenne').innerText = '';
            return;
        }
        data.prestations.forEach(item => {
            const row = `
                <tr>
                    <td class="border px-4 py-2">${item.prestation}</td>
                    <td class="border px-4 py-2">${item.date}</td>
                    <td class="border px-4 py-2">${item.heure}</td>
                    <td class="border px-4 py-2">${item.client}</td>
                    <td class="border px-4 py-2">${item.note ? '★'.repeat(item.note) : '-'}</td>
                    <td class="border px-4 py-2">${item.commentaire || ''}</td>
                </tr>`;
            body.innerHTML += row;
        });
        if (data.moyenne !== null) {
            document.getElementById('note-moyenne').innerText = `Note moyenne : ${data.moyenne} / 5 (${data.count} avis)`;
        } else {
            document.getElementById('note-moyenne').innerText = '';
        }
    } catch (err) {
        document.getElementById('error-msg').innerText = "Erreur lors du chargement des prestations terminées.";
        console.error(err);
    }
}

document.getElementById('type-filter').addEventListener('change', function() {
    loadHistorique(this.value);
});

loadTypes();
loadHistorique();
</script>

<?php include_once('../includes/footer.php'); ?>
