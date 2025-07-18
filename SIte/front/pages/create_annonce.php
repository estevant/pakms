<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?> <div class="max-w-4xl mx-auto py-10 px-4">
    <h1 class="text-3xl font-bold text-[#212121] mb-6 text-center"> Création d'une annonce </h1>
    <div class="flex justify-center mb-10 space-x-4 text-sm font-medium">
        <div class="step-indicator active" id="ind-step-1">1. Objets</div>
        <div class="step-indicator" id="ind-step-2">2. Départ</div>
        <div class="step-indicator" id="ind-step-3">3. Arrivée &amp; Box</div>
    </div>
    <form id="annonce-form" class="space-y-10" enctype="multipart/form-data">
        <!-- Étape 1 -->
        <div id="step-1" class="step space-y-4">
            <h2 class="text-xl font-semibold text-[#212121]">Étape 1 : décrivez vos objets</h2>
            <div id="objets-container" class="space-y-4"></div>
            <button type="button" onclick="ajouterObjet()" class="text-[#1976D2] hover:underline"> Ajouter un objet
            </button>
            <div class="mt-4">
                <button type="button" onclick="goToStep(2)"
                    class="bg-[#1976D2] text-white px-6 py-2 rounded hover:bg-blue-700"> Suivant </button>
            </div>
        </div>
        <!-- Étape 2 -->
        <div id="step-2" class="step hidden space-y-4">
            <h2 class="text-xl font-semibold text-[#212121]">Étape 2 : lieu de départ</h2>
            <label class="block">Adresse complète : <input type="text" id="depart_adresse" list="depart_list" required
                    autocomplete="off" oninput="autoCompleteAdresse('depart')" onchange="onAdressePick('depart')"
                    class="input-field">
                <datalist id="depart_list"></datalist>
            </label>
            <label class="block">Code postal : <input type="text" id="depart_code" required
                    oninput="autoCompleteVille('depart')" class="input-field">
            </label>
            <label class="block">Ville : <input type="text" id="ville_depart" readonly
                    class="input-field bg-gray-100 cursor-not-allowed">
            </label>
            <!-- Coordonnées cachées -->
            <input type="hidden" id="depart_lat">
            <input type="hidden" id="depart_lon">
            <div class="mt-4 space-x-2">
                <button type="button" onclick="goToStep(1)" class="text-gray-500 hover:underline">Précédent</button>
                <button type="button" onclick="goToStep(3)"
                    class="bg-[#1976D2] text-white px-6 py-2 rounded hover:bg-blue-700">Suivant</button>
            </div>
        </div>
        <!-- Étape 3 -->
        <div id="step-3" class="step hidden space-y-4">
            <h2 class="text-xl font-semibold text-[#212121]">Étape 3 : lieu d'arrivée &amp; option box</h2>
            <label class="block">Adresse complète : <input type="text" id="arrivee_adresse" list="arrivee_list" required
                    autocomplete="off" oninput="autoCompleteAdresse('arrivee')" onchange="onAdressePick('arrivee')"
                    class="input-field">
                <datalist id="arrivee_list"></datalist>
            </label>
            <label class="block">Code postal : <input type="text" id="arrivee_code" required
                    oninput="autoCompleteVille('arrivee')" class="input-field">
            </label>
            <label class="block">Ville : <input type="text" id="ville_arrivee" readonly
                    class="input-field bg-gray-100 cursor-not-allowed">
            </label>
            <!-- Coordonnées cachées -->
            <input type="hidden" id="arrivee_lat">
            <input type="hidden" id="arrivee_lon">
            <div id="box_section" class="hidden space-y-2">
                <label class="block font-semibold mt-6">Souhaitez-vous un dépôt en box ?</label>
                <select id="box_yesno" class="input-field" onchange="toggleBoxSelect()">
                    <option value="no" selected>Non</option>
                    <option value="yes">Oui</option>
                </select>
                <div id="box_choice" class="hidden">
                    <label class="block">Choisissez la box :</label>
                    <select id="box_id" class="input-field mt-1"></select>
                </div>
            </div>
            <div class="mt-4 space-x-2">
                <button type="button" onclick="goToStep(2)" class="text-gray-500 hover:underline">Précédent</button>
                <button type="button" onclick="soumettreAnnonce()"
                    class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Valider</button>
            </div>
            <div id="estimate-container" class="mt-4 text-lg font-semibold text-center <?= $isSeller ? '' : 'hidden' ?>"
                style="display:none;"> Prix estimé : <span id="estimate">…</span> € </div>
        </div>
        <div id="message" class="mt-6 text-sm font-medium text-center"></div>
    </form>
