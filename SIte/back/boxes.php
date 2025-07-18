<?php require_once('includes/admin_header.php'); ?> <div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800"> Boîtes de stockage</h1>
            <p class="text-gray-600 mt-1">Gérez les points de dépôt et de retrait de colis</p>
        </div>
        <button id="ajouterBtn" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 flex items-center gap-2 transition-colors shadow-sm">
            ➕ Ajouter une boîte
        </button>
    </div>
    <div class="mb-6">
        <div class="relative">
            <input type="text" id="filtre" placeholder=" Rechercher par ville ou code postal..."
                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pl-4">
        </div>
    </div>
    <div id="boxes" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>
</div>
<div id="modalBox" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
            </div>
            <h2 class="text-2xl font-bold text-gray-800" id="modalTitle">Ajouter une boîte</h2>
        </div>
        <form id="formBox" novalidate>
            <input type="hidden" id="box_id">
            <div class="grid gap-4">
                <div>
                    <label for="label" class="block text-sm font-medium text-gray-700 mb-1">Nom de la boîte *</label>
                    <input type="text" id="label" placeholder="Ex: Box A - Centre-ville" class="border p-3 rounded-md w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-md">
                    <h3 class="text-sm font-medium text-gray-700 mb-3"> Adresse de la boîte</h3>
                    <div class="grid gap-3">
                        <input type="text" id="address_street" placeholder="Numéro et nom de la rue" class="border p-3 rounded-md w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <div class="grid grid-cols-2 gap-3">
                            <input type="text" id="address_zip" placeholder="Code postal" class="border p-3 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required pattern="\d{5}">
                            <input type="text" id="address_city" placeholder="Ville" class="border p-3 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 p-4 rounded-md">
                    <h3 class="text-sm font-medium text-gray-700 mb-3"> Zone de couverture</h3>
                    <div class="grid gap-3">
                        <input type="text" id="location_city" placeholder="Ville ou zone desservie" class="border p-3 rounded-md w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <input type="text" id="location_code" placeholder="Code postal ou INSEE de la zone" class="border p-3 rounded-md w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <p class="text-xs text-gray-500 mt-2"> Cette information détermine dans quelle zone cette boîte sera proposée aux utilisateurs</p>
                </div>

                <input type="hidden" id="lat">
                <input type="hidden" id="lon">
                <div id="coordsInfo" class="text-sm text-gray-600 bg-green-50 p-2 rounded-md hidden"></div>
            </div>
            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="button" id="cancelBtn" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">Annuler</button>
                <button type="submit" id="submitBtn"
                    class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                     Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
<script>
const BASE_API = "<?= BASE_API ?>";
let boxes = [];
async function chargerBoxes() {
    const res = await fetch(`${BASE_API}/admin/boxes`, {
        credentials: 'include'
    });
    const data = await res.json();
    boxes = data.boxes ?? [];
    afficherBoxes();
}

function afficherBoxes() {
    const filtre = document.getElementById("filtre").value.toLowerCase();
    const container = document.getElementById("boxes");
    const filtrées = boxes.filter(b => b.address_city.toLowerCase().includes(filtre) || b.address_zip.includes(filtre))
        .sort((a, b) => a.label.localeCompare(b.label));
    container.innerHTML = filtrées.map(box => `
        <div class="bg-white p-4 rounded-lg shadow-sm border hover:shadow-md transition-shadow">
            <h3 class="text-lg font-bold mb-2 text-gray-800">${box.label}</h3>
            <div class="space-y-1 mb-3">
                <p class="text-sm text-gray-600"> ${box.full_address}</p>
                <p class="text-xs text-gray-500"> Zone : ${box.location_city} (${box.location_code})</p>
            </div>
            <div class="flex justify-end gap-2">
                <button onclick="editerBox(${box.box_id})"
                        class="px-3 py-1 text-sm text-blue-600 hover:bg-blue-50 rounded transition-colors">Modifier</button>
                <button onclick="supprimerBox(${box.box_id})"
                        class="px-3 py-1 text-sm text-red-600 hover:bg-red-50 rounded transition-colors">Supprimer</button>
            </div>
        </div>
    `).join("");
}

function ouvrirModal(edition = false) {
    document.getElementById('modalBox').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = edition ? "Modifier la boîte" : "Ajouter une boîte";
}

