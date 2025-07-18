<?php 
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?>

<h2 class="text-2xl font-bold mb-4">Mes prestations</h2>

<div class="flex gap-4 mb-6">
    <a href="mes_anciennes_prestations.php" class="inline-block bg-gray-600 text-white px-4 py-2 rounded">
        Voir mes prestations terminées
    </a>
    <button onclick="showNewTypeForm()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors">
        ➕ Ajouter un nouveau type de prestation
    </button>
</div>

<!-- FORMULAIRE DE DEMANDE DE NOUVEAU TYPE DE PRESTATION -->
<div id="new-type-form-container" class="bg-gray-50 p-6 rounded-lg mb-6 hidden">
    <h3 class="text-lg font-semibold mb-4 text-blue-700">Demander l'ajout d'une prestation</h3>
    <p class="text-gray-600 mb-4">Sélectionnez un type de prestation existant et envoyez une demande d'ajout. Votre prestation sera validée par l'administration avant d'apparaître dans votre liste.</p>
    
    <form id="new-type-form" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="new-type-nom" class="block font-semibold mb-2">Type de prestation <span class="text-red-500">*</span></label>
                <select id="new-type-nom" 
                        name="nom" 
                        class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        required>
                    <option value="">Choisir un type de prestation...</option>
                </select>
            </div>
            
            <div>
                <label for="new-type-price" class="block font-semibold mb-2">Tarif (€) <span class="text-red-500">*</span></label>
                <input type="number" id="new-type-price" step="0.01" min="0" required 
                       class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="25.00">
                <p id="price-info" class="text-xs text-gray-500 mt-1"></p>
            </div>
        </div>

        <div>
            <label for="new-type-description" class="block font-semibold mb-2">Description <span class="text-red-500">*</span></label>
            <textarea id="new-type-description" 
                      name="description" 
                      rows="3" 
                      class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" 
                      placeholder="Décrivez votre prestation, vos compétences, conditions..." required></textarea>
        </div>

        <div class="relative">
            <label for="new-type-address" class="block font-semibold mb-2">Adresse <span class="text-red-500">*</span></label>
            <input type="text" id="new-type-address" required 
                   class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Commencez à taper une adresse...">
            <ul id="address-suggestions" class="absolute bg-white border border-gray-300 w-full hidden z-10 max-h-40 overflow-y-auto"></ul>
            <div id="address-spinner" class="text-gray-500 text-sm hidden mt-1">Recherche d'adresses...</div>
        </div>

        <div class="flex justify-end space-x-3">
            <button type="button" onclick="hideNewTypeForm()" 
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors">
                Annuler
            </button>
            <button type="submit" 
                    class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition-colors">
                Envoyer la demande
            </button>
        </div>
    </form>

    <div id="new-type-msg" class="mt-4 text-sm font-medium text-center"></div>
</div>

<!-- FORMULAIRE DE MODIFICATION -->
<div id="edit-form-container" class="bg-blue-50 p-6 rounded-lg mb-6 hidden">
    <h3 class="text-lg font-semibold mb-4 text-blue-700">Modifier la prestation</h3>
    
    <form id="edit-form" class="space-y-4">
        <input type="hidden" id="edit-prestation-id" value="">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="edit-type" class="block font-semibold mb-2">Type de prestation <span class="text-red-500">*</span></label>
                <input type="text" id="edit-type" required 
                       class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label for="edit-price" class="block font-semibold mb-2">Tarif (€) <span class="text-red-500">*</span></label>
                <input type="number" id="edit-price" step="0.01" min="0" required 
                       class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label for="edit-description" class="block font-semibold mb-2">Description <span class="text-red-500">*</span></label>
            <textarea id="edit-description" rows="3" required 
                      class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="relative">
            <label for="edit-address" class="block font-semibold mb-2">Adresse <span class="text-red-500">*</span></label>
            <input type="text" id="edit-address" required 
                   class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Commencez à taper une adresse...">
            <ul id="edit-address-suggestions" class="absolute bg-white border border-gray-300 w-full hidden z-10 max-h-40 overflow-y-auto"></ul>
            <div id="edit-address-spinner" class="text-gray-500 text-sm hidden mt-1">Recherche d'adresses...</div>
        </div>

        <div class="flex justify-end space-x-3">
            <button type="button" onclick="hideEditForm()" 
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors">
                Annuler
            </button>
            <button type="submit" 
                    class="bg-yellow-500 text-white px-6 py-2 rounded hover:bg-yellow-600 transition-colors">
                Modifier la prestation
            </button>
        </div>
    </form>

    <div id="edit-msg" class="mt-4 text-sm font-medium text-center"></div>
