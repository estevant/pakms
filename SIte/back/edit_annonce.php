<?php
include_once 'includes/admin_header.php';
include_once 'includes/api_path.php';

if (!isset($_GET['id'])) {
    echo "<p class='text-red-600 font-semibold'>ID d'annonce manquant</p>";
    include_once 'includes/admin_footer.php';
    exit;
}
$id = intval($_GET['id']);
?> <div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-2xl font-bold text-[#212121] mb-6"> Modifier l’annonce #<?= $id ?> </h2>
    <form id="form-edit" class="space-y-6 max-w-3xl bg-white p-6 rounded shadow border border-gray-200">
        <input type="hidden" name="id_annonce" value="<?= $id ?>">
        <!-- 1. Adresses complète + coordonnées -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Départ -->
            <label class="block font-medium">Adresse complète départ : <input type="text" id="depart_adresse"
                    list="depart_list" name="adresse_depart" required autocomplete="off" class="input-field"
                    oninput="autoCompleteAdresse('depart')" onchange="onAdressePick('depart')">
                <datalist id="depart_list"></datalist>
            </label>
            <label class="block font-medium">Code postal départ : <input type="text" id="depart_code" name="code_postal"
                    required class="input-field bg-gray-100 cursor-not-allowed" readonly>
            </label>
            <label class="block font-medium">Ville de départ : <input type="text" id="ville_depart" name="ville"
                    required class="input-field bg-gray-100" readonly>
            </label>
            <!-- Arrivée -->
            <label class="block font-medium">Adresse complète arrivée : <input type="text" id="arrivee_adresse"
                    list="arrivee_list" name="adresse_arrivee" required autocomplete="off" class="input-field"
                    oninput="autoCompleteAdresse('arrivee')" onchange="onAdressePick('arrivee')">
                <datalist id="arrivee_list"></datalist>
            </label>
            <label class="block font-medium">Code postal arrivée : <input type="text" id="arrivee_code"
                    name="code_postal_arrivee" required class="input-field bg-gray-100 cursor-not-allowed" readonly>
            </label>
            <label class="block font-medium">Ville d’arrivée : <input type="text" id="ville_arrivee"
                    name="ville_arrivee" required class="input-field bg-gray-100" readonly>
            </label>
        </div>
        <!-- coordonnées cachées -->
        <input type="hidden" id="depart_lat" name="lat_depart">
        <input type="hidden" id="depart_lon" name="lon_depart">
        <input type="hidden" id="arrivee_lat" name="lat_arrivee">
        <input type="hidden" id="arrivee_lon" name="lon_arrivee">
        <!-- 2. Statut & livreur -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium mb-1">Statut annonce :</label>
                <select name="statut" required class="input-field">
                    <option value="en_attente">En attente</option>
                    <option value="validee">Validée</option>
                    <option value="livree">Livrée</option>
                    <option value="annulee">Annulée</option>
                </select>
            </div>
            <div>
                <label class="block font-medium mb-1">Livreur affecté :</label>
                <input type="text" name="livreur_nom" readonly class="input-field bg-gray-100 text-gray-700">
            </div>
            <div class="md:col-span-2">
                <label class="block font-medium mb-1">Statut livraison :</label>
                <div id="statut-livraison" class="inline-block px-3 py-1 text-white rounded text-sm bg-gray-400">
                    Chargement… </div>
            </div>
        </div>
        <!-- 3. Objets -->
        <div>
            <h3 class="text-lg font-semibold mb-2">📦 Objets</h3>
            <div id="objets-container" class="space-y-4"></div>
            <button type="button" onclick="ajouterObjet()"
                class="mt-3 bg-gray-200 text-sm px-3 py-1 rounded hover:bg-gray-300"> ➕ Ajouter un objet </button>
        </div>
        <!-- 4. Enregistrer -->
        <button type="submit" class="bg-[#1976D2] hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded"> 💾
            Enregistrer </button>
        <div id="message" class="mt-4 text-sm font-medium"></div>
    </form>
</div>
<style>
.input-field {
    @apply border border-gray-300 rounded px-3 py-2 w-full;
}
</style>
<script>
const API_BASE = "<?= BASE_API ?>";
const id = <?= $id ?>;
let objetsCount = 0;
/* ---------- autocomplétion adresse ---------- */
async function autoCompleteAdresse(type) {
    const q = document.getElementById(`${type}_adresse`).value.trim();
    if (q.length < 3) return;
    const res = await fetch(`${API_BASE}/adresses?q=${encodeURIComponent(q)}`, {
        credentials: 'include',
        headers: {
            'Accept': 'application/json'
        }
    });
    const list = await res.json();
    const dl = document.getElementById(`${type}_list`);
    dl.innerHTML = '';
    list.forEach(a => {
        dl.insertAdjacentHTML('beforeend', `<option value="${a.label}"
            data-postcode="${a.postcode}" data-city="${a.city}"
            data-lat="${a.lat}" data-lon="${a.lon}"></option>`);
    });
}

function onAdressePick(type) {
    const inp = document.getElementById(`${type}_adresse`);
    const opt = [...document.getElementById(`${type}_list`).options].find(o => o.value === inp.value);
    if (!opt) return;
    document.getElementById(`${type}_code`).value = opt.dataset.postcode;
    document.getElementById(`ville_${type}`).value = opt.dataset.city;
    document.getElementById(`${type}_lat`).value = opt.dataset.lat;
    document.getElementById(`${type}_lon`).value = opt.dataset.lon;
}
/* ---------- objets ---------- */
function ajouterObjet(obj = {}) {
    const i = objetsCount++;
    const div = document.createElement('div');
    div.className = 'border p-4 rounded bg-gray-50 relative';
    div.dataset.index = i;
    div.innerHTML = `
        <input type="hidden" name="objets[${i}][id_objet]" value="${obj.id || ''}">
        <input type="hidden" name="objets[${i}][delete]"   value="0" class="delete-flag">

        <button type="button" onclick="marquerASupprimer(this)"
                class="absolute top-2 right-2 bg-red-600 text-white text-xs px-2 py-1 rounded">
            🗑 Supprimer
        </button>

        <h4 class="font-semibold mb-2">Objet ${i+1}</h4>

        <label class="block">Nom :
            <input type="text" name="objets[${i}][nom]" value="${obj.nom ?? ''}" required class="input-field">
        </label>

        <label class="block">Quantité :
            <input type="number" name="objets[${i}][quantite]" value="${obj.quantite ?? 1}" required class="input-field">
        </label>

        <label class="block">Dimensions :
            <input type="text" name="objets[${i}][dimensions]" value="${obj.dimensions ?? ''}" class="input-field">
        </label>

        <label class="block">Poids :
            <input type="number" step="0.1" name="objets[${i}][poids]" value="${obj.poids ?? ''}" class="input-field">
        </label>

        <label class="block">Description :
            <textarea name="objets[${i}][description]" class="input-field">${obj.description ?? ''}</textarea>
        </label>
    `;
    document.getElementById('objets-container').appendChild(div);
}

function marquerASupprimer(btn) {
    const box = btn.closest('[data-index]');
    box.querySelector('.delete-flag').value = '1';
    box.classList.add('opacity-50', 'pointer-events-none');
    box.insertAdjacentHTML('beforeend', '<div class="text-red-600 text-sm mt-2">Cet objet sera supprimé</div>');
}
/* ---------- chargement annonce ---------- */
async function chargerAnnonce() {
    const res = await fetch(`${API_BASE}/admin/annonces/${id}`, {
        credentials: 'include',
        headers: {
            'Accept': 'application/json'
        }
    });
    const data = await res.json();
    if (!data.success) return;
    const a = data.annonce;
    /* adresse départ */
    document.getElementById('depart_adresse').value = a.depart_address || '';
    document.getElementById('depart_code').value = a.depart_code;
    document.getElementById('ville_depart').value = a.depart;
    document.getElementById('depart_lat').value = a.depart_lat || '';
    document.getElementById('depart_lon').value = a.depart_lon || '';
    /* adresse arrivée */
    document.getElementById('arrivee_adresse').value = a.arrivee_address || '';
    document.getElementById('arrivee_code').value = a.arrivee_code;
    document.getElementById('ville_arrivee').value = a.arrivee;
    document.getElementById('arrivee_lat').value = a.arrivee_lat || '';
    document.getElementById('arrivee_lon').value = a.arrivee_lon || '';
    /* statut annonce */
    const selStatut = document.querySelector('[name=statut]');
    if (![...selStatut.options].some(o => o.value === a.statut)) {
        selStatut.insertAdjacentHTML('beforeend', `<option value="${a.statut}">${a.statut}</option>`);
    }
    selStatut.value = a.statut;
    /* livreur */
    document.querySelector('[name=livreur_nom]').value = a.livreur_prenom ? `${a.livreur_prenom} ${a.livreur_nom}` :
        '—';
    /* statut livraison avec couleur */
    const slMap = {
        'En attente': 'bg-yellow-500',
        'Acceptée': 'bg-blue-500',
        'En cours': 'bg-indigo-600',
        'Livrée': 'bg-green-600',
        'Annulée': 'bg-red-600'
    };
    const sl = a.statut_livraison ?? 'En attente';
    const badge = document.getElementById('statut-livraison');
    badge.textContent = sl;
    badge.className = 'inline-block px-3 py-1 text-white rounded text-sm ' + (slMap[sl] || 'bg-gray-400');
    /* objets */
    (a.objets || []).forEach(ajouterObjet);
}
/* ---------- soumission ---------- */
document.getElementById('form-edit').addEventListener('submit', async e => {
    e.preventDefault();
    const objets = [...document.querySelectorAll('[data-index]')];
    const actifs = objets.filter(o => o.querySelector('.delete-flag').value === '0');
    if (actifs.length === 0) {
        alert("L'annonce doit contenir au moins un objet.");
        return;
    }
    /* construction payload JSON */
    const form = new FormData(e.target);
    const payload = Object.fromEntries(form.entries());
    /* on ajoute objets */
    payload.objets = objets.map(box => {
        const inputs = box.querySelectorAll('input, textarea');
        const obj = {};
        inputs.forEach(inp => {
            const key = inp.name.match(/\[(.*?)\]$/)[1];
            obj[key] = inp.value;
        });
        return obj;
    });
    /* suppression éventuelle du livreur_nom (read-only) */
    delete payload.livreur_nom;
    /* appel API */
    const res = await fetch(`${API_BASE}/admin/annonces/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify(payload)
    });
    const out = await res.json();
    const msg = document.getElementById('message');
    msg.textContent = out.message;
    msg.className = out.success ? 'text-green-600' : 'text-red-600';
});
chargerAnnonce();
</script> <?php include_once 'includes/admin_footer.php'; ?>