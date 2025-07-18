<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');

if (!isset($_GET['id'])) {
  echo '<p class="text-red-600">ID de l’annonce manquant.</p>';
  include_once('../includes/footer.php');
  exit;
}
?> <div class="max-w-3xl mx-auto py-10 px-6">
    <h2 class="text-2xl font-bold mb-4">Détails de l’annonce</h2>
    <div id="details" class="space-y-4 text-base">Chargement…</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const API_BASE = "<?= BASE_API ?>";
    const id = <?= intval($_GET['id']) ?>;
    const container = document.getElementById('details');
    try {
        const res = await fetch(`${API_BASE}/annonce/annonce?id=${id}`, {
            credentials: 'include',
            headers: {
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (!data.success) {
            container.innerHTML = `<p class="text-red-600">${data.message}</p>`;
            return;
        }
        const a = data.annonce;
        const departHtml = a.depart_address ?? `${a.depart} (${a.depart_code})`;
        const arriveeHtml = a.arrivee_address ?? `${a.arrivee} (${a.arrivee_code})`;
        let html = `
        <div class="p-4 bg-white shadow rounded space-y-2">
            <p><strong class="text-gray-700">Départ :</strong> ${departHtml}</p>
            <p><strong class="text-gray-700">Arrivée :</strong> ${arriveeHtml}</p>
            ${a.box_label ? `
              <p><strong class="text-gray-700">Box sélectionnée :</strong>
                 ${a.box_label} (${a.box_city}) - statut : ${a.box_status}
              </p>` : ''}
            <p><strong class="text-gray-700">Date :</strong> ${a.date_creation}</p>
            <p><strong class="text-gray-700">Client :</strong>
               ${a.client_prenom} ${a.client_nom} (${a.client_email})
            </p>
            <p><strong class="text-gray-700">Statut :</strong>
              <span class="inline-block px-2 py-1 rounded text-white text-xs
                ${
                  a.statut === 'en_attente' ? 'bg-yellow-500' :
                  a.statut === 'validee'    ? 'bg-green-600'  :
                  a.statut === 'acceptee'   ? 'bg-blue-600'   :
                  a.statut === 'terminee'   ? 'bg-gray-800'   :
                                                'bg-gray-400'
                }">
                ${a.statut}
              </span>
            </p>
        </div>
        `;
        /* Objets */
        if (!Array.isArray(a.objets) || a.objets.length === 0) {
            html += `<p class="text-gray-500">Aucun objet trouvé.</p>`;
        } else {
            a.objets.forEach((o, i) => {
                html += `
                <div class="border p-3 mb-3 rounded-md bg-gray-50">
                    <p class="font-semibold">Objet ${i + 1}</p>
                    <p>
                      Nom : <strong>${o.nom}</strong> |
                      Qté : ${o.quantite} |
                      Poids : ${o.poids} kg |
                      Dimensions : ${o.dimensions}
                    </p>
                    <p>Description : ${o.description || '—'}</p>
                    ${
                        o.photos?.length
                        ? `<div class="flex gap-2 mt-2">
                             ${o.photos.map(p =>
                               `<img src="${API_BASE}/storage/uploads/${p.chemin}" class="h-20 border rounded cursor-pointer"
                                      onclick="window.open('${API_BASE}/storage/uploads/${p.chemin}', '_blank')"
                                      title="Cliquer pour agrandir">`
                             ).join('')}
                           </div>`
                        : ''
                    }
                </div>
                `;
            });
        }
        html += `
          <a href="dashboard.php"
             class="inline-block mt-6 bg-[#1976D2] text-white hover:bg-blue-700
                    px-4 py-2 rounded text-sm">
            Retour au tableau de bord
          </a>
        `;
        container.innerHTML = html;
    } catch (err) {
        console.error(err);
        container.innerHTML = `<p class="text-red-600">Erreur lors du chargement des détails.</p>`;
    }
});
</script> <?php include_once('../includes/footer.php'); ?>