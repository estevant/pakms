<?php include_once('includes/admin_header.php'); ?>
<?php include_once('includes/api_path.php'); ?>

<!-- Bootstrap 5 CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="container py-5">
    <button onclick="history.back()" class="btn btn-link mb-3 p-0"><i class="bi bi-arrow-left"></i> Retour</button>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <img id="avatar" src="" alt="Photo" class="rounded-circle border me-4" style="width: 90px; height: 90px; object-fit: cover;">
                        <div>
                            <h1 id="name" class="h4 fw-bold mb-1">Chargement…</h1>
                            <div id="statut-badge"></div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <div class="text-muted small">Email</div>
                            <div id="email" class="fw-semibold"></div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="text-muted small">Téléphone</div>
                            <div id="phone"></div>
                        </div>
                    </div>
                    <div id="error-msg" class="alert alert-danger d-none"></div>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h5 mb-0">Justificatifs</h2>
                    </div>
                    <ul id="justificatifs-list" class="list-group list-group-flush"></ul>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h5 mb-0">Prestations</h2>
                        <button id="btn-ajout-prestation" class="btn btn-outline-primary btn-sm"><i class="bi bi-plus-circle"></i> Ajouter</button>
                    </div>
                    <ul id="prestations-list" class="list-group list-group-flush mb-3"></ul>
                    <div id="ajout-prestation-container"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";
const params   = new URLSearchParams(window.location.search);
const id       = params.get('id');
const nameEl   = document.getElementById('name');
const avatarEl = document.getElementById('avatar');
const statutBadge = document.getElementById('statut-badge');
const emailEl  = document.getElementById('email');
const phoneEl  = document.getElementById('phone');
const errorEl  = document.getElementById('error-msg');
const justifList = document.getElementById('justificatifs-list');
const prestaList = document.getElementById('prestations-list');
const btnAjout = document.getElementById('btn-ajout-prestation');
const ajoutPrestaContainer = document.getElementById('ajout-prestation-container');

// Ajout : fonction pour initialiser la session côté API
async function ensureApiSession() {
    await fetch(`${API_BASE}/get_session`, {
        method: 'GET',
        credentials: 'include'
    });
}

function getStatutBadge(val) {
    if (val == 1) return '<span class="badge bg-success">Validé</span>';
    if (val == -1) return '<span class="badge bg-warning text-dark">Refusé</span>';
    return '<span class="badge bg-secondary">En attente</span>';
}

async function loadDetail() {
    if (!id) {
        errorEl.textContent = "ID manquant";
        errorEl.classList.remove('d-none');
        return;
    }
    try {
        const res = await fetch(`${API_BASE}/admin/prestataires/${id}`, {
            credentials: 'include',
        });
        if (!res.ok) throw new Error("Utilisateur introuvable ou session expirée");
        const data = await res.json();
        // On prend toujours data.prestataire
        const u = data.prestataire;
        if (!u) throw new Error("Prestataire introuvable");

        nameEl.textContent = `${u.prenom ?? ''} ${u.nom ?? ''}`;
        avatarEl.src = u.profile_picture
            ? `${API_BASE}/storage/${u.profile_picture}`
            : `https://ui-avatars.com/api/?name=${encodeURIComponent((u.prenom ?? '')+' '+(u.nom ?? ''))}`;
        statutBadge.innerHTML = getStatutBadge(u.is_validated);
        emailEl.textContent = u.email ?? '';
        phoneEl.textContent = u.phone || '—';

        // Prestations
        prestaList.innerHTML = '';
        if (u.services && u.services.length) {
            u.services.forEach(s => {
                prestaList.innerHTML += `<li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><strong>${s.type_name ?? ''}</strong> — ${s.details || ''} <br><span class='text-muted small'>${s.address || ''}</span></span>
                    <span class="fw-semibold">${(Number(s.fixed_price ?? s.price) || 0).toFixed(2)} €</span>
                </li>`;
            });
        } else {
            prestaList.innerHTML = '<li class="list-group-item text-muted">Aucune prestation</li>';
        }

        // Justificatifs
        justifList.innerHTML = '';
        if (u.justificatifs && u.justificatifs.length) {
            u.justificatifs.forEach(j => {
                justifList.innerHTML += `<li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><strong>${j.type}</strong> — <a href="${API_BASE}/storage/${j.filename}" target="_blank">${j.filename ? j.filename.split('/').pop() : ''}</a></span>
                    <span class="badge ${j.statut === 'Validé' ? 'bg-success' : j.statut === 'Refusé' ? 'bg-danger' : 'bg-secondary'}">${j.statut}</span>
                </li>`;
            });
        } else {
            justifList.innerHTML = '<li class="list-group-item text-muted">Aucun justificatif</li>';
        }
    } catch (e) {
        errorEl.textContent = e.message || "Erreur lors du chargement du profil.";
        errorEl.classList.remove('d-none');
        console.error(e);
    }
}

