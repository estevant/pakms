<?php
include_once('includes/admin_header.php');
include_once('includes/api_path.php');
?>
<div class="container py-5">
    <div class="row justify-content-center mb-4">
        <div class="col-lg-8">
            <div class="card shadow border-0">
                <div class="card-body">
                    <h2 class="h3 fw-bold text-center mb-4 text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Alertes de modération automatique
                    </h2>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-danger">
                                <tr>
                                    <th scope="col">Utilisateur</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Mot incriminé</th>
                                    <th scope="col">Date</th>
                                </tr>
                            </thead>
                            <tbody id="alerts-table-body">
                                <tr><td colspan="4" class="text-center py-4 text-muted">Chargement...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<script>
const BASE_API = "<?= BASE_API ?>";

function truncate(text, max = 80) {
    return text && text.length > max ? text.substr(0, max) + '…' : text || '';
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getInitials(firstName, lastName) {
    let initials = '';
    if (firstName) initials += firstName.charAt(0).toUpperCase();
    if (lastName) initials += lastName.charAt(0).toUpperCase();
    return initials || '?';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

async function ensureApiSession() {
    try {
        await fetch(`${BASE_API}/get_session`, {
            method: 'GET',
            credentials: 'include'
        });
    } catch (error) {
        console.error('Erreur lors de l\'initialisation de la session:', error);
    }
}

async function chargerAlertes() {
    const tbody = document.getElementById('alerts-table-body');
    
    try {
        await ensureApiSession();
        
        const response = await fetch(`${BASE_API}/admin/alerts`, {
            method: 'GET',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Erreur lors de la récupération des données');
        }

        const alerts = data.alerts || [];

        if (alerts.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Aucune alerte.</td></tr>';
            return;
        }

        tbody.innerHTML = alerts.map(alert => `
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:40px;height:40px;font-weight:bold;font-size:1.1rem;">
                            ${getInitials(alert.first_name, alert.last_name)}
                        </div>
                        <div>
                            <div class="fw-bold mb-1" style="font-size:1.05rem;">
                                ${escapeHtml((alert.first_name || '') + ' ' + (alert.last_name || ''))}
                            </div>
                            <div class="text-muted small mb-1">
                                <i class="bi bi-envelope me-1"></i>${alert.email ? escapeHtml(alert.email) : '<span class="text-danger">Aucun email</span>'}
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-telephone me-1"></i>${alert.phone ? escapeHtml(alert.phone) : '<span class="text-danger">Aucun numéro</span>'}
                            </div>
                        </div>
                    </div>
                </td>
                <td style="max-width:300px;">
                    <span title="${escapeHtml(alert.description || '')}">
                        ${escapeHtml(truncate(alert.description, 80))}
                    </span>
                </td>
                <td>
                    <span class="badge bg-danger fs-6">${escapeHtml(alert.mot_incrimine || '')}</span>
                </td>
                <td>
                    <span class="text-muted small">${formatDate(alert.created_at)}</span>
                </td>
            </tr>
        `).join('');

    } catch (error) {
        console.error('Erreur lors du chargement des alertes:', error);
        tbody.innerHTML = `
            <tr><td colspan="4" class="text-center py-4 text-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>Erreur de connexion à l'API: ${escapeHtml(error.message)}
            </td></tr>
        `;
    }
}

document.addEventListener('DOMContentLoaded', chargerAlertes);
</script>

<?php include_once('includes/admin_footer.php'); ?> 