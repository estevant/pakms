<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?> <div id="unauthorized" class="text-red-600 text-center mt-12 hidden"> Accès refusé. Réservé aux livreurs. </div>
<div id="page-content" class="hidden max-w-5xl mx-auto py-10 px-6">
    <h2 class="text-2xl font-bold mb-6">📦 Annonces disponibles à livrer</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <input type="text" id="filter_depart" placeholder="Code postal de départ" class="input-field">
        <input type="text" id="filter_arrivee" placeholder="Code postal d’arrivée" class="input-field">
        <button onclick="chargerAnnonces()" class="bg-[#1976D2] text-white px-4 py-2 rounded col-span-1 md:col-span-2">
            🔍 Filtrer </button>
    </div>
    <div id="liste-annonces" class="space-y-6"></div>
</div>
<script>
const API_BASE = "<?= BASE_API ?>";
let user = null;
async function verifierAcces() {
    const res = await fetch(`${API_BASE}/get_session`, {
        credentials: 'include'
    });
    const data = await res.json();
    if (!data.user || !data.user.roles.includes('Deliverer')) {
        document.getElementById('unauthorized').classList.remove('hidden');
    } else {
        user = data.user;
        document.getElementById('page-content').classList.remove('hidden');
        chargerAnnonces();
    }
}
async function chargerAnnonces() {
    const depart = document.getElementById('filter_depart').value.trim();
    const arrivee = document.getElementById('filter_arrivee').value.trim();
    const params = new URLSearchParams();
    if (depart) params.append('depart_code', depart);
    if (arrivee) params.append('arrivee_code', arrivee);
    const res = await fetch(`${API_BASE}/livreur/disponibles?${params.toString()}`, {
        credentials: 'include'
    });
    const data = await res.json();
    const container = document.getElementById('liste-annonces');
    container.innerHTML = '';
    if (data.success && data.annonces.length) {
        const autresAnnonces = data.annonces.filter(a => a.user_id !== user.user_id);
        
        if (autresAnnonces.length === 0) {
            container.innerHTML = "<p class='text-gray-500'>Aucune annonce d'autres utilisateurs trouvée pour ce trajet.</p>";
            return;
        }
        
        autresAnnonces.forEach(a => {
            const departAdr = a.depart_address ? `${a.depart_address}, ${a.depart_code} ${a.depart}` :
                `${a.depart} (${a.depart_code})`;
            const arriveeAdr = a.arrivee_address ? `${a.arrivee_address}, ${a.arrivee_code} ${a.arrivee}` :
                `${a.arrivee} (${a.arrivee_code})`;
            const card = document.createElement('div');
            card.className = "border border-gray-300 bg-white rounded p-4 shadow";
            card.innerHTML = `
                <p><strong>Départ :</strong>  ${departAdr}</p>
                <p><strong>Arrivée :</strong> ${arriveeAdr}</p>
                ${a.box_label ? `
                    <p class="text-orange-600"><strong>Box :</strong>
                       ${a.box_label} (${a.box_city})</p>` : ''}
                <button class="mt-3 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    ✅ S’attribuer cette livraison
                </button>
            `;
            card.querySelector('button').addEventListener('click', () => attribuerAnnonce(a.id_annonce));
            container.appendChild(card);
        });
    } else {
        container.innerHTML = "<p class='text-gray-500'>Aucune annonce trouvée pour ce trajet.</p>";
    }
}
async function attribuerAnnonce(id) {
    if (!confirm("S’attribuer cette annonce ?")) return;
    try {
        const res = await fetch(`${API_BASE}/livreur/attribuer`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id_annonce: id
            })
        });
        const raw = await res.text();
        let r;
        try {
            r = JSON.parse(raw);
        } catch {
            alert('Réponse non JSON');
            return;
        }
        alert(r.message ?? 'Réponse sans message');
        if (r.success) chargerAnnonces();
    } catch (e) {
        alert('Erreur réseau : ' + e.message);
    }
}
document.addEventListener('DOMContentLoaded', verifierAcces);
</script>
<style>
.input-field {
    @apply border border-gray-300 rounded px-3 py-2 w-full;
}
</style> <?php include_once('../includes/footer.php'); ?>