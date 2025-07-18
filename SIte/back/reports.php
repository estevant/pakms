<?php require_once('includes/admin_header.php'); ?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Signalements</h1>

    <div class="bg-white p-4 rounded shadow mb-6">
        <form id="filters" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <select name="target_type" class="p-2 border rounded">
                <option value="">Type de cible</option>
                <option value="annonce">Annonce</option>
                <option value="user">Utilisateur</option>
            </select>

            <select name="status" class="p-2 border rounded">
                <option value="">Statut</option>
                <option value="Ouvert">Ouvert</option>
                <option value="Résolu">Résolu</option>
                <option value="Rejeté">Rejeté</option>
            </select>

            <input type="text" name="q" placeholder="Mot-clé..." class="p-2 border rounded">
            <input type="date" name="after" class="p-2 border rounded">
            <input type="date" name="before" class="p-2 border rounded">
        </form>
    </div>

    <div id="reportList" class="space-y-4"></div>
</div>

<script>
const BASE_API = "<?= BASE_API ?>";
const form = document.getElementById('filters');

form.addEventListener('change', chargerSignalements);

async function chargerSignalements() {
    const params = new URLSearchParams(new FormData(form)).toString();
    const res    = await fetch(`${BASE_API}/admin/reports?${params}`, { credentials: 'include' });
    const data   = await res.json();
    const cnt    = document.getElementById('reportList');

    if (!data.success) {
        cnt.innerHTML = `<p class="text-red-600">Erreur de chargement.</p>`;
        return;
    }
    if (data.reports.length === 0) {
        cnt.innerHTML = `<p class="text-gray-500">Aucun signalement trouvé.</p>`;
        return;
    }

    cnt.innerHTML = data.reports.map(r => `
        <div class="bg-white p-4 rounded shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-gray-600">${new Date(r.created_at).toLocaleString()}</p>
                    <p>
                      <strong>${r.reporter}</strong>
                      a signalé 
                      <span class="font-semibold">${r.target_label}</span>
                      (${r.reports_count} signalement${r.reports_count>1?'s':''})
                    </p>
                    <p class="text-gray-700">Raison : <strong>${r.reason}</strong></p>
                    ${r.description 
                      ? `<p class="text-gray-700">Commentaire : ${r.description}</p>` 
                      : ''}
                </div>
                <div class="text-right space-y-2">
                    <select onchange="changerStatut(${r.report_id}, this.value)" 
                            class="p-1 border rounded w-full">
                        <option ${r.status==='Ouvert'?'selected':''}>Ouvert</option>
                        <option ${r.status==='Résolu'?'selected':''}>Résolu</option>
                        <option ${r.status==='Rejeté'?'selected':''}>Rejeté</option>
                    </select>
                    <button onclick="supprimerSignalement(${r.report_id})" 
                            class="text-red-600 hover:underline">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

async function changerStatut(id, statut) {
    await fetch(`${BASE_API}/admin/reports/${id}`, {
        method: 'PATCH',
        headers:{'Content-Type':'application/json'},
        credentials:'include',
        body: JSON.stringify({ status: statut })
    });
    chargerSignalements();
}

async function supprimerSignalement(id) {
    if (!confirm('Supprimer ce signalement ?')) return;
    await fetch(`${BASE_API}/admin/reports/${id}`, {
        method:'DELETE',
        credentials:'include'
    });
    chargerSignalements();
}

window.addEventListener('DOMContentLoaded', chargerSignalements);
</script>

<?php require_once('includes/admin_footer.php'); ?>
