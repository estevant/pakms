<?php include_once('includes/admin_header.php'); ?>
<?php include_once('includes/api_path.php'); ?>

<!-- Bootstrap 5 CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold mb-0">Comptes Prestataires</h1>
        <div class="w-50">
            <input type="text" id="search-input" class="form-control" placeholder="Rechercher par nom, prénom ou email...">
        </div>
    </div>
    <div id="error-msg" class="alert alert-danger d-none"></div>
    <div class="table-responsive shadow rounded">
        <table class="table table-hover align-middle mb-0 bg-white">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>État</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="prestataires-body"></tbody>
        </table>
    </div>
</div>

<script>
const API_BASE = "<?php echo BASE_API; ?>";
const body = document.getElementById('prestataires-body');
const errorMsg = document.getElementById('error-msg');
const searchInput = document.getElementById('search-input');
let allPrestataires = [];

// Ajout : fonction pour initialiser la session côté API
async function ensureApiSession() {
    await fetch(`${API_BASE}/get_session`, {
        method: 'GET',
        credentials: 'include'
    });
}

function getEtatBadge(val) {
    if (val == 1) return '<span class="badge bg-success">Validé</span>';
    if (val == -1) return '<span class="badge bg-warning text-dark">Refusé</span>';
    return '<span class="badge bg-secondary">En attente</span>';
}

function renderPrestataires(list) {
    body.innerHTML = '';
    if (list.length === 0) {
        body.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Aucun prestataire trouvé.</td></tr>';
        return;
    }
    list.forEach(p => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="fw-semibold">${p.last_name}</td>
            <td>${p.first_name}</td>
            <td>
                <a href="detail_prestataire.php?id=${p.user_id}" class="text-decoration-none text-primary">${p.email}</a>
            </td>
            <td>${p.phone ?? ''}</td>
            <td>${getEtatBadge(p.is_validated)}</td>
            <td class="text-end">
                <button onclick="valider(${p.user_id})" class="btn btn-outline-success btn-sm me-1" title="Valider"><i class="bi bi-check-circle"></i></button>
                <button onclick="refuser(${p.user_id})" class="btn btn-outline-warning btn-sm me-1" title="Refuser"><i class="bi bi-x-circle"></i></button>
                <button onclick="supprimer(${p.user_id})" class="btn btn-outline-danger btn-sm me-1" title="Supprimer"><i class="bi bi-trash"></i></button>
                <a href="edit_prestataire.php?id=${p.user_id}" class="btn btn-outline-primary btn-sm" title="Modifier"><i class="bi bi-pencil"></i></a>
            </td>
        `;
        body.appendChild(tr);
    });
}

function getEtatText(val) {
    if (val == 1) return "Accepté";
    if (val == -1) return "Refusé";
    return "En attente";
}

async function chargerPrestataires() {
    try {
        const res = await fetch(`${API_BASE}/prestataires`, { credentials: 'include' });
        const data = await res.json();
        allPrestataires = data;
        renderPrestataires(data);
        errorMsg.classList.add('d-none');
    } catch (err) {
        errorMsg.textContent = "Erreur de chargement.";
        errorMsg.classList.remove('d-none');
        body.innerHTML = '';
    }
}

searchInput.addEventListener('input', function() {
    const q = this.value.trim().toLowerCase();
    const filtered = allPrestataires.filter(p =>
        (p.last_name && p.last_name.toLowerCase().includes(q)) ||
        (p.first_name && p.first_name.toLowerCase().includes(q)) ||
        (p.email && p.email.toLowerCase().includes(q))
    );
    renderPrestataires(filtered);
});


async function valider(id) {
    try {
        await fetch(`${API_BASE}/admin/prestataires/${id}/valider`, {
            method: 'PATCH',
            credentials: 'include'
        });
        chargerPrestataires();
    } catch (err) {
        errorMsg.textContent = "Erreur lors de la validation.";
        errorMsg.classList.remove('d-none');
        console.error(err);
    }
}

async function refuser(id) {
    try {
        await fetch(`${API_BASE}/admin/prestataires/${id}/refuser`, {
            method: 'PATCH',
            credentials: 'include'
        });
        chargerPrestataires();
    } catch (err) {
        errorMsg.textContent = "Erreur lors du refus.";
        errorMsg.classList.remove('d-none');
        console.error(err);
    }
}

async function supprimer(id) {
    if (!confirm("Supprimer ce compte ?")) return;
    try {
        await fetch(`${API_BASE}/admin/prestataires/${id}`, {
            method: 'DELETE',
            credentials: 'include'
        });
        chargerPrestataires();
    } catch (err) {
        errorMsg.textContent = "Erreur lors de la suppression.";
        errorMsg.classList.remove('d-none');
        console.error(err);
    }
}

// Ajout des icônes Bootstrap Icons
const biLink = document.createElement('link');
biLink.rel = 'stylesheet';
biLink.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css';
document.head.appendChild(biLink);

// Au chargement de la page, on s'assure que la session API est initialisée avant de charger les prestataires
window.addEventListener('DOMContentLoaded', async () => {
    await ensureApiSession();
    chargerPrestataires();
});
</script>

<?php include_once('includes/admin_footer.php'); ?>
