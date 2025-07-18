<?php
require_once('includes/admin_header.php');
?> <div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Gestion des utilisateurs bannis</h1>
    <div class="mb-4">
        <input type="text" id="filtre" placeholder="Filtrer par nom, email ou rôle..."
            class="w-full p-2 border rounded">
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded shadow">
            <thead class="bg-gray-100 text-gray-700 text-left">
                <tr>
                    <th class="p-3">ID</th>
                    <th class="p-3">Nom</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Rôle(s)</th>
                    <th class="p-3">Statut</th>
                    <th class="p-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody id="userBody" class="text-gray-800"></tbody>
        </table>
    </div>
</div>
<script>
const BASE_API = "<?= BASE_API ?>";
const CONNECTED_USER_ID = <?= $_SESSION['user_id'] ?? 'null' ?>;
let users = [];
async function chargerUtilisateurs() {
    try {
        const res = await fetch(`${BASE_API}/admin/users`, {
            credentials: 'include'
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error('Erreur API');
        users = data.users ?? [];
        afficherUtilisateurs();
    } catch (e) {
        console.error(e);
        document.getElementById('userBody').innerHTML = `
            <tr><td colspan="6" class="p-4 text-red-600">Erreur de chargement des utilisateurs</td></tr>
        `;
    }
}

function afficherUtilisateurs() {
    const filtre = document.getElementById('filtre').value.toLowerCase();
    const body = document.getElementById('userBody');
    const filtres = users.filter(u => u.name.toLowerCase().includes(filtre) || u.email.toLowerCase().includes(filtre) ||
        ((u.roles || []).join(', ').toLowerCase().includes(filtre)));
    body.innerHTML = filtres.map(u => {
        const isAdmin = (u.roles || []).some(r => r.toLowerCase() === 'admin');
        const isSelf = String(u.user_id) === String(CONNECTED_USER_ID);
        const statutClass = u.banned ? 'text-red-600' : 'text-green-600';
        let actionButton;
        if (isAdmin) {
            actionButton = '<button disabled class="text-gray-400 cursor-not-allowed">Protégé</button>';
        } else if (isSelf) {
            actionButton = '<button disabled class="text-gray-400 cursor-not-allowed">Vous-même</button>';
        } else {
            actionButton = `
                <button
                    onclick="toggleBan(${u.user_id})"
                    class="${u.banned ? 'text-green-600' : 'text-red-600'} hover:underline"
                >
                    ${u.banned ? 'Débannir' : 'Bannir'}
                </button>
            `;
        }
        return `
            <tr class="border-b">
                <td class="p-3 whitespace-nowrap">#${u.user_id}</td>
                <td class="p-3 whitespace-nowrap">${u.name}</td>
                <td class="p-3 whitespace-nowrap">${u.email}</td>
                <td class="p-3 whitespace-nowrap">${(u.roles || []).join(', ')}</td>
                <td class="p-3 whitespace-nowrap font-semibold ${statutClass}">
                    ${u.banned ? 'Banni' : 'Actif'}
                </td>
                <td class="p-3 text-right">
                    ${actionButton}
                </td>
            </tr>
        `;
    }).join('');
}
async function toggleBan(id) {
    try {
        const res = await fetch(`${BASE_API}/admin/users/${id}/ban`, {
            method: 'PUT',
            credentials: 'include'
        });
        if (res.status === 403) {
            const err = await res.json();
            alert(err.message || 'Action interdite');
            return;
        }
        if (!res.ok) throw new Error('Erreur API');
        await chargerUtilisateurs();
    } catch (e) {
        console.error(e);
        alert('Erreur lors de l\'opération.');
    }
}
document.getElementById('filtre').addEventListener('input', afficherUtilisateurs);
chargerUtilisateurs();
</script> <?php
require_once('includes/admin_footer.php');
?>