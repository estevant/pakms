<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?> <div class="max-w-4xl mx-auto py-10 px-4">
    <h1 class="text-3xl font-bold text-[#212121] mb-6 text-center"> Mes annonces </h1>
    <div id="annonces-container" class="space-y-4 text-sm text-gray-700"></div>
    <div class="mt-8 text-center">
        <a href="dashboard.php" class="bg-gray-300 hover:bg-gray-400 text-[#212121] font-medium
                  px-5 py-2 rounded shadow"> Retour </a>
    </div>
    <div id="annonces-message" class="text-center mt-6 text-sm font-medium"></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const API_BASE = "<?= BASE_API ?>";
    const msgEl = document.getElementById('annonces-message');
    const container = document.getElementById('annonces-container');
    try {
        const sess = await fetch(`${API_BASE}/get_session`, {
            credentials: 'include'
        }).then(r => r.json());
        if (!sess.user || (!sess.user.roles.includes('Customer') && !sess.user.roles.includes('Seller'))) {
            msgEl.textContent = "Accès refusé.";
            return;
        }
    } catch {
        msgEl.textContent = "Impossible de vérifier la session.";
        return;
    }
    try {
        const res = await fetch(`${API_BASE}/annonce/mes`, {
            credentials: 'include',
            headers: {
                'Accept': 'application/json'
            }
        });
        const result = await res.json();
        if (!result.success || !Array.isArray(result.annonces)) {
            msgEl.textContent = "Erreur de chargement ou accès refusé.";
            return;
        }
        const actives = result.annonces.filter(a => a.statut !== 'Livrée');
        if (actives.length === 0) {
            container.innerHTML = `
                <p class="text-center text-gray-500">
                    Vous n'avez aucune annonce active.
                </p>`;
            return;
        }
        actives.forEach(a => {
            const hasLivreur = a.livreur_id !== null && a.livreur_id !== 0;
            const isPartielle = String(a.is_split) === '1';
            
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
            div.className = "bg-white shadow rounded p-4 border border-gray-200";
            div.innerHTML = `
                <p class="font-semibold text-lg text-[#1976D2]">
                    Annonce #${a.id_annonce}
                </p>

                <p><strong>Départ :</strong> ${a.depart} (${a.depart_code})</p>
                <p><strong>Arrivée :</strong> ${a.arrivee} (${a.arrivee_code})</p>
                <p><strong>Statut :</strong> ${a.statut}</p>
                ${isPartielle
                    ? '<p class="text-yellow-600 font-semibold">Livraison partielle</p>'
                    : ''}

                <p class="text-sm text-gray-500">
                    Créée le ${a.date_creation}
                </p>

                ${photosHtml}
                <div class="mt-3 flex gap-4">
                    <a href="view_annonce.php?id=${a.id_annonce}"
                       class="text-blue-600 hover:underline font-medium">
                       Détails
                    </a>

                    ${
                        (hasLivreur || isPartielle)
                        ? `<span class="text-gray-400 cursor-not-allowed"
                                 title="${
                                     hasLivreur
                                         ? 'Annonce déjà attribuée'
                                         : 'Livraison partielle'
                                 }">
                               Modifier indisponible
                           </span>`
                        : `<a href="edit_annonce.php?id=${a.id_annonce}"
                              class="text-yellow-600 hover:underline font-medium">
                             Modifier
                           </a>`
                    }

                    ${
                        hasLivreur
                        ? `<a href="tchat.php?id=${a.id_annonce}"
                             class="text-green-600 hover:underline font-medium">
                             Tchat
                           </a>`
                        : `<span class="text-gray-400 cursor-not-allowed"
                                 title="Aucun livreur associé">
                             Tchat indisponible
                           </span>`
                    }

                    ${
                        hasLivreur && (a.statut === 'En cours' || a.statut === 'Acceptée')
                        ? `<a href="scanner_qr.php?request_id=${a.id_annonce}"
                             class="text-blue-600 hover:underline font-medium">
                             📱 Scanner QR code
                           </a>`
                        : hasLivreur
                        ? `<span class="text-gray-400 cursor-not-allowed"
                                 title="Livraison non en cours">
                             Scanner QR indisponible
                           </span>`
                        : `<span class="text-gray-400 cursor-not-allowed"
                                 title="Aucun livreur associé">
                             Scanner QR indisponible
                           </span>`
                    }

                    <a href="suivi.php?id=${a.id_annonce}"
                       class="text-purple-600 hover:underline font-medium">
                       Suivi
                    </a>

                    <button onclick="supprimerAnnonce(${a.id_annonce})"
                            class="text-red-600 hover:underline font-medium">
                       Supprimer
                    </button>
                </div>`;
            container.appendChild(div);
        });
    } catch (err) {
        console.error(err);
        msgEl.textContent = "Erreur de connexion au serveur.";
    }
});
async function supprimerAnnonce(id) {
    if (!confirm("Voulez-vous vraiment supprimer cette annonce ?")) return;
    const API_BASE = "<?= BASE_API ?>";
    try {
        const res = await fetch(`${API_BASE}/annonce/delete`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id_annonce: id
            })
        });
        const result = await res.json();
        alert(result.message);
        if (result.success) window.location.reload();
    } catch (err) {
        console.error(err);
        alert("Erreur lors de la suppression.");
    }
}
</script> <?php include_once('../includes/footer.php'); ?>