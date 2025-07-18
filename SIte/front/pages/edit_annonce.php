<?php
include_once '../includes/header.php';
include_once '../includes/api_path.php';

if (!isset($_GET['id'])) {
    echo '<p class="text-red-600 text-center">ID manquant.</p>';
    include_once '../includes/footer.php';
    exit;
}
$id = intval($_GET['id']);
?> <div class="max-w-4xl mx-auto py-10 px-6">
    <h2 class="text-2xl font-bold mb-6">Modifier l'annonce #<?= $id ?></h2>
    <form id="form-edit" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="id_annonce" value="<?= $id ?>">
        <input type="hidden" name="box_option" id="box_option" value="no">
        <!-- adresses -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <label class="block">Adresse complète départ : <input type="text" id="depart_adresse" list="depart_list"
                    name="adresse_depart" required autocomplete="off" class="input-field"
                    oninput="autoCompleteAdresse('depart')" onchange="onAdressePick('depart')">
                <datalist id="depart_list"></datalist>
            </label>
            <label class="block">Code postal départ : <input type="text" id="depart_code" name="code_postal" required
                    class="input-field bg-gray-100" readonly>
            </label>
            <label class="block">Ville de départ : <input type="text" id="ville_depart" name="ville" required
                    class="input-field bg-gray-100" readonly>
            </label>
            <label class="block">Adresse complète arrivée : <input type="text" id="arrivee_adresse" list="arrivee_list"
                    name="adresse_arrivee" required autocomplete="off" class="input-field"
                    oninput="autoCompleteAdresse('arrivee')" onchange="onAdressePick('arrivee')">
                <datalist id="arrivee_list"></datalist>
            </label>
            <label class="block">Code postal arrivée : <input type="text" id="arrivee_code" name="code_postal_arrivee"
                    required class="input-field bg-gray-100" readonly>
            </label>
            <label class="block">Ville d'arrivée : <input type="text" id="ville_arrivee" name="ville_arrivee" required
                    class="input-field bg-gray-100" readonly>
            </label>
        </div>
        <!-- coordonnées -->
        <input type="hidden" id="depart_lat" name="lat_depart">
        <input type="hidden" id="depart_lon" name="lon_depart">
        <input type="hidden" id="arrivee_lat" name="lat_arrivee">
        <input type="hidden" id="arrivee_lon" name="lon_arrivee">
        <!-- box -->
        <div id="box_section" class="space-y-2">
            <label class="block font-semibold mt-6">Box associée (facultatif) :</label>
            <select id="box_id" name="box_id" class="input-field">
                <option value="">- Aucune -</option>
            </select>
        </div>
        <!-- objets -->
        <div>
            <h3 class="text-lg font-semibold mb-2">Objets</h3>
            <div id="objets-container" class="space-y-4"></div>
            <button type="button" onclick="ajouterObjet()"
                class="mt-3 bg-gray-200 text-sm px-3 py-1 rounded hover:bg-gray-300"> Ajouter un objet </button>
        </div>
        <button type="submit" class="bg-[#1976D2] hover:bg-blue-700 text-white px-6 py-2 rounded"> Enregistrer </button>
    </form>
    <div id="message" class="mt-6 text-sm font-medium"></div>
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
async function autoCompleteAdresse(type) {
    const q = document.getElementById(`${type}_adresse`).value.trim();
    if (q.length < 3) return;
    
    try {
        const res = await fetch(`${API_BASE}/adresses?q=${encodeURIComponent(q)}`, {
            credentials: 'include',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        const data = await res.json();
        
        if (!Array.isArray(data)) {
            console.warn('Erreur API adresses:', data.message || 'Erreur inconnue');
            const input = document.getElementById(`${type}_adresse`);
            if (!input.dataset.errorShown) {
                input.dataset.errorShown = 'true';
                const message = document.createElement('div');
                message.className = 'text-orange-600 text-sm mt-1';
                message.textContent = 'L\'autocomplétion d\'adresse n\'est pas disponible. Vous pouvez saisir l\'adresse manuellement.';
                input.parentNode.appendChild(message);
                
                setTimeout(() => {
                    if (message.parentNode) {
                        message.parentNode.removeChild(message);
                        delete input.dataset.errorShown;
                    }
                }, 5000);
            }
            return;
        }
        
        const dl = document.getElementById(`${type}_list`);
        dl.innerHTML = '';
        data.forEach(a => {
            dl.insertAdjacentHTML('beforeend', `<option value="${a.label}"
                data-postcode="${a.postcode}" data-city="${a.city}"
                data-lat="${a.lat}" data-lon="${a.lon}"></option>`);
        });
    } catch (error) {
        console.warn('Erreur lors de la recherche d\'adresses:', error);
    }
}

function onAdressePick(type) {
    const inp = document.getElementById(`${type}_adresse`);
    const opt = [...document.getElementById(`${type}_list`).options].find(o => o.value === inp.value);
    if (!opt) {
        console.warn(`Aucune option trouvée pour l'adresse: ${inp.value}`);
        return;
    }
    
    const codeField = document.getElementById(`${type}_code`);
    const villeField = document.getElementById(`ville_${type}`);
    const latField = document.getElementById(`${type}_lat`);
    const lonField = document.getElementById(`${type}_lon`);
    
    codeField.value = opt.dataset.postcode || '';
    villeField.value = opt.dataset.city || '';
    latField.value = opt.dataset.lat || '';
    lonField.value = opt.dataset.lon || '';
    
    console.log(`${type} - Adresse sélectionnée:`, {
        adresse: inp.value,
        code: codeField.value,
        ville: villeField.value,
        lat: latField.value,
        lon: lonField.value
    });
    
    if (type === 'arrivee') {
        chargerBoxes(opt.dataset.postcode);
    }
}
/* -------- chargement de l'annonce -------- */
async function chargerAnnonce() {
    const res = await fetch(`${API_BASE}/annonce/annonce?id=${id}`, {
        credentials: 'include',
        headers: {
            'Accept': 'application/json'
        }
    });
    const data = await res.json();
    if (!data.success) {
        document.getElementById('message').innerHTML = `<span class="text-red-600">${data.message}</span>`;
        return;
    }
    const a = data.annonce;
    document.getElementById('depart_adresse').value = a.depart_address || '';
    document.getElementById('depart_code').value = a.depart_code;
    document.getElementById('ville_depart').value = a.depart;
    document.getElementById('depart_lat').value = a.depart_lat || '';
    document.getElementById('depart_lon').value = a.depart_lon || '';
    document.getElementById('arrivee_adresse').value = a.arrivee_address || '';
    document.getElementById('arrivee_code').value = a.arrivee_code;
    document.getElementById('ville_arrivee').value = a.arrivee;
    document.getElementById('arrivee_lat').value = a.arrivee_lat || '';
    document.getElementById('arrivee_lon').value = a.arrivee_lon || '';
    if (a.box_id) document.getElementById('box_option').value = 'yes';
    (a.objets || []).forEach(ajouterObjet);
    /* charge les boxes à proximité et sélectionne celle de l'annonce */
    chargerBoxes(a.arrivee_code, a.box_id || '');
}
/* -------- chargement des boxes -------- */
async function chargerBoxes(code = '', selected = '') {
    const res = await fetch(`${API_BASE}/boxes/near?code=${code}`, {
        credentials: 'include',
        headers: {
            'Accept': 'application/json'
        }
    });
    const data = await res.json();
    const sel = document.getElementById('box_id');
    sel.innerHTML = '<option value="">- Aucune -</option>';
    if (data.success && data.boxes.length) {
        data.boxes.forEach(b => {
            const adresse = b.full_address || `${b.location_city} (${b.location_code})`;
            const opt = document.createElement('option');
            opt.value = b.box_id; // entier sous forme de chaîne
            opt.textContent = `${b.label} - ${adresse}`;
            opt.dataset.address = adresse;
            opt.dataset.city = b.location_city;
            opt.dataset.code = b.location_code;
            opt.dataset.lat = b.lat;
            opt.dataset.lon = b.lon;
            if (String(b.box_id) === String(selected)) {
                opt.selected = true;
                document.getElementById('box_option').value = 'yes';
            }
            sel.appendChild(opt);
        });
    } else {
        const opt = document.createElement('option');
        opt.textContent = 'Aucune box trouvée';
        opt.disabled = true;
        sel.appendChild(opt);
    }
    /* si une box est déjà sélectionnée, met à jour l'adresse d'arrivée */
    if (sel.value) mettreAdresseArriveeDepuisBox();
}
/* -------- met l'adresse d'arrivée à partir de la box -------- */
function mettreAdresseArriveeDepuisBox() {
    const sel = document.getElementById('box_id');
    if (!sel.value) return;
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('arrivee_adresse').value = opt.dataset.address || '';
    document.getElementById('arrivee_code').value = opt.dataset.code || '';
    document.getElementById('ville_arrivee').value = opt.dataset.city || '';
    document.getElementById('arrivee_lat').value = opt.dataset.lat || '';
    document.getElementById('arrivee_lon').value = opt.dataset.lon || '';
}
/* -------- objets -------- */
function ajouterObjet(obj = {}) {
    const i = objetsCount++;
    const div = document.createElement('div');
    div.className = 'border p-4 mb-4 rounded bg-gray-50 relative';
    div.dataset.index = i;
    
    let photosExistantesHtml = '';
    if (obj.photos && obj.photos.length > 0) {
        photosExistantesHtml = `
            <div class="mt-3">
                <p class="text-sm font-medium text-gray-700 mb-2">Photos actuelles :</p>
                <div class="flex flex-wrap gap-3">
                    ${obj.photos.map((photo, photoIndex) => `
                        <div class="relative">
                            <img src="${API_BASE}/storage/uploads/${photo.chemin}" 
                                 class="h-20 w-20 object-cover border rounded">
                            <button type="button" 
                                    onclick="supprimerPhoto(this, ${photo.id})"
                                    class="absolute -top-2 -right-2 bg-red-600 text-white text-xs w-6 h-6 rounded-full hover:bg-red-700"
                                    title="Supprimer cette photo">
                                ×
                            </button>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    div.innerHTML = `
        <input type="hidden" name="objets[${i}][id]"     value="${obj.id||''}">
        <input type="hidden" name="objets[${i}][delete]" value="0" class="delete-flag">

        <button type="button" onclick="marquerASupprimer(this)"
                class="absolute top-2 right-2 bg-red-600 text-white text-xs px-2 py-1 rounded">
            Supprimer
        </button>

        <h4 class="font-semibold mb-2">Objet ${i+1}</h4>

        <label class="block">Nom :
            <input type="text" name="objets[${i}][nom]"
                   value="${obj.nom||''}" required class="input-field">
        </label>

        <label class="block">Quantité :
            <input type="number" name="objets[${i}][quantite]"
                   value="${obj.quantite||1}" required class="input-field">
        </label>

        <label class="block">Dimensions :
            <input type="text" name="objets[${i}][dimensions]"
                   value="${obj.dimensions||''}" class="input-field">
        </label>

        <label class="block">Poids :
            <input type="number" step="0.1" name="objets[${i}][poids]"
                   value="${obj.poids||''}" class="input-field">
        </label>

        <label class="block">Description :
            <textarea name="objets[${i}][description]" class="input-field">
${obj.description||''}</textarea>
        </label>

        ${photosExistantesHtml}

        <label class="block mt-3">Ajouter nouvelles photos :
            <input type="file" name="photos_${i}[]" multiple accept="image/*" class="mt-1">
            <p class="text-xs text-gray-500 mt-1">Vous pouvez ajouter de nouvelles photos en plus de celles existantes</p>
        </label>`;
    document.getElementById('objets-container').appendChild(div);
}

function marquerASupprimer(btn) {
    const box = btn.closest('[data-index]');
    box.querySelector('.delete-flag').value = '1';
    box.classList.add('opacity-50', 'pointer-events-none');
    box.insertAdjacentHTML('beforeend', '<div class="text-red-600 text-sm mt-2">Objet supprimé</div>');
}

function supprimerPhoto(button, photoId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette photo ?')) {
        return;
    }
    
    fetch(`${API_BASE}/annonce/photos/${photoId}`, {
        method: 'DELETE',
        credentials: 'include',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            button.parentElement.remove();
        } else {
            alert('Erreur lors de la suppression : ' + result.message);
        }
    })
    .catch(err => {
        console.warn('Erreur lors de la suppression de la photo:', err);
        button.parentElement.remove();
    });
}
/* -------- gestion box change -------- */
document.getElementById('box_id').addEventListener('change', () => {
    const sel = document.getElementById('box_id');
    const value = sel.value;
    document.getElementById('box_option').value = value ? 'yes' : 'no';
    if (value) {
        mettreAdresseArriveeDepuisBox();
    }
});
/* -------- soumission -------- */
document.getElementById('form-edit').addEventListener('submit', async e => {
    e.preventDefault();
    
    const requiredFields = [
        { id: 'depart_code', name: 'Code postal départ' },
        { id: 'ville_depart', name: 'Ville de départ' },
        { id: 'depart_lat', name: 'Latitude départ' },
        { id: 'depart_lon', name: 'Longitude départ' },
        { id: 'arrivee_code', name: 'Code postal arrivée' },
        { id: 'ville_arrivee', name: 'Ville d\'arrivée' },
        { id: 'arrivee_lat', name: 'Latitude arrivée' },
        { id: 'arrivee_lon', name: 'Longitude arrivée' }
    ];
    
    const missingFields = [];
    for (const field of requiredFields) {
        const element = document.getElementById(field.id);
        if (!element || !element.value.trim()) {
            missingFields.push(field.name);
        }
    }
    
    if (missingFields.length > 0) {
        alert(`Veuillez compléter les informations d'adresse suivantes :\n${missingFields.join('\n')}\n\nUtilisez l'autocomplétion d'adresse pour remplir automatiquement ces champs.`);
        return;
    }
    
    /* au moins un objet actif */
    const actifs = [...document.querySelectorAll('[data-index]')].filter(el => el.querySelector(
        '.delete-flag').value === '0');
    if (!actifs.length) {
        alert("L'annonce doit contenir au moins un objet.");
        return;
    }
    
    const formData = new FormData(e.target);
    if (!document.getElementById('box_id').value) {
        formData.delete('box_id'); 
    }
    
    formData.append('source', 'frontend_edit_annonce');
    
    console.log('Données envoyées:');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    console.log('URL cible:', `${API_BASE}/annonce/update`);
    
    const updateUrl = `${API_BASE}/annonce/update`;
    console.log('URL complète:', updateUrl);
    
    const res = await fetch(updateUrl, {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    });
    
    console.log('URL de réponse:', res.url);
    console.log('Statut de réponse:', res.status);
    
    const out = await res.json();
    const m = document.getElementById('message');
    m.textContent = out.message || (out.success ? 'Modifications enregistrées.' : 'Erreur.');
    m.className = out.success ? 'text-green-600' : 'text-red-600';
    
    if (!out.success && out.details) {
        console.error('Détails de l\'erreur:', out.details);
    }
});
/* lancement */
chargerAnnonce();
</script> <?php include_once '../includes/footer.php'; ?>