<?php include_once('../includes/header.php'); ?>
<?php include_once('../includes/api_path.php'); ?>

<div id="unauthorized" class="text-red-600 text-center mt-12 hidden">Accès refusé. Connectez-vous.</div>

<div id="page-content" class="hidden max-w-5xl mx-auto py-10 px-6">
    <h2 class="text-2xl font-bold mb-6">🔔 Mes notifications</h2>

    <div id="liste-notifications" class="space-y-6"></div>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";

async function verifierAcces() {
    const res = await fetch(`${API_BASE}/get_session`, { credentials: 'include' });
    const data = await res.json();

    if (!data.user) {
        document.getElementById('unauthorized').classList.remove('hidden');
    } else {
        document.getElementById('page-content').classList.remove('hidden');
        chargerNotifications();
    }
}

async function chargerNotifications() {
    const res = await fetch(`${API_BASE}/notifications`, { credentials: 'include' });
    const data = await res.json();
    const container = document.getElementById('liste-notifications');
    container.innerHTML = '';

    if (data.success && data.notifications.length) {
        data.notifications.forEach(n => {
            container.innerHTML += `
                <div class="border ${n.is_read ? 'border-gray-300' : 'border-blue-500'} 
                            bg-white rounded p-4 shadow relative transition 
                            ${!n.is_read ? 'hover:bg-blue-50' : ''}">
                    <p class="font-bold text-[#212121]">${n.titre}</p>
                    <p class="text-gray-700 mt-1">${n.contenu}</p>
                    <p class="text-gray-400 text-xs mt-2">${n.date}</p>

                    ${!n.is_read ? `
                        <button onclick="marquerLue(${n.id})" 
                                class="mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            ✅ Marquer comme lue
                        </button>
                    ` : ''}
                </div>
            `;
        });
    } else {
        container.innerHTML = "<p class='text-gray-500'>Aucune notification pour l'instant.</p>";
    }
}

async function marquerLue(id) {
    try {
        await fetch(`${API_BASE}/notifications/${id}/read`, {
            method: 'PATCH',
            headers: { 'Accept': 'application/json' },
            credentials: 'include',
        });
        chargerNotifications();
    } catch (e) {
        console.error(e);
        alert("Erreur réseau lors de la mise à jour.");
    }
}

verifierAcces();
</script>

<?php include_once('../includes/footer.php'); ?>
