<?php
require_once('includes/admin_header.php');
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Gestion des demandes de rôles</h1>
    
    <div class="mb-6 p-4 bg-white rounded-lg shadow">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                <select id="filter-status" class="w-full p-2 border border-gray-300 rounded-md">
                    <option value="">Tous les statuts</option>
                    <option value="En attente">En attente</option>
                    <option value="Approuvé">Approuvé</option>
                    <option value="Refusé">Refusé</option>
                    <option value="Annulé">Annulé</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rôle demandé</label>
                <select id="filter-role" class="w-full p-2 border border-gray-300 rounded-md">
                    <option value="">Tous les rôles</option>
                    <option value="Customer">Client</option>
                    <option value="Deliverer">Livreur</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vérification requise</label>
                <select id="filter-verification" class="w-full p-2 border border-gray-300 rounded-md">
                    <option value="">Toutes</option>
                    <option value="1">Avec vérification</option>
                    <option value="0">Sans vérification</option>
                </select>
            </div>
            <div class="flex items-end">
                <button id="apply-filters" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                    Appliquer les filtres
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle demandé</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vérification</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="requests-table-body" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Chargement...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="details-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-xl font-bold">Détails de la demande</h2>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div id="modal-content">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="action-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="p-6">
                <h2 id="action-modal-title" class="text-xl font-bold mb-4"></h2>
                
                <form id="action-form">
                    <input type="hidden" id="action-request-id">
                    <input type="hidden" id="action-type">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Commentaire</label>
                        <textarea id="admin-comment" rows="3" class="w-full p-2 border border-gray-300 rounded-md" 
                                 placeholder="Commentaire pour l'utilisateur..."></textarea>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="submit" id="action-submit-btn" class="flex-1 py-2 px-4 rounded-md text-white font-medium">
                            Confirmer
                        </button>
                        <button type="button" onclick="closeActionModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md font-medium">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const BASE_API = "<?= BASE_API ?>";
let allRequests = [];
let filteredRequests = [];

