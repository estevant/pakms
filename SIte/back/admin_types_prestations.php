<?php
include_once('includes/admin_header.php');
include_once('includes/api_path.php');
?>
<!-- Bootstrap 5 CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="container py-5">
    <h1 class="h3 fw-bold mb-4">Gestion des types de prestations</h1>
    <div id="error-msg" class="alert alert-danger d-none"></div>
    <button onclick="showAddForm()" class="btn btn-success mb-3"><i class="bi bi-plus-circle"></i> Ajouter un type</button>
    <div class="table-responsive shadow rounded">
        <table class="table table-hover align-middle mb-0 bg-white">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Prix (€)</th>
                    <th>Prix imposé</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="types-body"></tbody>
        </table>
    </div>
</div>

<!-- Modale d'ajout/modification -->
<div id="type-form-modal" class="modal fade" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="form-title" class="modal-title">Ajouter un type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <form id="type-form">
        <div class="modal-body">
          <input type="hidden" id="type-id">
          <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" id="type-nom" required class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea id="type-description" rows="2" class="form-control"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Prix (€)</label>
            <input type="number" id="type-prix" step="0.01" min="0" required class="form-control">
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="type-prix-impose">
            <label class="form-check-label" for="type-prix-impose">Prix imposé</label>
          </div>
          <div id="form-msg" class="small"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";
const body = document.getElementById('types-body');
const errorMsg = document.getElementById('error-msg');
let typeModal;
let allTypes = [];

document.addEventListener('DOMContentLoaded', function() {
    typeModal = new bootstrap.Modal(document.getElementById('type-form-modal'));
});

function showAddForm() {
    document.getElementById('form-title').textContent = 'Ajouter un type';
    document.getElementById('type-id').value = '';
    document.getElementById('type-nom').value = '';
    document.getElementById('type-description').value = '';
    document.getElementById('type-prix').value = '';
    document.getElementById('type-prix-impose').checked = false;
    document.getElementById('form-msg').textContent = '';
    typeModal.show();
}
function showEditForm(type) {
    document.getElementById('form-title').textContent = 'Modifier le type';
    document.getElementById('type-id').value = type.service_type_id;
    document.getElementById('type-nom').value = type.name;
    document.getElementById('type-description').value = type.description || '';
    document.getElementById('type-prix').value = type.fixed_price ?? type.price ?? '';
    document.getElementById('type-prix-impose').checked = !!type.is_price_fixed;
    document.getElementById('form-msg').textContent = '';
    typeModal.show();
}

async function chargerTypes() {
    try {
        const res = await fetch(`${API_BASE}/servicetypes`, { credentials: 'include' });
        const data = await res.json();
        allTypes = data.data; // Correction ici
        body.innerHTML = '';
        allTypes.forEach(type => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${type.name}</td>
                <td>${type.description || ''}</td>
                <td>${type.fixed_price ?? type.price ?? ''}</td>
                <td class="text-center">
                    <input type="checkbox" ${type.is_price_fixed ? 'checked' : ''} onchange="togglePrixFixe(${type.service_type_id}, this.checked)">
                </td>
                <td class="text-end">
                    <button class="btn btn-outline-warning btn-sm me-1 btn-edit-type" data-id="${type.service_type_id}"><i class="bi bi-pencil"></i></button>
                    <button onclick='supprimerType(${type.service_type_id})' class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                </td>
            `;
            body.appendChild(tr);
        });
        // Ajout des listeners après le rendu
        document.querySelectorAll('.btn-edit-type').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const type = allTypes.find(t => t.service_type_id == id);
                showEditForm(type);
            });
        });
        errorMsg.classList.add('d-none');
    } catch (e) {
        body.innerHTML = '<tr><td colspan="4" class="text-danger">Erreur de chargement des types de prestations</td></tr>';
    }
}

async function supprimerType(id) {
    if (!confirm("Supprimer ce type de prestation ?")) return;
    await fetch(`${API_BASE}/servicetypes/${id}`, {
        method: 'DELETE',
        credentials: 'include'
    });
    chargerTypes();
}
async function togglePrixImpose(id, checked) {
    await fetch(`${API_BASE}/servicetypes/${id}`, {
        method: 'PATCH',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_price_fixed: checked ? 1 : 0 })
    });
    chargerTypes();
}
document.getElementById('type-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('type-id').value;
    const nom = document.getElementById('type-nom').value.trim();
    const description = document.getElementById('type-description').value.trim();
    const prix = document.getElementById('type-prix').value;
    const prixImpose = document.getElementById('type-prix-impose').checked;
    const msg = document.getElementById('form-msg');
    if (!nom || !prix) {
        msg.textContent = 'Nom et prix obligatoires.';
        msg.className = 'text-danger';
        return;
    }
    const method = id ? 'PUT' : 'POST';
    const url = id ? `${API_BASE}/servicetypes/${id}` : `${API_BASE}/servicetypes`;
    const res = await fetch(url, {
        method,
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            name: nom,
            description,
            price: parseFloat(prix),
            is_price_fixed: prixImpose ? 1 : 0
        })
    });
    const result = await res.json();
    msg.textContent = result.message;
    msg.className = result.success ? 'text-success' : 'text-danger';
    if (result.success) {
        setTimeout(() => {
            typeModal.hide();
            chargerTypes();
        }, 1000);
    }
});

// Ajout des icônes Bootstrap Icons
const biLink = document.createElement('link');
biLink.rel = 'stylesheet';
biLink.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css';
document.head.appendChild(biLink);

chargerTypes();
</script>
<?php include_once('includes/admin_footer.php'); ?> 