</div>

<!-- MES PRESTATIONS -->
<h3 class="text-xl font-semibold mb-4 text-blue-700">📋 Mes prestations</h3>
<div id="prestations-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <div class="text-center py-4">Chargement...</div>
</div>

<!-- CRÉNEAUX FUTURS -->
<h3 class="text-xl font-semibold mb-4 text-green-700">📅 Mes créneaux à venir</h3>
<table class="table-auto w-full border-collapse border border-gray-300 mb-8">
    <thead class="bg-gray-100">
        <tr>
            <th class="border px-4 py-2">Type</th>
            <th class="border px-4 py-2">Date</th>
            <th class="border px-4 py-2">Heure</th>
            <th class="border px-4 py-2">Adresse</th>
            <th class="border px-4 py-2">Tarif (€)</th>
            <th class="border px-4 py-2">Statut</th>
            <th class="border px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody id="creneaux-body">
        <tr><td colspan="7" class="text-center py-4">Chargement...</td></tr>
    </tbody>
</table>

<script>
const API_BASE = "<?= BASE_API ?>";

// Ajout : fonction pour initialiser la session côté API
async function ensureApiSession() {
    await fetch(`${API_BASE}/get_session`, {
        method: 'GET',
        credentials: 'include'
    });
}

// Charger les prestations et créneaux
async function loadPrestations() {
    try {
        const res = await fetch(`${API_BASE}/prestataire/prestations`, {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });

        const data = await res.json();
        
        // Séparer les prestations et les créneaux
        const prestations = [];
        const creneaux = [];
        const prestationsMap = new Map();
        
        data.forEach(item => {
            if (!item.date) {
                // C'est une prestation de base
                prestations.push(item);
                prestationsMap.set(item.offered_service_id, item);
            } else {
                // C'est un créneau
                creneaux.push(item);
            }
        });

        // Afficher les prestations
        const prestationsContainer = document.getElementById('prestations-container');
        if (prestations.length === 0) {
            prestationsContainer.innerHTML = '<div class="text-center py-8 text-gray-500">Aucune prestation enregistrée.</div>';
        } else {
            prestationsContainer.innerHTML = prestations.map(p => {
                // Échapper les caractères spéciaux pour éviter les erreurs JavaScript
                const escapedName = p.service_type_name.replace(/'/g, "\\'").replace(/"/g, '\\"');
                const escapedDetails = p.details.replace(/'/g, "\\'").replace(/"/g, '\\"');
                const escapedAddress = p.address.replace(/'/g, "\\'").replace(/"/g, '\\"');
                
                return `
                    <div class="bg-white border border-gray-300 rounded-lg p-4 shadow-sm">
                        <h4 class="font-semibold text-lg text-blue-700 mb-2">${p.service_type_name}</h4>
                        <p class="text-gray-600 mb-2">${p.details}</p>
                        <p class="text-sm text-gray-500 mb-3">📍 ${p.address}</p>
                        <p class="font-semibold text-green-600 mb-3">${p.price} €</p>
                        <div class="flex gap-2">
                            <button onclick="editPrestation(${p.offered_service_id}, '${escapedName}', '${escapedDetails}', '${escapedAddress}', ${p.price})"
                                class="bg-yellow-500 text-white px-3 py-1 rounded text-sm hover:bg-yellow-600">Modifier</button>
                            <button onclick="deletePrestation(${p.offered_service_id})"
                                class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">Supprimer</button>
                            <button onclick="window.location.href='disponibilites_par_prestation.php?id=${p.offered_service_id}'"
                                class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">+ Créneau</button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Afficher les créneaux
        const creneauxBody = document.getElementById('creneaux-body');
        if (creneaux.length === 0) {
            creneauxBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-gray-500">Aucun créneau à venir.</td></tr>';
        } else {
            creneauxBody.innerHTML = creneaux.map(c => {
                const status = c.is_reserved ? 
                    '<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Réservé</span>' : 
                    '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Disponible</span>';
                // Calcul du prix total (prix * durée en heures)
                let total = c.price;
                if (c.start_time && c.end_time) {
                    const [h1, m1] = c.start_time.split(":").map(Number);
                    const [h2, m2] = c.end_time.split(":").map(Number);
                    let duree = (h2 + m2/60) - (h1 + m1/60);
                    if (duree < 0) duree += 24; // gestion chevauchement minuit
                    total = (c.price * duree).toFixed(2);
                }
                return `
                    <tr>
                        <td class="border px-4 py-2">${c.service_type_name}</td>
                        <td class="border px-4 py-2">${c.date}</td>
                        <td class="border px-4 py-2">${c.start_time} - ${c.end_time}</td>
                        <td class="border px-4 py-2">${c.address}</td>
                        <td class="border px-4 py-2">${total} €</td>
                        <td class="border px-4 py-2 text-center">${status}</td>
                        <td class="border px-4 py-2 text-center">
                            <button onclick="window.location.href='disponibilites_par_prestation.php?id=${c.offered_service_id}'"
                                class="bg-blue-500 text-white px-2 py-1 rounded text-sm hover:bg-blue-600">Gérer</button>
                        </td>
                    </tr>`;
            }).join('');
        }

    } catch (err) {
        console.error(err);
        document.getElementById('prestations-container').innerHTML = '<div class="text-center py-8 text-red-500">Erreur de chargement.</div>';
        document.getElementById('creneaux-body').innerHTML = '<tr><td colspan="7" class="text-center py-4 text-red-500">Erreur de chargement.</td></tr>';
    }
}

// Fonction pour modifier une prestation
function editPrestation(id, type, description, address, price) {
    console.log('editPrestation appelée avec:', { id, type, description, address, price });
    
    try {
        document.getElementById('edit-prestation-id').value = id;
        document.getElementById('edit-type').value = type;
        document.getElementById('edit-description').value = description;
        document.getElementById('edit-address').value = address;
        document.getElementById('edit-price').value = price;
        
        document.getElementById('edit-form-container').classList.remove('hidden');
        document.getElementById('edit-msg').textContent = '';
    } catch (error) {
        console.error('Erreur dans editPrestation:', error);
        alert('Erreur lors de l\'ouverture du formulaire de modification');
    }
}

// Fonction pour masquer le formulaire de modification
function hideEditForm() {
    document.getElementById('edit-form-container').classList.add('hidden');
    document.getElementById('edit-form').reset();
    document.getElementById('edit-msg').textContent = '';
}

// Fonction pour supprimer une prestation
async function deletePrestation(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette prestation ?')) {
        return;
    }
    
    try {
        const res = await fetch(`${API_BASE}/prestataire/prestations/${id}`, {
            method: 'DELETE',
            credentials: 'include',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        const data = await res.json();
        
        if (data.success || data.message) {
            alert(data.message || 'Prestation supprimée avec succès.');
            loadPrestations();
        } else {
            alert(data.error || 'Erreur lors de la suppression.');
        }
    } catch (err) {
        console.error('Erreur lors de la suppression:', err);
        alert('Erreur de connexion. Veuillez réessayer.');
    }
}

// Fonctions pour le formulaire de nouveau type de prestation
function showNewTypeForm() {
    document.getElementById('new-type-form-container').classList.remove('hidden');
    loadExistingServiceTypes();
}

function hideNewTypeForm() {
    document.getElementById('new-type-form-container').classList.add('hidden');
    document.getElementById('new-type-form').reset();
    document.getElementById('new-type-msg').textContent = '';
    document.getElementById('price-info').textContent = '';
}

// Gérer le changement de type de prestation
function handleServiceTypeChange() {
    const select = document.getElementById('new-type-nom');
    const priceInput = document.getElementById('new-type-price');
    const priceInfo = document.getElementById('price-info');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption && selectedOption.value) {
        const isPriceFixed = selectedOption.dataset.isPriceFixed === '1';
        const fixedPrice = selectedOption.dataset.fixedPrice;
        const defaultPrice = selectedOption.dataset.price;
        
        if (isPriceFixed && fixedPrice && fixedPrice !== '') {
            // Prix imposé par EcoDeli - désactiver le champ
            priceInput.value = fixedPrice;
            priceInput.disabled = true;
            priceInput.classList.add('bg-gray-100');
            priceInfo.textContent = '⚠️ Prix imposé par EcoDeli - non modifiable';
            priceInfo.className = 'text-xs text-orange-600 mt-1';
        } else if (defaultPrice && defaultPrice !== '') {
            // Prix par défaut - activer le champ
            priceInput.value = defaultPrice;
            priceInput.disabled = false;
            priceInput.classList.remove('bg-gray-100');
            priceInfo.textContent = '💡 Prix suggéré, vous pouvez le modifier';
            priceInfo.className = 'text-xs text-blue-600 mt-1';
        } else {
            // Aucun prix - activer le champ
            priceInput.value = '';
            priceInput.disabled = false;
            priceInput.classList.remove('bg-gray-100');
            priceInfo.textContent = '';
        }
    } else {
        // Aucun type sélectionné
        priceInput.value = '';
        priceInput.disabled = false;
        priceInput.classList.remove('bg-gray-100');
        priceInfo.textContent = '';
    }
}

// Charger les types de prestation existants
async function loadExistingServiceTypes() {
    try {
        const res = await fetch(`${API_BASE}/servicetypes`, { credentials: 'include' });
        const data = await res.json();
        
        const select = document.getElementById('new-type-nom');
        select.innerHTML = '<option value="">Choisir un type de prestation...</option>';
        
        if (data.data && data.data.length > 0) {
            data.data.forEach(type => {
                const option = document.createElement('option');
                option.value = type.service_type_id;
                option.textContent = type.name;
                // Stocker les informations du type dans l'option
                option.dataset.isPriceFixed = type.is_price_fixed ? '1' : '';
                option.dataset.fixedPrice = type.fixed_price || '';
                option.dataset.price = type.price || '';
                select.appendChild(option);
            });
        }
    } catch (err) {
        console.error('Erreur lors du chargement des types:', err);
    }
}

// Lier le changement de type au handler
if (document.getElementById('new-type-nom')) {
    document.getElementById('new-type-nom').addEventListener('change', handleServiceTypeChange);
}

// Formulaire de demande d'ajout de prestation
document.getElementById('new-type-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const typeId = document.getElementById('new-type-nom').value;
    const price = document.getElementById('new-type-price').value;
    const description = document.getElementById('new-type-description').value.trim();
    const address = document.getElementById('new-type-address').value.trim();
    const msg = document.getElementById('new-type-msg');
    const submitBtn = this.querySelector('button[type="submit"]');
    
    if (!typeId || !price || !description || !address) {
        msg.textContent = 'Tous les champs sont obligatoires.';
        msg.className = 'text-red-600';
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Envoi de la demande...';
    msg.textContent = '';
    
    try {
        const res = await fetch(`${API_BASE}/prestataire/demande-prestation`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                service_type_id: typeId,
                price: parseFloat(price),
                description: description,
                address: address
            })
        });
        
        const data = await res.json();
        
        if (data.success) {
            msg.textContent = 'Demande envoyée avec succès ! Votre prestation sera validée par l\'administration avant d\'apparaître dans votre liste.';
            msg.className = 'text-green-600';
            
            // Réinitialiser le formulaire
            this.reset();
            document.getElementById('price-info').textContent = '';
            
            // Masquer le formulaire après 3 secondes
            setTimeout(() => {
                hideNewTypeForm();
            }, 3000);
        } else {
            msg.textContent = data.message || 'Erreur lors de l\'envoi de la demande.';
            msg.className = 'text-red-600';
        }
    } catch (err) {
        console.error('Erreur lors de l\'envoi:', err);
        msg.textContent = 'Erreur de connexion. Veuillez réessayer.';
        msg.className = 'text-red-600';
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Envoyer la demande';
    }
});

// Formulaire de modification
document.getElementById('edit-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const id = document.getElementById('edit-prestation-id').value;
    const type = document.getElementById('edit-type').value;
    const description = document.getElementById('edit-description').value.trim();
    const address = document.getElementById('edit-address').value.trim();
    const price = document.getElementById('edit-price').value;
    const msg = document.getElementById('edit-msg');
    const submitBtn = this.querySelector('button[type="submit"]');
    
    if (!type || !price || !description || !address) {
        msg.textContent = 'Tous les champs sont obligatoires.';
        msg.className = 'text-red-600';
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Modification en cours...';
    msg.textContent = '';
    
    try {
        const res = await fetch(`${API_BASE}/prestataire/prestations/${id}`, {
            method: 'PUT',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                type: type,
                price: parseFloat(price),
                description: description,
                address: address
            })
        });
        
        const data = await res.json();
        
        if (data.success || data.message) {
            msg.textContent = data.message || 'Prestation modifiée avec succès !';
            msg.className = 'text-green-600';
            
            // Masquer le formulaire après 3 secondes
            setTimeout(() => {
                hideEditForm();
            }, 3000);
            
            // Recharger la liste des prestations
            loadPrestations();
        } else {
            msg.textContent = data.error || 'Erreur lors de la modification.';
            msg.className = 'text-red-600';
        }
    } catch (err) {
        console.error('Erreur lors de la modification:', err);
        msg.textContent = 'Erreur de connexion. Veuillez réessayer.';
        msg.className = 'text-red-600';
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Modifier la prestation';
    }
});

const GEOAPIFY_KEY = "4895a280184449b48cb968fb3771f9ae";

document.addEventListener('DOMContentLoaded', function() {
    // Pour le formulaire d'ajout
    const addressInput = document.getElementById('new-type-address');
    const suggestionsBox = document.getElementById('address-suggestions');
    const spinner = document.getElementById('address-spinner');

    if (addressInput) {
        let timeoutId;
        addressInput.addEventListener('input', () => {
            const query = addressInput.value.trim();
            clearTimeout(timeoutId);

            if (query.length < 3) {
                suggestionsBox.classList.add('hidden');
                spinner.classList.add('hidden');
                return;
            }

            timeoutId = setTimeout(async () => {
                spinner.classList.remove('hidden');
                suggestionsBox.classList.add('hidden');
                try {
                    const res = await fetch(`https://api.geoapify.com/v1/geocode/autocomplete?text=${encodeURIComponent(query)}&limit=5&lang=fr&apiKey=${GEOAPIFY_KEY}`);
                    const data = await res.json();

                    suggestionsBox.innerHTML = '';
                    if (data.features && data.features.length > 0) {
                        data.features.forEach(item => {
                            const li = document.createElement('li');
                            li.textContent = item.properties.formatted;
                            li.className = "px-3 py-2 cursor-pointer hover:bg-gray-100 border-b border-gray-200 last:border-b-0";
                            li.addEventListener('click', () => {
                                addressInput.value = item.properties.formatted;
                                suggestionsBox.classList.add('hidden');
                            });
                            suggestionsBox.appendChild(li);
                        });
                        suggestionsBox.classList.remove('hidden');
                    }
                } catch (e) {
                    suggestionsBox.innerHTML = '<li class="px-3 py-2 text-red-600">Erreur lors de la recherche d\'adresse</li>';
                    suggestionsBox.classList.remove('hidden');
                } finally {
                    spinner.classList.add('hidden');
                }
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!suggestionsBox.contains(e.target) && e.target !== addressInput) {
                suggestionsBox.classList.add('hidden');
            }
        });
    }

    // Autocomplétion pour le formulaire de modification
    const editAddressInput = document.getElementById('edit-address');
    const editSuggestionsBox = document.getElementById('edit-address-suggestions');
    const editSpinner = document.getElementById('edit-address-spinner');
    
    if (editAddressInput) {
        let editTimeoutId;
        
        editAddressInput.addEventListener('input', () => {
            const query = editAddressInput.value.trim();
            
            // Annuler la recherche précédente
            clearTimeout(editTimeoutId);
            
            if (query.length < 3) {
                editSuggestionsBox.classList.add('hidden');
                editSpinner.classList.add('hidden');
                return;
            }

            // Attendre 300ms avant de faire la recherche
            editTimeoutId = setTimeout(async () => {
                editSpinner.classList.remove('hidden');
                editSuggestionsBox.classList.add('hidden');

                try {
                    const res = await fetch(`https://api.geoapify.com/v1/geocode/autocomplete?text=${encodeURIComponent(query)}&limit=5&lang=fr&apiKey=${GEOAPIFY_KEY}`);
                    const data = await res.json();

                    editSuggestionsBox.innerHTML = '';
                    
                    if (data.features && data.features.length > 0) {
                        data.features.forEach(item => {
                            const li = document.createElement('li');
                            li.textContent = item.properties.formatted;
                            li.className = "px-3 py-2 cursor-pointer hover:bg-gray-100 border-b border-gray-200 last:border-b-0";
                            li.addEventListener('click', () => {
                                editAddressInput.value = item.properties.formatted;
                                editSuggestionsBox.classList.add('hidden');
                            });
                            editSuggestionsBox.appendChild(li);
                        });
                        editSuggestionsBox.classList.remove('hidden');
                    }
                } catch (e) {
                    console.error("Erreur Geoapify :", e);
                } finally {
                    editSpinner.classList.add('hidden');
                }
            }, 300);
        });

        // Masquer les suggestions quand on clique ailleurs
        document.addEventListener('click', (e) => {
            if (!editSuggestionsBox.contains(e.target) && e.target !== editAddressInput) {
                editSuggestionsBox.classList.add('hidden');
            }
        });
    }
});

// Au chargement de la page, on s'assure que la session API est initialisée avant de charger les prestations
window.addEventListener('DOMContentLoaded', async () => {
    await ensureApiSession();
    loadPrestations();
});
</script>

<?php include_once('../includes/footer.php'); ?> 