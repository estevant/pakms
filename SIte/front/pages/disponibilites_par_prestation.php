<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');

$serviceId = $_GET['id'] ?? null;
if (!$serviceId) {
    echo "<p class='text-red-600'>ID de prestation manquant.</p>";
    exit;
}
?>

<h2 class="text-2xl font-bold mb-4">Créneaux pour la prestation #<?= $serviceId ?></h2>

<a href="mes_prestations.php" class="inline-block bg-gray-600 text-white px-4 py-2 rounded mb-4">
    ← Retour à mes prestations
</a>

<!-- Formulaire ajout de créneau -->
<form id="dispo-form" class="mb-4">
    <label>Date :</label>
    <input type="date" id="date" class="border px-2 py-1 w-full mb-2" required>

    <label>Heure de début :</label>
    <input type="time" id="start_time" class="border px-2 py-1 w-full mb-2" required>

    <label>Heure de fin :</label>
    <input type="time" id="end_time" class="border px-2 py-1 w-full mb-2" required>

    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Ajouter le créneau</button>
</form>

<!-- Tableau des créneaux -->
<table class="table-auto w-full border-collapse border border-gray-300">
    <thead class="bg-gray-100">
        <tr>
            <th class="border px-4 py-2">Date</th>
            <th class="border px-4 py-2">Début</th>
            <th class="border px-4 py-2">Fin</th>
            <th class="border px-4 py-2">Prix Total</th>
            <th class="border px-4 py-2">Action</th>
        </tr>
    </thead>
    <tbody id="dispo-body">
        <tr><td colspan="4" class="text-center py-4">Chargement...</td></tr>
    </tbody>
</table>

<script>
const API_BASE = "<?= BASE_API ?>";
const serviceId = <?= $serviceId ?>;

// Charger les créneaux
async function loadDispos() {
    const res = await fetch(`${API_BASE}/prestataire/prestations/${serviceId}/disponibilites`, {
        method: 'GET',
        credentials: 'include',
        headers: { 'Accept': 'application/json' }
    });

    const data = await res.json();
    const body = document.getElementById('dispo-body');
    body.innerHTML = "";

    if (!Array.isArray(data) || data.length === 0) {
        body.innerHTML = "<tr><td colspan='4' class='text-center py-4'>Aucun créneau enregistré.</td></tr>";
        return;
    }

    data.forEach(d => {
        // Calculer si le créneau est passé
        const now = new Date();
        const dateStr = d.date + 'T' + d.end_time;
        const endDate = new Date(dateStr);
        const isPast = endDate < now;
        // Calcul du prix total
        const start = new Date(d.date + 'T' + d.start_time);
        const end = new Date(d.date + 'T' + d.end_time);
        const durationH = (end - start) / (1000 * 60 * 60);
        const total = d.price && durationH > 0 ? (parseFloat(d.price) * durationH).toFixed(2) : '-';
        const row = `
            <tr>
                <td class="border px-4 py-2">${d.date}</td>
                <td class="border px-4 py-2">${d.start_time}</td>
                <td class="border px-4 py-2">${d.end_time}</td>
                <td class="border px-4 py-2">${total} €</td>
                <td class="border px-4 py-2 text-center">
                    <button onclick="deleteDispo(${d.availability_id})" class="bg-red-600 text-white px-2 py-1 rounded" ${isPast ? 'disabled style=\'opacity:0.5;cursor:not-allowed\'' : ''}>
                        Supprimer
                    </button>
                </td>
            </tr>`;
        body.innerHTML += row;
    });
}

// Ajouter un créneau
document.getElementById('dispo-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const date = document.getElementById('date').value;
    const start_time = document.getElementById('start_time').value;
    const end_time = document.getElementById('end_time').value;

    const res = await fetch(`${API_BASE}/prestataire/prestations/${serviceId}/disponibilites`, {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ date, start_time, end_time })
    });

    const result = await res.json();
    alert(result.message || "Créneau ajouté.");
    loadDispos();
});

// Supprimer un créneau
async function deleteDispo(id) {
    if (!confirm("Confirmer la suppression de ce créneau ?")) return;

    try {
        const res = await fetch(`${API_BASE}/prestataire/disponibilites/${id}`, {
            method: 'DELETE',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });

        const result = await res.json();
        alert(result.message || "Créneau supprimé.");
        loadDispos();
    } catch (err) {
        alert("Erreur lors de la suppression.");
        console.error(err);
    }
}

loadDispos();
</script>

<?php include_once('../includes/footer.php'); ?>
