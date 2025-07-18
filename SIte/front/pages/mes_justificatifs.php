<?php 
include_once('../includes/header.php');
include_once('../includes/api_path.php');

$types = ['CNI', 'Permis', 'Carte Grise', 'Assurance', 'Autre'];
?>

<div class="max-w-3xl mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6 text-blue-700">Mes justificatifs</h2>
    <form id="upload-form" class="mb-8 bg-white rounded-lg shadow-md p-6 flex flex-col gap-4" enctype="multipart/form-data">
        <div>
            <label for="type" class="block font-medium mb-1">Type de justificatif :</label>
            <select id="type" name="type" required class="input-field">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="description" class="block font-medium mb-1">Description (optionnelle, obligatoire si "Autre") :</label>
            <input type="text" id="description" name="description" maxlength="255" class="input-field">
        </div>
        <div>
            <label for="document" class="block font-medium mb-1">Fichier justificatif :</label>
            <input type="file" id="document" name="document" accept=".pdf,.jpg,.jpeg,.png" required class="input-field">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded self-end">Envoyer</button>
        <div id="upload-msg" class="text-sm mt-2"></div>
    </form>
    <div class="bg-white rounded-lg shadow-md p-6">
        <table class="min-w-full table-auto border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 border">Type</th>
                    <th class="px-4 py-2 border">Description</th>
                    <th class="px-4 py-2 border">Date</th>
                    <th class="px-4 py-2 border">Statut</th>
                    <th class="px-4 py-2 border">Fichier</th>
                </tr>
            </thead>
            <tbody id="justif-body">
                <tr><td colspan="5" class="text-center py-4">Chargement...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";

async function loadJustificatifs() {
    try {
        const res = await fetch(`${API_BASE}/prestataire/mes-justificatifs`, { credentials: 'include' });
        const data = await res.json();
        const body = document.getElementById('justif-body');
        body.innerHTML = '';
        if (!data.data || data.data.length === 0) {
            body.innerHTML = '<tr><td colspan="5" class="text-center py-4">Aucun justificatif trouvé.</td></tr>';
            return;
        }
        data.data.forEach(j => {
            body.innerHTML += `
                <tr>
                    <td class="border px-4 py-2">${j.type}</td>
                    <td class="border px-4 py-2">${j.description || ''}</td>
                    <td class="border px-4 py-2">${j.created_at ? j.created_at.substring(0, 10) : ''}</td>
                    <td class="border px-4 py-2">${j.statut}</td>
                    <td class="border px-4 py-2">${j.url ? `<a href="${j.url}" target="_blank" class="text-blue-600 underline">Télécharger</a>` : '-'}</td>
                </tr>
            `;
        });
    } catch (err) {
        document.getElementById('justif-body').innerHTML = '<tr><td colspan="5" class="text-center text-red-600 py-4">Erreur de chargement</td></tr>';
    }
}

document.getElementById('upload-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const type = form.type.value;
    const description = form.description.value;
    const file = form.document.files[0];
    const msg = document.getElementById('upload-msg');
    msg.textContent = '';
    if (!type || !file || (type === 'Autre' && !description)) {
        msg.textContent = 'Veuillez remplir tous les champs obligatoires.';
        msg.className = 'text-red-600';
        return;
    }
    const formData = new FormData();
    formData.append('type', type);
    formData.append('description', description);
    formData.append('document', file);
    try {
        const res = await fetch(`${API_BASE}/justificatifs`, {
            method: 'POST',
            credentials: 'include',
            body: formData
        });
        const result = await res.json();
        msg.textContent = result.message || (result.success ? 'Justificatif envoyé.' : 'Erreur lors de l\'envoi.');
        msg.className = result.success ? 'text-green-600' : 'text-red-600';
        if (result.success) {
            form.reset();
            loadJustificatifs();
        }
    } catch (err) {
        msg.textContent = 'Erreur lors de l\'envoi.';
        msg.className = 'text-red-600';
    }
});

loadJustificatifs();
</script>

<style>
.input-field {
    @apply border border-gray-300 rounded px-3 py-2 w-full;
}
</style>

<?php include_once('../includes/footer.php'); ?>
