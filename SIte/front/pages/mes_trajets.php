<?php include_once('../includes/header.php'); ?>
<?php include_once('../includes/api_path.php'); ?>

<div id="unauthorized" class="text-red-600 text-center mt-12 hidden">Accès refusé. Réservé aux livreurs.</div>

<div id="page-content" class="hidden max-w-5xl mx-auto py-10 px-6">
    <h2 class="text-2xl font-bold mb-6">🛣️ Mes trajets prévus</h2>

    <div class="bg-gray-100 p-4 rounded shadow mb-8">
        <h3 class="text-lg font-semibold mb-4">➕ Ajouter un trajet</h3>
        <form id="form-trajet" class="space-y-4">

            <div>
                <label class="block mb-1 font-semibold">Code postal départ :</label>
                <input type="text" id="code_depart" oninput="autoCompleteVille('depart')" class="input-field" required>
            </div>
            <div>
                <label class="block mb-1 font-semibold">Ville de départ :</label>
                <input type="text" id="ville_depart" class="input-field bg-gray-100 cursor-not-allowed" readonly required>
            </div>

            <div>
                <label class="block mb-1 font-semibold">Code postal arrivée :</label>
                <input type="text" id="code_arrivee" oninput="autoCompleteVille('arrivee')" class="input-field" required>
            </div>
            <div>
                <label class="block mb-1 font-semibold">Ville d'arrivée :</label>
                <input type="text" id="ville_arrivee" class="input-field bg-gray-100 cursor-not-allowed" readonly required>
            </div>

            <div>
                <label class="block mb-1 font-semibold">Date de départ :</label>
                <input type="date" id="date_depart" class="input-field" required>
            </div>
            <div>
                    <label class="block mb-1 font-semibold">Heure de départ :</label>
                    <input type="time" id="heure_depart" class="input-field" required>
            </div>

            <div>
                <label class="block mb-1 font-semibold">Notes (optionnel) :</label>
                <textarea id="notes" class="input-field"></textarea>
            </div>

            <div class="mt-6">
                <h4 class="text-lg font-semibold mb-2">➕ Villes de passage</h4>
                <div id="waypoints-container" class="space-y-4"></div>
                <button type="button" onclick="ajouterWaypoint()" class="text-blue-600 hover:underline">➕ Ajouter une ville de passage</button>
            </div>

            <button type="submit" class="mt-6 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Ajouter</button>
        </form>
    </div>

    <h3 class="text-xl font-semibold mb-4">📋 Liste de mes trajets</h3>
    <div id="liste-trajets" class="space-y-6"></div>

    <h3 class="text-xl font-semibold mb-4 mt-10">📍 Trajet du jour</h3>
    <div id="trajet-du-jour" class="space-y-6"></div>

    <h3 class="text-xl font-semibold mb-4 mt-10">📁 Trajets terminés</h3>
    <div id="trajets-termines" class="space-y-6"></div>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";
let waypointIndex = 0;

async function verifierAcces() {
    const res = await fetch(`${API_BASE}/get_session`, { credentials: 'include' });
    const data = await res.json();

    if (!data.user || !data.user.roles.includes('Deliverer')) {
        document.getElementById('unauthorized').classList.remove('hidden');
    } else {
        document.getElementById('page-content').classList.remove('hidden');
        chargerTrajets();
    }
}

async function chargerTrajets() {
    const res = await fetch(`${API_BASE}/livreur/routes`, { credentials: 'include' });
    const data = await res.json();

    const now = new Date().toISOString().split('T')[0];

    const containerPrevus = document.getElementById('liste-trajets');
    const containerJour = document.getElementById('trajet-du-jour');
    const containerTermines = document.getElementById('trajets-termines');

    containerPrevus.innerHTML = '';
    containerJour.innerHTML = '';
    containerTermines.innerHTML = '';

    if (data.success && data.routes.length) {
        data.routes.forEach(r => {
            const html = `
                <div class="border border-gray-300 bg-white rounded p-4 shadow relative">
                    <p><strong>Départ :</strong> ${r.ville_depart} (${r.code_depart ?? '-'})</p>
                    <p><strong>Arrivée :</strong> ${r.ville_arrivee} (${r.code_arrivee ?? '-'})</p>
                    <p><strong>Date prévue :</strong> ${r.date_depart ?? 'Non précisée'}</p>
                    <p><strong>Notes :</strong> ${r.notes ?? '—'}</p>
                    ${r.waypoints.length ? `
                        <div class="mt-2">
                            <strong>Villes de passage :</strong>
                            <ul class="list-disc list-inside">
                                ${r.waypoints.map(wp => `<li>${wp.ville} (${wp.code ?? '-'})</li>`).join('')}
                            </ul>
                        </div>
                    ` : ''}
                    <div class="flex flex-wrap gap-2 mt-4">
                        <button onclick="supprimerTrajet(${r.id_trajet})" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">❌ Supprimer</button>
                    </div>
                </div>
            `;

            if (r.date_depart === now) {
                containerJour.innerHTML += html;
            } else if (r.date_depart && r.date_depart < now) {
                containerTermines.innerHTML += html;
            } else {
                containerPrevus.innerHTML += html;
            }
        });
    } else {
        containerPrevus.innerHTML = "<p class='text-gray-500'>Aucun trajet déclaré.</p>";
    }
}

