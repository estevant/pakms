<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');

if (!isset($_GET['id'])) {
  echo '<p class="text-red-600 text-center mt-10">ID d’annonce manquant.</p>';
  include_once('../includes/footer.php');
  exit;
}
$id = intval($_GET['id']);
?> <div id="unauthorized" class="text-red-600 text-center mt-12 hidden"> Accès refusé. Réservé aux livreurs. </div>
<div id="page-content" class="hidden max-w-4xl mx-auto py-10 px-6">
    <h2 class="text-2xl font-bold mb-6">📍 Suivi annonce #<?= $id ?></h2>
    <div id="adresses" class="space-y-1 mb-8 text-sm text-gray-700"></div>
    <h3 class="text-lg font-semibold mb-3">Historique</h3>
    <div id="timeline" class="space-y-3 border-l-2 pl-4 mb-10"></div>
    <h3 class="text-lg font-semibold mb-2">➕ Nouvelle étape</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <select id="status" class="input-field">
            <option>Pris en charge</option>
            <option>En cours</option>
            <option>Déposé en box</option>
            <option>Retiré du box</option>
            <option>Livré</option>
        </select>
        <input id="city" class="input-field" placeholder="Ville (facultatif)">
        <input id="code" class="input-field" placeholder="Code postal">
        <input id="desc" class="input-field md:col-span-2" placeholder="Description facultative">
    </div>
    <button id="btn-add" class="bg-[#1976D2] hover:bg-blue-700 text-white px-6 py-2 rounded"> 💾 Ajouter l’étape
    </button>
    <div id="msg" class="mt-6 text-sm font-medium"></div>
</div>
<style>
.input-field {
    @apply border border-gray-300 rounded px-3 py-2 w-full;
}

.step-bullet {
    @apply inline-block w-2 h-2 rounded-full mr-2 bg-[#1976D2];
}
</style>
<script>
const API_BASE = "<?= BASE_API ?>";
const annonceId = <?= $id ?>;
async function verifierAcces() {
    const res = await fetch(`${API_BASE}/get_session`, {
        credentials: 'include'
    });
    const json = await res.json();
    if (!json.user || !json.user.roles.includes('Deliverer')) {
        document.getElementById('unauthorized').classList.remove('hidden');
    } else {
        document.getElementById('page-content').classList.remove('hidden');
        loadTimeline();
    }
}
async function loadTimeline() {
    const res = await fetch(`${API_BASE}/suivi?id_annonce=${annonceId}`, {
        credentials: 'include'
    });
    const data = await res.json();
    const tl = document.getElementById('timeline');
    const addr = document.getElementById('adresses');
    if (!data.success) {
        tl.innerHTML = "<p class='text-red-600'>Erreur de chargement.</p>";
        return;
    }
    const departHtml = data.depart_address ? `${data.depart_address}, ${data.depart_code} ${data.depart_city}` :
        `${data.depart_city} (${data.depart_code})`;
    const arriveeHtml = data.arrivee_address ?
        `${data.arrivee_address}, ${data.arrivee_code} ${data.arrivee_city}` :
        `${data.arrivee_city} (${data.arrivee_code})`;
    addr.innerHTML = `
        <p><strong>Départ :</strong> ${departHtml}</p>
        <p><strong>Arrivée :</strong> ${arriveeHtml}</p>
    `;
    if (!data.events.length) {
        tl.innerHTML = "<p class='text-gray-500'>Aucune étape pour l’instant.</p>";
        return;
    }
    tl.innerHTML = data.events.map(e => `
        <div>
            <span class="step-bullet"></span>
            <span class="font-semibold">${e.status}</span>
            <span class="text-xs text-gray-500">(${e.created_at})</span><br>
            ${e.location_city ? `${e.location_city} (${e.location_code})<br>` : ''}
            ${e.description || ''}
        </div>
    `).join('');
}
async function addEvt() {
    const body = {
        id_annonce: annonceId,
        status: document.getElementById('status').value,
        description: document.getElementById('desc').value,
        location_city: document.getElementById('city').value,
        location_code: document.getElementById('code').value
    };
    const msg = document.getElementById('msg');
    try {
        const res = await fetch(`${API_BASE}/suivi`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        });
        const r = await res.json();
        msg.textContent = r.message || (r.success ? 'Étape ajoutée.' : 'Erreur');
        msg.className = r.success ? 'text-green-600' : 'text-red-600';
        if (r.success) {
            document.getElementById('desc').value = '';
            document.getElementById('city').value = '';
            document.getElementById('code').value = '';
            loadTimeline();
        }
    } catch {
        msg.textContent = 'Erreur réseau';
        msg.className = 'text-red-600';
    }
}
document.getElementById('btn-add').addEventListener('click', addEvt);
document.addEventListener('DOMContentLoaded', verifierAcces);
</script> <?php include_once('../includes/footer.php'); ?>