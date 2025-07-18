<?php
include_once 'includes/admin_header.php';
include_once 'includes/api_path.php';

if (!isset($_GET['id'])) {
    echo "<p class='text-red-600'>ID de l’annonce manquant.</p>";
    include_once 'includes/admin_footer.php';
    exit;
}
$id = intval($_GET['id']);
?> <div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-2xl font-bold text-[#212121] mb-6"> Détails de l’annonce #<?= htmlspecialchars($id) ?> </h2>
    <div id="details" class="space-y-6 text-base">Chargement…</div>
</div>
<script>
const API_BASE = "<?= BASE_API ?>";
/* -------- récupération & rendu -------- */
async function chargerDetails(id) {
    try {
        const res = await fetch(`${API_BASE}/admin/annonces/${id}`, {
            credentials: 'include',
            headers: {
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (!data.success) {
            document.getElementById('details').innerHTML = `<p class="text-red-600">${data.message}</p>`;
            return;
        }
        const a = data.annonce;
        const objets = Array.isArray(a.objets) ? a.objets : [];
        /* adresses complètes (fallback vers ville+CP) */
        const departAdresse = a.depart_address ? `${a.depart_address}, ${a.depart_code} ${a.depart}` :
            `${a.depart} (${a.depart_code})`;
        const arriveeAdresse = a.arrivee_address ? `${a.arrivee_address}, ${a.arrivee_code} ${a.arrivee}` :
            `${a.arrivee} (${a.arrivee_code})`;
        /* ligne box éventuelle */
        const boxLigne = a.box_label ? `<p><strong class="text-gray-700">Box :</strong> ${a.box_label} – ${
                  a.box_full_address ?? `${a.box_city} (${a.box_code ?? ''})`
              }</p>` : '';
        /* couleur statut livraison */
        const livColors = {
            'En attente': 'bg-yellow-500',
            'Acceptée': 'bg-blue-500',
            'En cours': 'bg-indigo-600',
            'Livrée': 'bg-green-600',
            'Annulée': 'bg-red-600'
        };
        const statutLivraisonClass = livColors[a.statut_livraison] || 'bg-gray-400';
        let html = `
        <div class="p-6 bg-white rounded shadow space-y-3 border border-gray-200">
            <p><strong class="text-gray-700">Départ :</strong> ${departAdresse}</p>
            <p><strong class="text-gray-700">Arrivée :</strong> ${arriveeAdresse}</p>
            ${boxLigne}
            <p><strong class="text-gray-700">Date :</strong> ${a.date_creation}</p>
            <p><strong class="text-gray-700">Client :</strong>
               ${(a.client_prenom ?? '—') + ' ' + (a.client_nom ?? '')}
               (${a.client_email ?? '—'})
            </p>
            <p><strong class="text-gray-700">Statut annonce :</strong>
               <span class="inline-block px-2 py-1 rounded text-white text-xs ${
                   a.statut === 'en_attente' ? 'bg-yellow-500' :
                   a.statut === 'validee'    ? 'bg-green-600'  :
                   a.statut === 'livree'     ? 'bg-gray-600'   : 'bg-red-600'
               }">${a.statut}</span>
            </p>
            <p><strong class="text-gray-700">Livreur :</strong>
               ${a.livreur_prenom ? `${a.livreur_prenom} ${a.livreur_nom}` : '—'}
            </p>
            <p><strong class="text-gray-700">Statut livraison :</strong>
               <span class="inline-block px-2 py-1 rounded text-white text-xs ${statutLivraisonClass}">
                   ${a.statut_livraison ?? 'Non assignée'}
               </span>
            </p>
        </div>

        <h3 class="text-lg font-semibold mt-8">Objets :</h3>
        `;
        /* objets */
        if (objets.length === 0) {
            html += "<p class='text-gray-500'>Aucun objet trouvé pour cette annonce.</p>";
        } else {
            objets.forEach((o, i) => {
                const dims = (o.length && o.width && o.height) ?
                    `${o.length}m × ${o.width}m × ${o.height}m` : (o.dimensions || '—');
                html += `
                <div class="border border-gray-200 bg-gray-50 p-4 rounded mb-4">
                    <p class="font-semibold mb-1">Objet ${i+1}</p>
                    <p class="mb-1">Nom : <strong>${o.nom}</strong></p>
                    <p class="mb-1">Qté : ${o.quantite}
                        | Poids : ${o.poids ?? '—'} kg
                        | Dim. : ${dims}
                    </p>
                    <p class="mb-2">Description : ${o.description || '—'}</p>
                    ${
                        o.photos && o.photos.length
                        ? `<div class="flex flex-wrap gap-3 mt-2">
                               ${o.photos.map(p =>
                                   `<img src="${API_BASE}/storage/uploads/${p.chemin}"
                                         class="h-24 w-auto border rounded shadow-sm cursor-pointer hover:shadow-md"
                                         onclick="window.open('${API_BASE}/storage/uploads/${p.chemin}', '_blank')"
                                         title="Cliquer pour agrandir">`).join('')}
                           </div>`
                        : '<p class="text-gray-500 text-sm mt-2">Aucune photo pour cet objet</p>'
                    }
                </div>`;
            });
        }
        html += `
        <a href="annonces.php"
           class="inline-block mt-6 bg-[#1976D2] text-white hover:bg-blue-700
                  px-4 py-2 rounded text-sm">
            ⬅️ Retour aux annonces
        </a>`;
        document.getElementById('details').innerHTML = html;
    } catch (err) {
        console.error('Erreur :', err);
        document.getElementById('details').innerHTML = '<p class="text-red-600">Erreur serveur</p>';
    }
}
chargerDetails(<?= $id ?>);
</script> <?php include_once 'includes/admin_footer.php'; ?>