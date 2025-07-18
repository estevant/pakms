<?php
require_once('../includes/header.php');
require_once('../includes/api_path.php');
?>

<div class="max-w-4xl mx-auto mt-8 p-6">
    <h1 class="text-3xl font-bold mb-6 text-center">Demande de rôle supplémentaire</h1>
    
    <div id="current-roles" class="mb-8 p-4 bg-blue-50 rounded-lg">
        <h2 class="text-xl font-semibold mb-2">Mes rôles actuels</h2>
        <div id="current-roles-list" class="text-gray-700"></div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Faire une nouvelle demande</h2>
        
        <form id="role-request-form" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rôle demandé</label>
                <select name="requested_role" required class="w-full p-2 border border-gray-300 rounded-md">
                    <option value="">-- Choisir un rôle --</option>
                    <option value="Customer">Client</option>
                    <option value="Deliverer">Livreur</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Raison de la demande (optionnel)</label>
                <textarea name="reason" rows="3" class="w-full p-2 border border-gray-300 rounded-md" 
                         placeholder="Expliquez pourquoi vous souhaitez ce rôle..."></textarea>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                Faire la demande
            </button>
        </form>

        <div id="request-message" class="mt-4 text-sm font-medium"></div>
    </div>

    <div id="upload-section" class="bg-white rounded-lg shadow p-6 mb-8 hidden">
        <h2 class="text-xl font-semibold mb-4">Upload des justificatifs requis</h2>
        <p class="text-gray-600 mb-4">Votre demande nécessite des justificatifs. Veuillez uploader les documents requis pour finaliser votre demande de changement de rôle.</p>
        <div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-4">
            <p class="text-blue-700 text-sm">
                <strong>📋 Information :</strong> Cette section s'affiche automatiquement si vous avez une demande en attente nécessitant des justificatifs.
            </p>
        </div>
        
        <form id="justificatifs-form" class="space-y-4">
            <input type="hidden" id="current-request-id" name="request_id">
            
            <div id="justificatifs-container">
                <div class="justificatif-item p-4 border border-gray-200 rounded-md">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type de document</label>
                            <select name="types[]" required class="w-full p-2 border border-gray-300 rounded-md">
                                <option value="CNI">Carte Nationale d'Identité</option>
                                <option value="Permis">Permis de conduire</option>
                                <option value="Carte Grise">Carte grise</option>
                                <option value="Assurance">Assurance</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Document</label>
                            <input type="file" name="justificatifs[]" accept=".pdf,.jpg,.jpeg,.png" required 
                                   class="w-full p-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <input type="text" name="descriptions[]" placeholder="Description du document" 
                                   class="w-full p-2 border border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" id="add-justificatif" class="text-blue-600 hover:underline">
                + Ajouter un autre document
            </button>

            <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">
                Uploader les justificatifs
            </button>
        </form>

        <div id="upload-message" class="mt-4 text-sm font-medium"></div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Mes demandes en cours</h2>
        <div id="my-requests" class="space-y-4">
            <p class="text-gray-500">Chargement...</p>
        </div>
    </div>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";
let currentUser = null;

async function loadCurrentUser() {
    try {
        const res = await fetch(`${API_BASE}/get_session`, {
            method: 'GET',
            credentials: 'include'
        });
        const data = await res.json();
        if (data.user) {
            currentUser = data.user;
            displayCurrentRoles();
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

function displayCurrentRoles() {
    const rolesContainer = document.getElementById('current-roles-list');
    if (currentUser && currentUser.roles) {
                 const rolesText = currentUser.roles.map(role => {
             const roleNames = {
                 'Customer': 'Client',
                 'Deliverer': 'Livreur', 
                 'Admin': 'Administrateur'
             };
             return roleNames[role] || role;
         }).join(', ');
        rolesContainer.innerHTML = `<span class="font-semibold">Rôles: ${rolesText}</span>`;
    }
}

document.getElementById('role-request-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const messageDiv = document.getElementById('request-message');
    
    try {
        const res = await fetch(`${API_BASE}/role-change/request`, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        });
        
        const data = await res.json();
        
        if (data.success) {
            messageDiv.innerHTML = `<span class="text-green-600">${data.message}</span>`;
            
            if (data.requires_verification) {
                document.getElementById('current-request-id').value = data.request_id;
                document.getElementById('upload-section').classList.remove('hidden');
            }
            
            loadMyRequests();
            e.target.reset();
        } else {
            messageDiv.innerHTML = `<span class="text-red-600">${data.message}</span>`;
        }
    } catch (error) {
        console.error('Erreur:', error);
        messageDiv.innerHTML = '<span class="text-red-600">Erreur lors de l\'envoi de la demande</span>';
    }
});

document.getElementById('add-justificatif').addEventListener('click', () => {
    const container = document.getElementById('justificatifs-container');
    const newItem = document.querySelector('.justificatif-item').cloneNode(true);
    
    newItem.querySelectorAll('input, select').forEach(input => {
        input.value = '';
    });
    
    container.appendChild(newItem);
});

document.getElementById('justificatifs-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const messageDiv = document.getElementById('upload-message');
    
    try {
        const res = await fetch(`${API_BASE}/role-change/upload-justificatifs`, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        });
        
        const data = await res.json();
        
        if (data.success) {
            messageDiv.innerHTML = `<span class="text-green-600">${data.message}</span>`;
            document.getElementById('upload-section').classList.add('hidden');
            loadMyRequests();
        } else {
            messageDiv.innerHTML = `<span class="text-red-600">${data.message}</span>`;
        }
    } catch (error) {
        console.error('Erreur:', error);
        messageDiv.innerHTML = '<span class="text-red-600">Erreur lors de l\'upload</span>';
    }
});

