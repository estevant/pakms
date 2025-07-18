<?php include_once('includes/admin_header.php'); ?>
<?php include_once('includes/api_path.php'); ?>

<!-- Bootstrap 5 CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="container py-5">
    <h1 class="h3 fw-bold mb-4">Demandes d’ajout de prestations</h1>
    <div class="row mb-3">
        <div class="col-md-6">
            <input type="text" id="search-input" class="form-control" placeholder="Rechercher par nom, email ou type...">
        </div>
    </div>
    <div id="error-msg" class="alert alert-danger d-none"></div>
    <div class="table-responsive shadow rounded">
        <table class="table table-hover align-middle mb-0 bg-white">
            <thead class="table-light">
                <tr>
                    <th>Prestataire</th>
                    <th>Nom du type</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Justificatif</th>
                    <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody id="propositions-body"></tbody>
    </table>
    </div>
</div>

<script>
const API_BASE = "<?php echo BASE_API; ?>";
const body = document.getElementById('propositions-body');
const errorMsg = document.getElementById('error-msg');

function getStatutBadge(val) {
    if (val === 'Validé') return '<span class="badge bg-success">Validé</span>';
    if (val === 'Refusé') return '<span class="badge bg-danger">Refusé</span>';
    return '<span class="badge bg-secondary">En attente</span>';
}

let allPropositions = [];

function renderPropositions(list) {
    body.innerHTML = '';
    if (!list.length) {
        body.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Aucune proposition en attente</td></tr>';
        return;
    }
    list.forEach(p => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <div class="fw-semibold">${p.prestataire_nom}</div>
                <div class="text-muted small">${p.prestataire_email}</div>
            </td>
            <td class="fw-semibold">${p.nom}</td>
            <td><div class="text-truncate" style="max-width: 200px;" title="${p.description || 'Aucune description'}">${p.description || 'Aucune description'}</div></td>
            <td class="text-nowrap">${new Date(p.created_at).toLocaleDateString('fr-FR')}</td>
            <td>${getStatutBadge(p.statut)}</td>
            <td>
                ${p.justificatif_url ? `<a href="${p.justificatif_url}" target="_blank" class="text-primary text-decoration-underline">Voir</a>` : '<span class="text-muted">Aucun</span>'}
            </td>
            <td class="text-end">
                <button onclick="valider(${p.id})" class="btn btn-outline-success btn-sm me-1" title="Valider"><i class="bi bi-check-circle"></i></button>
                <button onclick="refuser(${p.id})" class="btn btn-outline-danger btn-sm" title="Refuser"><i class="bi bi-x-circle"></i></button>
            </td>
        `;
        body.appendChild(tr);
    });
}

async function chargerPropositions() {
    try {
        const res = await fetch(`${API_BASE}/admin/propositions-types`, { credentials: 'include' });
        const data = await res.json();
        allPropositions = data.data || [];
        renderPropositions(allPropositions);
        errorMsg.classList.add('d-none');
    } catch (err) {
        errorMsg.textContent = "Erreur de chargement.";
        errorMsg.classList.remove('d-none');
        body.innerHTML = '';
    }
}

document.getElementById('search-input').addEventListener('input', function() {
    const q = this.value.trim().toLowerCase();
    const filtered = allPropositions.filter(p =>
        (p.prestataire_nom && p.prestataire_nom.toLowerCase().includes(q)) ||
        (p.prestataire_email && p.prestataire_email.toLowerCase().includes(q)) ||
        (p.nom && p.nom.toLowerCase().includes(q))
    );
    renderPropositions(filtered);
});

async function valider(id) {
    const motif = prompt("Motif de validation (optionnel) :");
    if (motif === null) return;
    try {
        const res = await fetch(`${API_BASE}/admin/propositions-types/${id}/valider`, {
            method: 'PATCH',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ motif: motif })
        });
        const data = await res.json();
        if (data.success) {
            chargerPropositions();
        } else {
            alert('Erreur lors de la validation : ' + (data.message || 'Erreur inconnue'));
        }
    } catch (err) {
        alert('Erreur de connexion lors de la validation');
    }
}

async function refuser(id) {
    const motif = prompt("Motif du refus (obligatoire) :");
    if (!motif || motif.trim() === '') {
        alert('Le motif du refus est obligatoire');
        return;
    }
    try {
        const res = await fetch(`${API_BASE}/admin/propositions-types/${id}/refuser`, {
            method: 'PATCH',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ motif: motif })
        });
        const data = await res.json();
        if (data.success) {
            chargerPropositions();
        } else {
            alert('Erreur lors du refus : ' + (data.message || 'Erreur inconnue'));
        }
    } catch (err) {
        alert('Erreur de connexion lors du refus');
    }
}

// Ajout des icônes Bootstrap Icons
const biLink = document.createElement('link');
biLink.rel = 'stylesheet';
biLink.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css';
document.head.appendChild(biLink);

chargerPropositions();
</script>

<?php include_once('includes/admin_footer.php'); ?> 