<?php
include_once('includes/admin_header.php');
include_once('includes/api_path.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p class='text-red-600 p-6'>ID utilisateur invalide</p>";
    include_once('includes/admin_footer.php');
    exit;
}

$userId = (int)$_GET['id'];
?>

<div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-2xl font-bold text-[#212121] mb-6">📁 Justificatifs du livreur #<?= $userId ?></h2>
    <div id="contenu-justificatifs" class="space-y-6"></div>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";
const LIVREUR_ID = <?= $userId ?>;
const BASE_STORAGE = new URL('../public/storage', API_BASE).href;

async function chargerJustificatifs() {
    try {
        const res = await fetch(`${API_BASE}/admin/justificatifs/${LIVREUR_ID}`, { credentials: 'include' });
        const data = await res.json();
        const container = document.getElementById('contenu-justificatifs');
        container.innerHTML = '';

        if (!data.success || !data.justificatifs.length) {
            container.innerHTML = "<p class='text-gray-500'>Aucun justificatif trouvé pour cet utilisateur.</p>";
            return;
        }

        data.justificatifs.forEach(doc => {
            const ext = doc.filename.split('.').pop().toLowerCase();
            const fileUrl = `${BASE_STORAGE}/${doc.filename}`;
            let preview = '';

            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                preview = `<img src="${fileUrl}" class="w-24 h-24 object-cover border rounded" alt="aperçu document">`;
            } else if (ext === 'pdf') {
                preview = `<embed src="${fileUrl}" type="application/pdf" width="100" height="100" class="border rounded">`;
            } else {
                preview = `<span class="text-gray-400 text-sm">Aperçu non dispo</span>`;
            }

            container.innerHTML += `
                <div class="p-4 border bg-white rounded shadow space-y-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-semibold">${doc.type}${doc.type === 'Autre' ? ' : ' + doc.description : ''}</p>
                            <p class="text-sm text-gray-500">${doc.created_at}</p>
                        </div>
                        <div>
                            <select class="text-sm px-2 py-1 border rounded" onchange="changerStatut(${doc.id}, this.value)">
                                <option value="En attente" ${doc.statut === 'En attente' ? 'selected' : ''}>⏳ En attente</option>
                                <option value="Validé" ${doc.statut === 'Validé' ? 'selected' : ''}>✅ Validé</option>
                                <option value="Refusé" ${doc.statut === 'Refusé' ? 'selected' : ''}>❌ Refusé</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-4 items-center">
                        ${preview}
                        <div>
                            <a href="${API_BASE}/livreur/justificatifs/${doc.id}/download" class="text-blue-600 hover:underline text-sm">📥 Télécharger</a>
                        </div>
                    </div>
                </div>
            `;
        });
    } catch (err) {
        console.error("Erreur lors du chargement des justificatifs", err);
        document.getElementById('contenu-justificatifs').innerHTML = "<p class='text-red-600'>Erreur serveur.</p>";
    }
}

async function changerStatut(id, statut) {
    try {
        const res = await fetch(`${API_BASE}/admin/justificatifs/statut`, {
            method: 'PATCH',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id_justificatif: id, statut })
        });

        const result = await res.json();
        if (!result.success) {
            alert("Erreur : " + result.message);
        } else {
            alert("Statut mis à jour.");
        }
    } catch (err) {
        console.error("Erreur mise à jour statut", err);
        alert("Erreur serveur lors de la mise à jour du statut.");
    }
}

chargerJustificatifs();
</script>

<?php include_once('includes/admin_footer.php'); ?>