async function chargerTypesPrestation() {
    const res = await fetch(`${API_BASE}/servicetypes`, { credentials: 'include' });
    if (!res.ok) return [];
    const data = await res.json();
    return data.data || [];
}

function afficherFormulaireAjout() {
    chargerTypesPrestation().then(types => {
        const selectOptions = types.map(t => `<option value="${t.service_type_id}">${t.name}</option>`).join('');
        ajoutPrestaContainer.innerHTML = `
            <form id="form-ajout-prestation" class="border p-4 rounded bg-light mb-3">
                <h3 class="h6 fw-semibold mb-3">Ajouter une prestation</h3>
                <div class="mb-3">
                    <label class="form-label">Type de prestation</label>
                    <select name="service_type_id" id="select-type-prestation" class="form-select" required>
                        <option value="">-- Choisir --</option>
                        ${selectOptions}
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Prix (€)</label>
                    <input type="number" name="price" id="input-prix-prestation" class="form-control" min="0" step="0.01" required>
                </div>
                <div class="d-flex align-items-center">
                    <button type="submit" class="btn btn-success me-2">Ajouter</button>
                    <button type="button" id="annuler-ajout-prestation" class="btn btn-outline-secondary">Annuler</button>
                </div>
                <div id="ajout-prestation-message" class="mt-2 small"></div>
            </form>
        `;
        // Gestion du prix imposé
        const selectType = document.getElementById('select-type-prestation');
        const inputPrix = document.getElementById('input-prix-prestation');
        selectType.onchange = function() {
            const t = types.find(x => x.service_type_id == selectType.value);
            if (t && t.is_price_fixed == 1) {
                inputPrix.value = t.fixed_price;
                inputPrix.readOnly = true;
            } else {
                inputPrix.value = '';
                inputPrix.readOnly = false;
            }
        };
        document.getElementById('annuler-ajout-prestation').onclick = () => {
            ajoutPrestaContainer.innerHTML = '';
        };
        document.getElementById('form-ajout-prestation').onsubmit = async function(e) {
            e.preventDefault();
            const form = e.target;
            const service_type_id = form.service_type_id.value;
            const description = form.description.value;
            const price = form.price.value;
            const res = await fetch(`${API_BASE}/admin/prestataires/${id}/ajouter-prestation`, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ service_type_id, description, price })
            });
            const msgEl = document.getElementById('ajout-prestation-message');
            if (res.ok) {
                msgEl.textContent = 'Prestation ajoutée avec succès.';
                msgEl.className = 'text-success mt-2';
                setTimeout(() => { location.reload(); }, 1000);
            } else {
                const data = await res.json().catch(() => ({}));
                msgEl.textContent = data.message || 'Erreur lors de l\'ajout.';
                msgEl.className = 'text-danger mt-2';
            }
        };
    });
}

btnAjout.onclick = afficherFormulaireAjout;

// Ajout des icônes Bootstrap Icons
const biLink = document.createElement('link');
biLink.rel = 'stylesheet';
biLink.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css';
document.head.appendChild(biLink);

// Au chargement de la page, on s'assure que la session API est initialisée avant de charger le détail
window.addEventListener('DOMContentLoaded', async () => {
    await ensureApiSession();
    loadDetail();
});
</script>

<?php include_once('includes/admin_footer.php'); ?>