async function loadMyRequests() {
    try {
        const res = await fetch(`${API_BASE}/role-change/my-requests`, {
            credentials: 'include'
        });
        
        const data = await res.json();
        const container = document.getElementById('my-requests');
        
        if (data.success && data.requests.length > 0) {
           const pendingRequestNeedingDocs = data.requests.find(request => 
                request.status === 'En attente' && 
                request.requires_verification && 
                !request.justificatifs_uploaded
            );
            
            if (pendingRequestNeedingDocs) {
                document.getElementById('current-request-id').value = pendingRequestNeedingDocs.request_id;
                document.getElementById('upload-section').classList.remove('hidden');
            }
            
            container.innerHTML = data.requests.map(request => {
                const statusColors = {
                    'En attente': 'text-yellow-600 bg-yellow-50',
                    'Approuvé': 'text-green-600 bg-green-50',
                    'Refusé': 'text-red-600 bg-red-50',
                    'Annulé': 'text-gray-600 bg-gray-50'
                };
                
                const roleNames = {
                    'Customer': 'Client',
                    'Deliverer': 'Livreur'
                };
                
                let actions = '';
                if (request.status === 'En attente') {
                    actions = `<button onclick="cancelRequest(${request.request_id})" 
                                     class="text-red-600 hover:underline text-sm mr-2">Annuler</button>`;
                    
                    if (request.requires_verification && !request.justificatifs_uploaded) {
                        actions += `<button onclick="showUploadSection(${request.request_id})" 
                                         class="text-blue-600 hover:underline text-sm">Uploader justificatifs</button>`;
                    }
                }
                
                let docsStatus = '';
                if (request.requires_verification) {
                    if (request.justificatifs_uploaded) {
                        docsStatus = '<p class="text-sm text-green-600 mt-1">✓ Justificatifs envoyés</p>';
                    } else {
                        docsStatus = '<p class="text-sm text-orange-600 mt-1">⚠ Justificatifs requis</p>';
                    }
                }
                
                return `
                    <div class="border border-gray-200 rounded-md p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-semibold">${roleNames[request.requested_role] || request.requested_role}</h3>
                                <p class="text-sm text-gray-600">Demandé le ${new Date(request.requested_at).toLocaleDateString()}</p>
                                ${request.reason ? `<p class="text-sm text-gray-700 mt-1">Raison: ${request.reason}</p>` : ''}
                                ${docsStatus}
                                ${request.admin_comment ? `<p class="text-sm text-gray-700 mt-1">Commentaire admin: ${request.admin_comment}</p>` : ''}
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 rounded-full text-xs font-medium ${statusColors[request.status] || 'text-gray-600 bg-gray-50'}">
                                    ${request.status}
                                </span>
                                <div class="mt-2">${actions}</div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            container.innerHTML = '<p class="text-gray-500">Aucune demande en cours</p>';
        }
    } catch (error) {
        console.error('Erreur:', error);
        document.getElementById('my-requests').innerHTML = '<p class="text-red-500">Erreur lors du chargement</p>';
    }
}

async function cancelRequest(requestId) {
    if (!confirm('Êtes-vous sûr de vouloir annuler cette demande ?')) {
        return;
    }
    
    try {
        const res = await fetch(`${API_BASE}/role-change/${requestId}/cancel`, {
            method: 'PATCH',
            credentials: 'include'
        });
        
        const data = await res.json();
        
        if (data.success) {
            const currentRequestId = document.getElementById('current-request-id').value;
            if (currentRequestId == requestId) {
                document.getElementById('upload-section').classList.add('hidden');
                document.getElementById('current-request-id').value = '';
            }
            loadMyRequests();
        } else {
            alert('Erreur lors de l\'annulation: ' + data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de l\'annulation');
    }
}

function showUploadSection(requestId) {
    document.getElementById('current-request-id').value = requestId;
    document.getElementById('upload-section').classList.remove('hidden');
    
    document.getElementById('upload-section').scrollIntoView({ 
        behavior: 'smooth', 
        block: 'start' 
    });
}

document.addEventListener('DOMContentLoaded', () => {
    loadCurrentUser();
    loadMyRequests();
});
</script>

<?php require_once('../includes/footer.php'); ?> 