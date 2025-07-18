<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?> <div id="unauthorized" class="text-red-600 text-center mt-12 hidden"> Accès refusé. Réservé aux livreurs. </div>
<div id="page-content" class="hidden max-w-5xl mx-auto py-10 px-6">
    <h2 class="text-2xl font-bold mb-6">🚚 Mes livraisons</h2>
    <div id="liste-livraisons" class="space-y-6"></div>
    <div class="mt-8">
        <a href="livraisons_terminees.php"
            class="inline-block bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800"> 📁 Voir les livraisons
            terminées </a>
    </div>
</div>
<script>
const API_BASE = "<?= BASE_API ?>";
async function verifierAcces() {
    const res = await fetch(`${API_BASE}/get_session`, {
        credentials: 'include'
    });
    const data = await res.json();
    if (!data.user || !data.user.roles.includes('Deliverer')) {
        document.getElementById('unauthorized').classList.remove('hidden');
    } else {
        document.getElementById('page-content').classList.remove('hidden');
        chargerLivraisons();
    }
}
async function chargerLivraisons() {
    const res = await fetch(`${API_BASE}/livreur/mes`, {
        credentials: 'include'
    });
    const data = await res.json();
    const container = document.getElementById('liste-livraisons');
    container.innerHTML = '';
    if (data.success && data.livraisons.length) {
        data.livraisons.forEach(a => {
            const departAdr = a.depart_address ? `${a.depart_address}, ${a.depart_code} ${a.depart}` :
                `${a.depart} (${a.depart_code})`;
            const arriveeAdr = a.arrivee_address ? `${a.arrivee_address}, ${a.arrivee_code} ${a.arrivee}` :
                `${a.arrivee} (${a.arrivee_code})`;
            const card = document.createElement('div');
            card.className = "border border-gray-300 bg-white rounded p-4 shadow";
            card.innerHTML = `
                <p><strong>Départ :</strong> ${departAdr}</p>
                <p><strong>Arrivée :</strong> ${arriveeAdr}</p>
                ${a.box_label ? `<p class="text-orange-600"><strong>📦 Box :</strong> ${a.box_label} (${a.box_city})</p>` : ''}
                <p><strong>Statut :</strong> ${a.statut}</p>
                ${a.partial ? `<p class="text-yellow-600 font-semibold">⚠️ Livraison partielle</p>` : ''}
                <div class="flex flex-wrap gap-2 mt-4">
                    <a href="suivi_livreur.php?id=${a.id_annonce}"
                       class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">📍 Suivi</a>
                    <button onclick="marquerLivre(${a.id_annonce})"
                            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">✅ Marquer comme livrée</button>
                    <a href="tchat.php?id=${a.id_annonce}"
                       class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">💬 Tchat</a>
                    <button onclick="desattribuer(${a.id_annonce})"
                            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">❌ Se désattribuer</button>
                </div>
            `;
            container.appendChild(card);
        });
    } else {
        container.innerHTML = "<p class='text-gray-500'>Aucune livraison en cours.</p>";
    }
}
async function marquerLivre(id) {
    try {
        const res = await fetch(`${API_BASE}/livreur/livree`, {
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
        const r = await res.json();
        alert(r.message || 'Statut mis à jour.');
        chargerLivraisons();
    } catch (e) {
        alert("Erreur réseau lors de la mise à jour.");
    }
}
async function desattribuer(id) {
    if (!confirm("Se désattribuer de cette livraison ?")) return;
    try {
        const res = await fetch(`${API_BASE}/livreur/desattribuer`, {
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
        const r = await res.json();
        alert(r.message || 'Désattribution effectuée.');
        chargerLivraisons();
    } catch (e) {
        alert("Erreur réseau lors de la désattribution.");
    }
}
document.addEventListener('DOMContentLoaded', verifierAcces);
</script>
<style>
.input-field {
    @apply border border-gray-300 rounded px-3 py-2 w-full;
}
</style> <?php include_once('../includes/footer.php'); ?>