async function loadRoleRequests() {
    try {
        const res = await fetch(`${BASE_API}/admin/role-change/`, {
            credentials: 'include'
        });
        
        const data = await res.json();
        
        if (data.success) {
            allRequests = data.requests;
            filteredRequests = [...allRequests];
            displayRequests();
        } else {
            console.error('Erreur:', data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        document.getElementById('requests-table-body').innerHTML = 
            '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Erreur lors du chargement</td></tr>';
    }
}

function displayRequests() {
    const tbody = document.getElementById('requests-table-body');
    
    if (filteredRequests.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Aucune demande trouvée</td></tr>';
        return;
    }
    
    const roleNames = {
        'Customer': 'Client',
        'Deliverer': 'Livreur'
    };
    
    const statusColors = {
        'En attente': 'bg-yellow-100 text-yellow-800',
        'Approuvé': 'bg-green-100 text-green-800',
        'Refusé': 'bg-red-100 text-red-800',
        'Annulé': 'bg-gray-100 text-gray-800'
    };
    
    tbody.innerHTML = filteredRequests.map(request => {
        const user = request.user;
        const userName = `${user.first_name} ${user.last_name}`;
        const requestDate = new Date(request.requested_at).toLocaleDateString();
        
        let actions = '';
        if (request.status === 'En attente') {
            actions = `
                <button onclick="openActionModal(${request.request_id}, 'approve')" 
                        class="text-green-600 hover:text-green-900 mr-2">Approuver</button>
                <button onclick="openActionModal(${request.request_id}, 'reject')" 
                        class="text-red-600 hover:text-red-900">Refuser</button>
            `;
        }
        
        return `
            <tr>
                <td class="px-6 py-4">
                    <div>
                        <div class="text-sm font-medium text-gray-900">${userName}</div>
                        <div class="text-sm text-gray-500">${user.email}</div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="text-sm font-medium">${roleNames[request.requested_role]}</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">${requestDate}</td>
                <td class="px-6 py-4">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusColors[request.status]}">
                        ${request.status}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm">
                    ${request.requires_verification ? 
                        `<span class="text-orange-600">Requis ${request.justificatifs_uploaded ? 
                            `<span class="text-green-600">✓ ${request.justificatifs ? request.justificatifs.length : 0} fichier(s)</span>` : 
                            '<span class="text-red-600">✗ Manquants</span>'}</span>` : 
                        '<span class="text-gray-500">Non requis</span>'
                    }
                </td>
                <td class="px-6 py-4 text-sm">
                    <button onclick="showDetails(${request.request_id})" 
                            class="text-blue-600 hover:text-blue-900 mr-2">Voir détails</button>
                    ${actions}
                </td>
            </tr>
        `;
    }).join('');
}

function applyFilters() {
    const statusFilter = document.getElementById('filter-status').value;
    const roleFilter = document.getElementById('filter-role').value;
    const verificationFilter = document.getElementById('filter-verification').value;
    
    filteredRequests = allRequests.filter(request => {
        if (statusFilter && request.status !== statusFilter) return false;
        if (roleFilter && request.requested_role !== roleFilter) return false;
        if (verificationFilter !== '' && request.requires_verification.toString() !== verificationFilter) return false;
        return true;
    });
    
    displayRequests();
}

function showDetails(requestId) {
    const request = allRequests.find(r => r.request_id === requestId);
    if (!request) return;
    
    const user = request.user;
    const roleNames = {
        'Customer': 'Client',
        'Deliverer': 'Livreur'
    };
    
    let justificatifsHtml = '';
    if (request.justificatifs && request.justificatifs.length > 0) {
        justificatifsHtml = `
            <div class="mt-4">
                <h4 class="font-semibold mb-2">Justificatifs uploadés:</h4>
                <ul class="space-y-2">
                    ${request.justificatifs.map(j => `
                        <li class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <div class="flex-1">
                                <span class="font-medium">${j.type}</span>
                                ${j.description ? `<br><span class="text-sm text-gray-600">${j.description}</span>` : ''}
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-500">${j.statut}</span>
                                <a href="${BASE_API}/admin/role-change/justificatif/${j.id}" 
                                   target="_blank" 
                                   class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                    📄 Voir fichier
                                </a>
                            </div>
                        </li>
                    `).join('')}
                </ul>
            </div>
        `;
    }
    
    const currentRoles = request.current_roles ? JSON.parse(request.current_roles) : [];
    
    document.getElementById('modal-content').innerHTML = `
        <div class="space-y-4">
            <div>
                <h3 class="font-semibold">Utilisateur</h3>
                <p>${user.first_name} ${user.last_name} (${user.email})</p>
            </div>
            
            <div>
                <h3 class="font-semibold">Rôles actuels</h3>
                <p>${currentRoles.map(role => roleNames[role] || role).join(', ') || 'Aucun'}</p>
            </div>
            
            <div>
                <h3 class="font-semibold">Rôle demandé</h3>
                <p>${roleNames[request.requested_role]}</p>
            </div>
            
            <div>
                <h3 class="font-semibold">Date de demande</h3>
                <p>${new Date(request.requested_at).toLocaleString()}</p>
            </div>
            
            ${request.reason ? `
                <div>
                    <h3 class="font-semibold">Raison</h3>
                    <p>${request.reason}</p>
                </div>
            ` : ''}
            
            <div>
                <h3 class="font-semibold">Vérification requise</h3>
                <p>${request.requires_verification ? 'Oui' : 'Non'}</p>
            </div>
            
            ${justificatifsHtml}
            
            ${request.admin_comment ? `
                <div>
                    <h3 class="font-semibold">Commentaire admin</h3>
                    <p>${request.admin_comment}</p>
                </div>
            ` : ''}
        </div>
    `;
    
    document.getElementById('details-modal').classList.remove('hidden');
}

function openActionModal(requestId, action) {
    const request = allRequests.find(r => r.request_id === requestId);
    if (!request) return;
    
    document.getElementById('action-request-id').value = requestId;
    document.getElementById('action-type').value = action;
    
    const modal = document.getElementById('action-modal');
    const title = document.getElementById('action-modal-title');
    const submitBtn = document.getElementById('action-submit-btn');
    const commentField = document.getElementById('admin-comment');
    
    if (action === 'approve') {
        title.textContent = 'Approuver la demande';
        submitBtn.textContent = 'Approuver';
        submitBtn.className = 'flex-1 bg-green-600 hover:bg-green-700 py-2 px-4 rounded-md text-white font-medium';
        commentField.placeholder = 'Commentaire (optionnel)';
        commentField.required = false;
    } else {
        title.textContent = 'Refuser la demande';
        submitBtn.textContent = 'Refuser';
        submitBtn.className = 'flex-1 bg-red-600 hover:bg-red-700 py-2 px-4 rounded-md text-white font-medium';
        commentField.placeholder = 'Motif de refus (requis)';
        commentField.required = true;
    }
    
    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('details-modal').classList.add('hidden');
}

function closeActionModal() {
    document.getElementById('action-modal').classList.add('hidden');
    document.getElementById('action-form').reset();
}

async function processAction(event) {
    event.preventDefault();
    
    const requestId = document.getElementById('action-request-id').value;
    const actionType = document.getElementById('action-type').value;
    const comment = document.getElementById('admin-comment').value;
    
    if (actionType === 'reject' && !comment.trim()) {
        alert('Le motif de refus est requis');
        return;
    }
    
    try {
        const endpoint = actionType === 'approve' ? 'approve' : 'reject';
        const res = await fetch(`${BASE_API}/admin/role-change/${requestId}/${endpoint}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({
                admin_comment: comment
            })
        });
        
        const data = await res.json();
        
        if (data.success) {
            closeActionModal();
            loadRoleRequests();
            alert(`Demande ${actionType === 'approve' ? 'approuvée' : 'refusée'} avec succès`);
        } else {
            alert('Erreur: ' + data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du traitement de la demande');
    }
}

document.getElementById('apply-filters').addEventListener('click', applyFilters);
document.getElementById('action-form').addEventListener('submit', processAction);

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeModal();
        closeActionModal();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    loadRoleRequests();
});
</script>

<?php require_once('includes/admin_footer.php'); ?> 