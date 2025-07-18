<?php include_once('includes/admin_header.php'); ?>
<!-- Bootstrap 5 CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="container py-5">
    <h2 class="h3 fw-bold mb-4">Validation des justificatifs</h2>
    <div class="row g-3 align-items-end mb-4">
        <div class="col-md-4">
            <input id="filtre-nom" type="text" class="form-control" placeholder="Filtrer par nom ou email">
        </div>
        <div class="col-md-3">
            <select id="filtre-type" class="form-select">
                <option value="">Tous les types</option>
                <option value="CNI">CNI</option>
                <option value="Permis">Permis</option>
                <option value="Carte Grise">Carte Grise</option>
                <option value="Assurance">Assurance</option>
                <option value="Autre">Autre</option>
            </select>
        </div>
        <div class="col-md-3">
            <select id="filtre-statut" class="form-select">
                <option value="">Tous les statuts</option>
                <option value="En attente">En attente</option>
                <option value="Validé">Validé</option>
                <option value="Refusé">Refusé</option>
            </select>
        </div>
        <div class="col-md-2">
            <button id="reset" class="btn btn-outline-secondary w-100">Réinitialiser</button>
        </div>
    </div>
    <div class="table-responsive shadow rounded">
        <table class="table table-hover align-middle mb-0 bg-white">
            <thead class="table-light">
                <tr>
                    <th>Propriétaire</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="tbody-justificatifs"></tbody>
        </table>
    </div>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";
let justificatifs = [];

function getStatutBadge(statut) {
    if (statut === 'Validé') return '<span class="badge bg-success">Validé</span>';
    if (statut === 'Refusé') return '<span class="badge bg-danger">Refusé</span>';
    return '<span class="badge bg-secondary">En attente</span>';
}

async function chargerJustificatifs() {
    const res = await fetch(`${API_BASE}/admin/justificatifs`, { credentials: 'include' });
    const data = await res.json();
    if (data.success) {
        justificatifs = data.justificatifs;
        afficherJustificatifs();
    } else {
        alert('Erreur lors du chargement.');
    }
}

function afficherJustificatifs() {
    const nom = document.getElementById('filtre-nom').value.trim().toLowerCase();
    const type = document.getElementById('filtre-type').value;
    const statut = document.getElementById('filtre-statut').value;
    const tbody = document.getElementById('tbody-justificatifs');
    tbody.innerHTML = '';
    const filtres = justificatifs.filter(j => {
        const nomComplet = `${j.first_name} ${j.last_name}`.toLowerCase();
        const mail       = j.email.toLowerCase();
        return (!nom || nomComplet.includes(nom) || mail.includes(nom))
            && (!type || j.type === type)
            && (!statut || j.statut === statut);
    });
    if (filtres.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">Aucun justificatif trouvé.</td></tr>`;
        return;
    }
    filtres.forEach(j => {
        tbody.innerHTML += `
            <tr>
                <td>
                  ${j.first_name} ${j.last_name}<br>
                  <span class="text-muted small">${j.email}</span>
                </td>
                <td>${j.type}</td>
                <td>${j.description ?? '—'}</td>
                <td>${new Date(j.created_at).toLocaleDateString()}</td>
                <td>${getStatutBadge(j.statut)}</td>
                <td class="text-end">
                    <button onclick="voirJustificatif('${j.filename}')" class="btn btn-outline-secondary btn-sm me-1">
                      <i class="bi bi-eye"></i> Voir
                    </button>
                    <a href="${API_BASE}/justificatifs/${j.id}/telecharger" target="_blank" class="btn btn-outline-primary btn-sm me-1"><i class="bi bi-download"></i> Télécharger</a>
                    ${j.statut === 'En attente' ? `
                        <button onclick="changerStatut(${j.id}, 'Validé')" class="btn btn-outline-success btn-sm me-1">Valider</button>
                        <button onclick="changerStatut(${j.id}, 'Refusé')" class="btn btn-outline-danger btn-sm">Refuser</button>
                    ` : ''}
                </td>
            </tr>
        `;
    });
}

async function changerStatut(id, nouveauStatut) {
    const res = await fetch(`${API_BASE}/admin/justificatifs/statut`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ id_justificatif: id, statut: nouveauStatut })
    });
    const result = await res.json();
    alert(result.message);
    if (result.success) chargerJustificatifs();
}

document.getElementById('filtre-nom').addEventListener('input', afficherJustificatifs);
document.getElementById('filtre-type').addEventListener('change', afficherJustificatifs);
document.getElementById('filtre-statut').addEventListener('change', afficherJustificatifs);
document.getElementById('reset').addEventListener('click', () => {
    document.getElementById('filtre-nom').value = '';
    document.getElementById('filtre-type').value = '';
    document.getElementById('filtre-statut').value = '';
    afficherJustificatifs();
});

// Ajout des icônes Bootstrap Icons
const biLink = document.createElement('link');
biLink.rel = 'stylesheet';
biLink.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css';
document.head.appendChild(biLink);

chargerJustificatifs();

// Ajoute la fonction JS pour l'aperçu
function voirJustificatif(filename) {
    if (!filename) {
        document.getElementById('contenuApercu').innerHTML = '<div class="text-danger">Aucun fichier disponible.</div>';
    } else {
        // Correction du chemin d'accès au fichier
        const url = '<?= str_replace("/api", "", BASE_API) ?>/storage/justificatifs/' + filename;
        let contenu = '';
        if (filename.match(/\.(jpg|jpeg|png|gif)$/i)) {
            contenu = `<img src="${url}" alt="Justificatif" class="img-fluid" style="max-height:70vh;">`;
        } else if (filename.match(/\.pdf$/i)) {
            contenu = `<iframe src="${url}" style="width:100%;height:70vh;" frameborder="0"></iframe>`;
        } else {
            contenu = `<a href="${url}" target="_blank">Voir le fichier</a>`;
        }
        document.getElementById('contenuApercu').innerHTML = contenu;
    }
    var modal = new bootstrap.Modal(document.getElementById('modalApercu'));
    modal.show();
}
</script>

<!-- Modale d'aperçu -->
<div class="modal fade" id="modalApercu" tabindex="-1" aria-labelledby="modalApercuLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalApercuLabel">Aperçu du justificatif</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body text-center" id="contenuApercu"></div>
    </div>
  </div>
</div>

<?php include_once('includes/admin_footer.php'); ?>
