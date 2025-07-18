<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?>

<div id="unauthorized" class="text-red-600 text-center mt-12 hidden">
    Accès refusé. Réservé aux clients et commerçants.
</div>

<div id="page-content" class="hidden max-w-4xl mx-auto py-10 px-4">
    <h1 class="text-3xl font-bold text-[#212121] mb-6 text-center">
        ✅ Mes annonces terminées
    </h1>

    <div id="annonces-container" class="space-y-4 text-sm text-gray-700"></div>

    <div class="mt-8 text-center">
        <a href="dashboard.php"
           class="bg-gray-300 hover:bg-gray-400 text-[#212121] font-medium
                  px-5 py-2 rounded shadow">
            ⬅️ Retour
        </a>
    </div>

    <div id="annonces-message" class="text-center mt-6 text-sm font-medium"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const API_BASE = "<?= BASE_API ?>";
    const msg      = document.getElementById('annonces-message');
    const list     = document.getElementById('annonces-container');
    const unauth   = document.getElementById('unauthorized');
    const page     = document.getElementById('page-content');

    let session;
    try {
        const r = await fetch(`${API_BASE}/get_session`, { credentials:'include' });
        session = await r.json();
    } catch {
        unauth.classList.remove('hidden'); return;
    }
    if (!session.user || (!session.user.roles.includes('Customer') && !session.user.roles.includes('Seller'))) {
        unauth.classList.remove('hidden'); return;
    }
    page.classList.remove('hidden');

    let data;
    try {
        const r = await fetch(`${API_BASE}/annonce/mes`, {
            credentials:'include',
            headers:{ Accept:'application/json' }
        });
        data = await r.json();
    } catch {
        msg.textContent = 'Erreur de connexion au serveur.'; return;
    }
    if (!data.success || !Array.isArray(data.annonces)) {
        msg.textContent = 'Erreur de chargement ou accès refusé.'; return;
    }

    const terminees = data.annonces.filter(a => a.statut === 'Livrée');
    if (terminees.length === 0) {
        list.innerHTML = `<p class="text-center text-gray-500">
            Vous n'avez aucune annonce terminée pour l'instant.
        </p>`;
        return;
    }

    terminees.forEach(a => {
        const delivererId = encodeURIComponent(a.livreur_id);
        const requestId   = encodeURIComponent(a.id_annonce);

        let photosHtml = '';
        if (a.objets && a.objets.length > 0) {
            const toutesLesPhotos = [];
            a.objets.forEach(obj => {
                if (obj.photos && obj.photos.length > 0) {
                    toutesLesPhotos.push(...obj.photos.slice(0, 2));
                }
            });
            
            if (toutesLesPhotos.length > 0) {
                photosHtml = `
                    <div class="mt-2">
                        <p class="text-sm text-gray-600 mb-1">Aperçu des objets :</p>
                        <div class="flex gap-1 flex-wrap">
                            ${toutesLesPhotos.slice(0, 4).map(photo => 
                                `<img src="${API_BASE}/storage/uploads/${photo.chemin}" 
                                      class="h-12 w-12 object-cover border rounded cursor-pointer"
                                      onclick="window.open('${API_BASE}/storage/uploads/${photo.chemin}', '_blank')"
                                      title="Cliquer pour agrandir">`
                            ).join('')}
                            ${toutesLesPhotos.length > 4 ? 
                                `<div class="h-12 w-12 bg-gray-200 border rounded flex items-center justify-center text-xs text-gray-600">
                                    +${toutesLesPhotos.length - 4}
                                </div>` : ''
                            }
                        </div>
                    </div>
                `;
            }
        }

        const div = document.createElement('div');
        div.className = 'bg-white shadow rounded p-4 border border-gray-200';
        div.innerHTML = `
            <p class="font-semibold text-lg text-[#1976D2]">Annonce #${a.id_annonce}</p>
            <p><strong>Départ :</strong> ${a.depart} (${a.depart_code})</p>
            <p><strong>Arrivée :</strong> ${a.arrivee} (${a.arrivee_code})</p>
            <p class="text-sm text-gray-500">Créée le ${a.date_creation}</p>

            ${photosHtml}
            <div class="mt-3 flex flex-wrap gap-4">
                <a href="view_annonce.php?id=${requestId}"
                   class="text-blue-600 hover:underline font-medium">
                   🔍 Voir les détails
                </a>
                <a href="noter_livreur.php?deliverer_id=${delivererId}&request_id=${requestId}"
                   class="text-green-600 hover:underline font-medium">
                   ⭐ Noter le livreur
                </a>
                <a href="report_user.php?user_id=${delivererId}"
                   class="text-red-600 hover:underline font-medium">
                   🚩 Signaler le livreur
                </a>
            </div>`;
        list.appendChild(div);
    });
});
</script>

<?php include_once('../includes/footer.php'); ?>
