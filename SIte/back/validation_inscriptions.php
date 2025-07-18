<?php
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['statut'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit;
}
include_once('includes/admin_header.php');
include_once('includes/api_path.php');
?>
<!-- Bootstrap 5 CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="container py-5">
    <h1 class="h3 fw-bold mb-4 text-center">Validation des comptes utilisateurs</h1>
    <div id="pending-users"></div>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";
const container = document.getElementById("pending-users");

async function chargerUtilisateurs() {
    container.innerHTML = '<div class="text-center text-muted">Chargement...</div>';
    try {
        const res = await fetch(`${API_BASE}/admin/validations`, { credentials: 'include' });
        const users = await res.json();
        if (!users.length) {
            container.innerHTML = '<div class="alert alert-info text-center">Aucun compte en attente.</div>';
            return;
        }
        container.innerHTML = users.map(user => `
            <div class="card shadow-sm mb-4">
                <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                    <div>
                        <div class="fw-bold mb-1">${user.first_name} ${user.last_name}</div>
                        <div class="text-muted small mb-1">${user.email}</div>
                        <div class="small">Rôle : <span class="badge bg-secondary">${user.role ?? 'Non défini'}</span></div>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <button onclick="valider(${user.user_id})" class="btn btn-success btn-sm me-2"><i class="bi bi-check-circle"></i> Valider</button>
                        <button onclick="refuser(${user.user_id})" class="btn btn-danger btn-sm"><i class="bi bi-x-circle"></i> Refuser</button>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (err) {
        container.innerHTML = '<div class="alert alert-danger text-center">Erreur de chargement.</div>';
    }
}

async function valider(id) {
    if (!confirm("Valider ce compte ?")) return;
    await fetch(`${API_BASE}/admin/validations/${id}`, {
        method: 'PATCH',
        credentials: 'include'
    });
    chargerUtilisateurs();
}

async function refuser(id) {
    if (!confirm("Refuser et supprimer ce compte ?")) return;
    await fetch(`${API_BASE}/admin/validations/${id}`, {
        method: 'DELETE',
        credentials: 'include'
    });
    chargerUtilisateurs();
}

// Ajout des icônes Bootstrap Icons
const biLink = document.createElement('link');
biLink.rel = 'stylesheet';
biLink.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css';
document.head.appendChild(biLink);

chargerUtilisateurs();
</script>

<?php include_once('includes/admin_footer.php'); ?>