</div>
<style>
.input-field {
    @apply border border-gray-300 rounded px-3 py-2 w-full focus: outline-none focus:ring-2 focus:ring-[#1976D2];
}

.step-indicator {
    @apply px-4 py-2 rounded-full bg-gray-200 text-gray-700;
}

.step-indicator.active {
    @apply bg-[#1976D2] text-white;
}
</style>
<script defer>
const API_BASE = "<?= BASE_API ?>";
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content') || '';
let step = 1;
let objets = [];
let isSeller = false;
fetch(`${API_BASE}/get_session`, {
    credentials: 'include',
    headers: {
        'Accept': 'application/json'
    }
}).then(r => r.ok ? r.json() : Promise.reject()).then(json => {
    if (json.user && Array.isArray(json.user.roles)) {
        isSeller = json.user.roles.includes('Seller');
    }
}).catch(() => {}).finally(initWizard);
/*  ----------  WIZARD ---------- */
function initWizard() {
    ajouterObjet();
    goToStep(1);
}

function goToStep(s) {
    if (s > step && !validerEtape(step)) return;
    document.querySelectorAll('.step').forEach(e => e.classList.add('hidden'));
    document.querySelectorAll('.step-indicator').forEach(e => e.classList.remove('active'));
    document.getElementById(`step-${s}`).classList.remove('hidden');
    document.getElementById(`ind-step-${s}`).classList.add('active');
    step = s;
    const div = document.getElementById('estimate-container');
    div.style.display = (s === 3 && isSeller) ? 'block' : 'none';
}
/*  ----------  OBJETS ---------- */
function ajouterObjet() {
    const i = objets.length;
    objets.push({});
    const div = document.createElement('div');
    div.className = 'border border-gray-300 p-4 rounded bg-white shadow relative';
    div.setAttribute('data-index', i);
    div.innerHTML = `
    <button type="button" onclick="supprimerObjet(${i})" class="absolute top-2 right-2 text-sm bg-red-600 text-white px-2 py-1 rounded">Supprimer</button>
    <h3 class="font-semibold mb-2 text-[#212121]">Objet ${i + 1}</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <label>Nom* : <input type="text" name="objets[${i}][nom]" required class="input-field"></label>
      <label>Quantité* : <input type="number" name="objets[${i}][quantite]" value="1" required class="input-field"></label>
      <label>Longueur (m)* : <input type="number" step="0.01" name="objets[${i}][length]" required class="input-field"></label>
      <label>Largeur (m)* : <input type="number" step="0.01" name="objets[${i}][width]" required class="input-field"></label>
      <label>Hauteur (m)* : <input type="number" step="0.01" name="objets[${i}][height]" required class="input-field"></label>
      <label>Poids (kg) : <input type="number" step="0.1" name="objets[${i}][poids]" class="input-field"></label>
    </div>
    <label class="block mt-3">Description : <textarea name="objets[${i}][description]" class="input-field"></textarea></label>
    <label class="block mt-3">Photos : <input type="file" name="photos_${i}[]" multiple accept="image/*" class="mt-1"></label>
  `;
    document.getElementById('objets-container').appendChild(div);
}

function supprimerObjet(i) {
    document.querySelector(`#objets-container [data-index="${i}"]`).remove();
    objets.splice(i, 1);
}
/*  ----------  VALIDATION ---------- */
function validerEtape(s) {
    if (s === 1) {
        const nodes = document.querySelectorAll('#objets-container > div');
        if (!nodes.length) {
            alert('Veuillez ajouter au moins un objet.');
            return false;
        }
        let ok = true;
        nodes.forEach((el, i) => {
            ['nom', 'quantite', 'length', 'width', 'height'].forEach(field => {
                const inp = el.querySelector(`[name="objets[${i}][${field}]"]`);
                if (!inp.value.trim()) {
                    inp.classList.add('border-red-500');
                    ok = false;
                } else inp.classList.remove('border-red-500');
            });
        });
        return ok;
    }
    if (s === 2) {
    if (!document.getElementById('depart_adresse').value.trim()) {
        alert('Veuillez choisir une adresse de départ valide.');
        return false;
    }
    if (!document.getElementById('depart_lat').value || !document.getElementById('depart_lon').value) {
        alert('Veuillez sélectionner une adresse dans la liste proposée.');
        return false;
    }
}
    if (s === 3 && !document.getElementById('arrivee_adresse').value.trim()) {
        alert('Veuillez choisir une adresse d\'arrivée valide.');
        return false;
    }
    return true;
}
/*  ----------  BOX ---------- */
function toggleBoxSelect() {
    const want = document.getElementById('box_yesno').value === 'yes';
    document.getElementById('box_choice').classList.toggle('hidden', !want);
}
/*  ----------  AUTOCOMPLÉTION D’ADRESSE ---------- */
async function autoCompleteAdresse(type) {
    const q = document.getElementById(`${type}_adresse`).value.trim();
    if (q.length < 3) return;
    const res = await fetch(`${API_BASE}/adresses?q=${encodeURIComponent(q)}`, {
        credentials: 'include',
        headers: {
            'Accept': 'application/json'
        }
    });
    const json = await res.json();
    const dl = document.getElementById(`${type}_list`);
    dl.innerHTML = '';
    json.forEach(a => {
        dl.insertAdjacentHTML('beforeend', `<option value="${a.label}"
               data-postcode="${a.postcode}"
               data-city="${a.city}"
               data-lat="${a.lat}"
               data-lon="${a.lon}"></option>`);
    });
}

function onAdressePick(type) {
    const input = document.getElementById(`${type}_adresse`);
    const option = [...document.getElementById(`${type}_list`).options].find(o => o.value === input.value);
    if (!option) return;
    document.getElementById(`${type}_code`).value = option.dataset.postcode;
    document.getElementById(`ville_${type}`).value = option.dataset.city;
    document.getElementById(`${type}_lat`).value = option.dataset.lat;
    document.getElementById(`${type}_lon`).value = option.dataset.lon;
    if (type === 'arrivee') {
        autoCompleteVille('arrivee'); // pour la section box
        if (isSeller) estimerAnnonce();
    }
}
/*  ----------  AUTOCOMPLÉTION VILLE */
async function autoCompleteVille(type) {
    const code = document.getElementById(`${type}_code`).value.trim();
    const inputVille = document.getElementById(`ville_${type}`);
    if (!/^[0-9]{5}$/.test(code)) {
        inputVille.value = '';
        return;
    }
    const [villeRes, boxRes] = await Promise.all([
        fetch(`${API_BASE}/annonce/villes?q=${code}`, {
            credentials: 'include',
            headers: {
                'Accept': 'application/json'
            }
        }),
        type === 'arrivee' ? fetch(`${API_BASE}/boxes/near?code=${code}`) : Promise.resolve({
            json: async () => ({
                success: false
            })
        })
    ]);
    const villes = await villeRes.json();
    inputVille.value = villes.length ? villes[0].ville : '';
    if (type !== 'arrivee') return;
    /*  --------  BOX LISTE  -------- */
    const near = await boxRes.json();
    const sel = document.getElementById('box_id');
    sel.innerHTML = '';
    document.getElementById('box_section').classList.remove('hidden');
    if (near.success && near.boxes.length) {
        near.boxes.forEach(b => {
            const adresse = b.full_address || `${b.location_city} (${b.location_code})`;
            sel.insertAdjacentHTML('beforeend',
                `<option value="${b.box_id}">${b.label} - ${adresse}</option>`);
        });
        document.querySelector('#box_yesno option[value="yes"]').disabled = false;
    } else {
        document.querySelector('#box_yesno').value = 'no';
        toggleBoxSelect();
        document.querySelector('#box_yesno option[value="yes"]').disabled = true;
    }
    if (type === 'arrivee' && inputVille.value && isSeller) {
        estimerAnnonce();
    }
}
/*  ----------  SUBMIT ---------- */
async function soumettreAnnonce() {
    if (!validerEtape(3)) return;
    const formData = new FormData();
    /* Objets */
    const nodes = document.querySelectorAll('#objets-container > div');
    const listeObj = [];
    nodes.forEach((el, i) => {
        listeObj.push({
            nom: el.querySelector(`[name="objets[${i}][nom]"]`).value,
            quantite: el.querySelector(`[name="objets[${i}][quantite]"]`).value,
            length: el.querySelector(`[name="objets[${i}][length]"]`).value,
            width: el.querySelector(`[name="objets[${i}][width]"]`).value,
            height: el.querySelector(`[name="objets[${i}][height]"]`).value,
            poids: el.querySelector(`[name="objets[${i}][poids]"]`).value,
            description: el.querySelector(`[name="objets[${i}][description]"]`).value
        });
        const files = el.querySelector(`[name="photos_${i}[]"]`).files;
        for (let f = 0; f < files.length; f++) formData.append(`photos_${i}[]`, files[f]);
    });
    formData.append('objets', JSON.stringify(listeObj));
    /* Départ */
    formData.append('adresse_depart', document.getElementById('depart_adresse').value);
    formData.append('code_postal', document.getElementById('depart_code').value);
    formData.append('ville', document.getElementById('ville_depart').value);
    formData.append('lat_depart', document.getElementById('depart_lat').value);
    formData.append('lon_depart', document.getElementById('depart_lon').value);
    /* Arrivée */
    formData.append('adresse_arrivee', document.getElementById('arrivee_adresse').value);
    formData.append('code_postal_arrivee', document.getElementById('arrivee_code').value);
    formData.append('ville_arrivee', document.getElementById('ville_arrivee').value);
    formData.append('lat_arrivee', document.getElementById('arrivee_lat').value);
    formData.append('lon_arrivee', document.getElementById('arrivee_lon').value);
    /* Box */
    const wantBox = document.getElementById('box_yesno').value === 'yes';
    formData.append('box_option', wantBox ? 'yes' : 'no');
    if (wantBox) formData.append('box_id', document.getElementById('box_id').value);
    /* Envoi */
    const res = await fetch(`${API_BASE}/annonce/submit`, {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: formData
    });
    const result = await res.json();
    const msg = document.getElementById('message');
    
    if (result.success) {
        msg.textContent = result.message || 'Annonce créée !';
        msg.className = 'text-green-600';
        
        if (result.photos_provided && result.photos_saved === false) {
            msg.innerHTML += '<br><span class="text-orange-600 text-sm">⚠️ Attention: Les photos n\'ont pas pu être sauvegardées (table manquante)</span>';
        }
        
        /*  Example redirection Stripe (conservé) */
        fetch(`${API_BASE}/stripe/create-session`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                request_id: result.request_id
            })
        }).then(r => r.json()).then(data => {
            if (data.url) window.location.href = data.url;  
        }).catch(err => console.error('Erreur requête Stripe :', err));
    } else {
        let errorMessage = result.message || 'Erreur lors de la création.';
        
        if (result.errors && Array.isArray(result.errors)) {
            errorMessage += '<br><br><strong>Détails des erreurs :</strong><br>';
            result.errors.forEach(error => {
                errorMessage += `• ${error}<br>`;
            });
        }
        
        if (result.suggestion) {
            errorMessage += `<br><strong>Solution :</strong><br>${result.suggestion}`;
        }
        
        msg.innerHTML = errorMessage;
        msg.className = 'text-red-600';
    }
}
/*  ----------  ESTIMATION ---------- */
async function estimerAnnonce() {
    const nodes = document.querySelectorAll('#objets-container > div');
    const listeObj = Array.from(nodes).map((el, i) => ({
        nom: el.querySelector(`[name="objets[${i}][nom]"]`).value,
        quantite: el.querySelector(`[name="objets[${i}][quantite]"]`).value,
        length: el.querySelector(`[name="objets[${i}][length]"]`).value,
        width: el.querySelector(`[name="objets[${i}][width]"]`).value,
        height: el.querySelector(`[name="objets[${i}][height]"]`).value,
        poids: el.querySelector(`[name="objets[${i}][poids]"]`).value,
    }));
    const formData = new FormData();
    formData.append('objets', JSON.stringify(listeObj));
    formData.append('code_postal', document.getElementById('depart_code').value);
    formData.append('code_postal_arrivee', document.getElementById('arrivee_code').value);
    const span = document.getElementById('estimate');
    span.textContent = 'Calcul…';
    try {
        const res = await fetch(`${API_BASE}/annonce/estimate`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: formData
        });
        const json = await res.json();
        span.textContent = json.success ? (json.prix_cents / 100).toFixed(2) : 'Erreur';
    } catch {
        span.textContent = 'Erreur';
    }
}
</script>