function fermerModal() {
    document.getElementById('modalBox').classList.add('hidden');
    document.getElementById('formBox').reset();
    document.getElementById('box_id').value = "";
    const coordsDiv = document.getElementById('coordsInfo');
    coordsDiv.classList.add('hidden');
    coordsDiv.innerHTML = "";
}
document.getElementById('filtre').addEventListener('input', afficherBoxes);
document.getElementById('ajouterBtn').addEventListener('click', () => ouvrirModal());
document.getElementById('cancelBtn').addEventListener('click', fermerModal);
document.addEventListener('keydown', e => {
    if (e.key === "Escape") fermerModal();
});
let geoTimer = null;
["address_street", "address_zip", "address_city"].forEach(id => {
    document.getElementById(id).addEventListener('input', () => {
        clearTimeout(geoTimer);
        geoTimer = setTimeout(autocompleteCoords, 500);
    });
});
async function autocompleteCoords() {
    const street = document.getElementById('address_street').value.trim();
    const zip = document.getElementById('address_zip').value.trim();
    const city = document.getElementById('address_city').value.trim();
    if (!street || !zip || !city) return;
    const query = encodeURIComponent(`${street} ${zip} ${city}`);
    const url = `https://api-adresse.data.gouv.fr/search/?q=${query}&limit=1`;
    try {
        const res = await fetch(url);
        const data = await res.json();
        if (data.features && data.features.length) {
            const [lon, lat] = data.features[0].geometry.coordinates;
            document.getElementById('lat').value = lat.toFixed(7);
            document.getElementById('lon').value = lon.toFixed(7);
            const coordsDiv = document.getElementById('coordsInfo');
            coordsDiv.innerHTML = `<strong>Coordonnées trouvées</strong> : ${lat.toFixed(5)}, ${lon.toFixed(5)}`;
            coordsDiv.classList.remove('hidden');
            coordsDiv.classList.add('bg-green-50');
            coordsDiv.classList.remove('bg-red-50', 'bg-yellow-50');
        } else {
            const coordsDiv = document.getElementById('coordsInfo');
            coordsDiv.innerHTML = "<strong>Adresse non trouvée</strong> : impossible d'obtenir les coordonnées automatiquement";
            coordsDiv.classList.remove('hidden');
            coordsDiv.classList.add('bg-red-50');
            coordsDiv.classList.remove('bg-green-50', 'bg-yellow-50');
            document.getElementById('lat').value = "";
            document.getElementById('lon').value = "";
        }
    } catch {
        const coordsDiv = document.getElementById('coordsInfo');
        coordsDiv.innerHTML = "<strong>Erreur réseau</strong> lors de la recherche des coordonnées";
        coordsDiv.classList.remove('hidden');
        coordsDiv.classList.add('bg-yellow-50');
        coordsDiv.classList.remove('bg-green-50', 'bg-red-50');
    }
}
document.getElementById('formBox').addEventListener('submit', async e => {
    e.preventDefault();
    const zip = document.getElementById('address_zip').value;
    if (!/^\d{5}$/.test(zip)) {
        alert("Le code postal doit contenir exactement 5 chiffres.");
        return;
    }
    const lat = document.getElementById('lat').value;
    const lon = document.getElementById('lon').value;
    if (!lat || !lon) {
        alert("Coordonnées manquantes : vérifiez l’adresse.");
        return;
    }
    const id = document.getElementById('box_id').value;
    const payload = {
        label: document.getElementById('label').value,
        address_street: document.getElementById('address_street').value,
        address_zip: zip,
        address_city: document.getElementById('address_city').value,
        location_city: document.getElementById('location_city').value,
        location_code: document.getElementById('location_code').value,
        lat,
        lon
    };
    const method = id ? 'PUT' : 'POST';
    const url = id ? `${BASE_API}/admin/boxes/${id}` : `${BASE_API}/admin/boxes`;
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    const res = await fetch(url, {
        method,
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    });
    submitBtn.disabled = false;
    if (res.ok) {
        fermerModal();
        alert(id ? "Boîte modifiée avec succès." : "Boîte ajoutée avec succès.");
        await chargerBoxes();
    } else {
        alert("Erreur lors de l'enregistrement.");
    }
});

function editerBox(id) {
    const box = boxes.find(b => b.box_id === id);
    if (!box) return;
    document.getElementById('box_id').value = box.box_id;
    document.getElementById('label').value = box.label;
    document.getElementById('address_street').value = box.address_street;
    document.getElementById('address_zip').value = box.address_zip;
    document.getElementById('address_city').value = box.address_city;
    document.getElementById('location_city').value = box.location_city;
    document.getElementById('location_code').value = box.location_code;
    document.getElementById('lat').value = box.lat || "";
    document.getElementById('lon').value = box.lon || "";
    const coordsDiv = document.getElementById('coordsInfo');
    if (box.lat && box.lon) {
        coordsDiv.innerHTML = ` <strong>Coordonnées</strong> : ${box.lat}, ${box.lon}`;
        coordsDiv.classList.remove('hidden');
        coordsDiv.classList.add('bg-green-50');
    } else {
        coordsDiv.classList.add('hidden');
    }
    ouvrirModal(true);
}
async function supprimerBox(id) {
    if (!confirm("Supprimer cette boîte ?")) return;
    const res = await fetch(`${BASE_API}/admin/boxes/${id}`, {
        method: 'DELETE',
        credentials: 'include'
    });
    if (res.ok) {
        alert("Boîte supprimée.");
        await chargerBoxes();
    } else {
        alert("Erreur lors de la suppression.");
    }
}
chargerBoxes();
</script> <?php require_once('includes/admin_footer.php'); ?>