function ajouterWaypoint() {
    const container = document.getElementById('waypoints-container');
    const div = document.createElement('div');
    div.innerHTML = `
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
            <input type="text" id="waypoint_code_${waypointIndex}" placeholder="Code postal" class="input-field" oninput="autoCompleteWaypoint(${waypointIndex})">
            <input type="text" id="waypoint_city_${waypointIndex}" placeholder="Ville" class="input-field bg-gray-100 cursor-not-allowed" readonly>
        </div>
    `;
    container.appendChild(div);
    waypointIndex++;
}

async function autoCompleteVille(type) {
    const code = document.getElementById(`code_${type}`).value.trim();
    const input = document.getElementById(`ville_${type}`);
    if (/^\d{5}$/.test(code)) {
        const res = await fetch(`${API_BASE}/annonce/villes?q=${code}`, { credentials: 'include', headers: { 'Accept': 'application/json' } });
        const villes = await res.json();
        input.value = villes.length ? villes[0].ville : '';
    } else {
        input.value = '';
    }
}

async function autoCompleteWaypoint(index) {
    const codeInput = document.getElementById(`waypoint_code_${index}`);
    const cityInput = document.getElementById(`waypoint_city_${index}`);
    const code = codeInput.value.trim();
    if (/^\d{5}$/.test(code)) {
        const res = await fetch(`${API_BASE}/annonce/villes?q=${code}`, { credentials: 'include', headers: { 'Accept': 'application/json' } });
        const villes = await res.json();
        cityInput.value = villes.length ? villes[0].ville : '';
    } else {
        cityInput.value = '';
    }
}

async function ajouterTrajet(e) {
    e.preventDefault();

    const waypoints = [];
    for (let i = 0; i < waypointIndex; i++) {
        const code = document.getElementById(`waypoint_code_${i}`)?.value.trim();
        const ville = document.getElementById(`waypoint_city_${i}`)?.value.trim();
        if (ville) {
            waypoints.push({ city: ville, postal_code: code });
        }
    }

    const trajet = {
        ville_depart: document.getElementById('ville_depart').value,
        code_depart: document.getElementById('code_depart').value,
        ville_arrivee: document.getElementById('ville_arrivee').value,
        code_arrivee: document.getElementById('code_arrivee').value,
        date_depart: document.getElementById('date_depart').value,
        heure_depart: document.getElementById('heure_depart').value,
        notes: document.getElementById('notes').value,
        waypoints: waypoints
    };

    try {
        const res = await fetch(`${API_BASE}/livreur/routes`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(trajet)
        });
        const result = await res.json();
        alert(result.message || 'Trajet ajouté.');
        document.getElementById('form-trajet').reset();
        document.getElementById('waypoints-container').innerHTML = '';
        waypointIndex = 0;
        chargerTrajets();
    } catch (e) {
        console.error(e);
        alert("Erreur réseau lors de l'ajout.");
    }
}

async function supprimerTrajet(id) {
    if (!confirm("Supprimer ce trajet ?")) return;

    try {
        const res = await fetch(`${API_BASE}/livreur/routes/${id}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json' },
            credentials: 'include',
        });
        const result = await res.json();
        alert(result.message || 'Trajet supprimé.');
        chargerTrajets();
    } catch (e) {
        console.error(e);
        alert("Erreur réseau lors de la suppression.");
    }
}

document.getElementById('form-trajet').addEventListener('submit', ajouterTrajet);
verifierAcces();
</script>

<style>
.input-field {
    @apply border border-gray-300 rounded px-3 py-2 w-full;
}
</style>

<?php include_once('../includes/footer.php'); ?>
