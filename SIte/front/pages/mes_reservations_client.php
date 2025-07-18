<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?>

<h2 class="text-2xl font-bold mb-6">Mes réservations</h2>

<!-- À VENIR -->
<h3 class="text-xl font-semibold mb-2">🔵 À venir</h3>
<table class="table-auto w-full border-collapse border border-gray-300 mb-8">
    <thead class="bg-gray-100">
        <tr>
            <th class="border px-4 py-2">Type</th>
            <th class="border px-4 py-2">Date</th>
            <th class="border px-4 py-2">Heure</th>
            <th class="border px-4 py-2">Adresse</th>
            <th class="border px-4 py-2">Tarif (€)</th>
            <th class="border px-4 py-2">Statut</th>
        </tr>
    </thead>
    <tbody id="upcoming-body">
        <tr><td colspan="6" class="text-center py-4">Chargement...</td></tr>
    </tbody>
</table>

<!-- TERMINÉES -->
<h3 class="text-xl font-semibold mb-2">🔴 Terminées</h3>
<table class="table-auto w-full border-collapse border border-gray-300">
    <thead class="bg-gray-100">
        <tr>
            <th class="border px-4 py-2">Type</th>
            <th class="border px-4 py-2">Date</th>
            <th class="border px-4 py-2">Heure</th>
            <th class="border px-4 py-2">Adresse</th>
            <th class="border px-4 py-2">Tarif (€)</th>
            <th class="border px-4 py-2">Statut</th>
            <th class="border px-4 py-2">Note</th>
            <th class="border px-4 py-2">Commentaire</th>
            <th class="border px-4 py-2">Action</th>
        </tr>
    </thead>
    <tbody id="past-body">
        <tr><td colspan="9" class="text-center py-4">Chargement...</td></tr>
    </tbody>
</table>

<!-- MODAL ÉVALUATION -->
<div id="modal" class="fixed hidden inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h3 class="text-xl font-bold mb-4">Noter cette prestation</h3>
        <form id="eval-form">
            <input type="hidden" id="eval-id">

            <label>Note :</label>
            <select id="rating" required class="border w-full px-2 py-1 mb-3">
                <option value="">Choisir une note</option>
                <option value="5">★★★★★</option>
                <option value="4">★★★★☆</option>
                <option value="3">★★★☆☆</option>
                <option value="2">★★☆☆☆</option>
                <option value="1">★☆☆☆☆</option>
            </select>

            <label>Commentaire :</label>
            <textarea id="comment" class="border w-full px-2 py-1 mb-4" rows="3"></textarea>

            <div class="flex justify-end">
                <button type="button" onclick="closeModal()" class="mr-2 px-4 py-2 bg-gray-400 text-white rounded">Annuler</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Valider</button>
            </div>
        </form>
    </div>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";

function closeModal() {
    document.getElementById('modal').classList.add('hidden');
    document.getElementById('eval-form').reset();
}

function openModal(id) {
    document.getElementById('eval-id').value = id;
    document.getElementById('modal').classList.remove('hidden');
}

async function loadReservations() {
    const res = await fetch(`${API_BASE}/client/mes-reservations`, {
        credentials: 'include',
        headers: { 'Accept': 'application/json' }
    });

    const data = await res.json();
    const today = new Date().toISOString().split('T')[0];

    const upcomingBody = document.getElementById('upcoming-body');
    const pastBody = document.getElementById('past-body');
    upcomingBody.innerHTML = "";
    pastBody.innerHTML = "";

    let hasUpcoming = false, hasPast = false;

    data.forEach(r => {
        const isPast = r.date < today;
        const note = r.note ? '★'.repeat(r.note) : '-';
        const comment = r.commentaire || '-';
        const action = (!r.note && isPast)
            ? `<button onclick="openModal(${r.availability_id})" class="bg-blue-600 text-white px-2 py-1 rounded">Noter</button>`
            : '—';

        const start = new Date(r.date + 'T' + r.start_time);
        const end = new Date(r.date + 'T' + r.end_time);
        const durationMinutes = Math.max(0, (end - start) / (1000 * 60));
        
        let basePrice = 0;
        if (r.is_price_fixed) {
            basePrice = parseFloat(r.fixed_price) || 0;
        } else {
            basePrice = parseFloat(r.provider_price) || 0;
        }
        
        const pricePerMinute = basePrice / 60;
        const totalPrice = (pricePerMinute * durationMinutes).toFixed(2);
        const priceDisplay = totalPrice > 0 ? `${totalPrice}€` : 'Gratuit';

        // Ajout du bouton Annuler pour les réservations à venir
        let annulerBtn = '';
        if (!isPast) {
            // Calcul du délai avant le début du créneau
            const startDate = new Date(r.date + 'T' + r.start_time);
            const now = new Date();
            const diffH = (startDate - now) / (1000 * 60 * 60);
            if (diffH > 5) {
                annulerBtn = `<button onclick="annulerReservation(${r.availability_id}, false)" class="bg-red-600 text-white px-2 py-1 rounded ml-2">Annuler</button>`;
            } else if (diffH > 0) {
                annulerBtn = `<button onclick="annulerReservation(${r.availability_id}, true)" class="bg-red-600 text-white px-2 py-1 rounded ml-2">Annuler</button>`;
            }
        }

        const row = `
            <tr>
                <td class="border px-4 py-2">${r.service_type_name}</td>
                <td class="border px-4 py-2">${r.date}</td>
                <td class="border px-4 py-2">${r.start_time} - ${r.end_time}</td>
                <td class="border px-4 py-2">${r.address}</td>
                <td class="border px-4 py-2">${priceDisplay}</td>
                <td class="border px-4 py-2">${r.status}</td>
                ${isPast
                    ? `<td class="border px-4 py-2">${note}</td>
                       <td class="border px-4 py-2">${comment}</td>
                       <td class="border px-4 py-2 text-center">${action}</td>`
                    : `<td class="border px-4 py-2 text-center" colspan="3">${annulerBtn}</td>`
                }
            </tr>`;

        if (isPast) {
            pastBody.innerHTML += row;
            hasPast = true;
        } else {
            upcomingBody.innerHTML += row;
            hasUpcoming = true;
        }
    });

    if (!hasUpcoming) {
        upcomingBody.innerHTML = "<tr><td colspan='6' class='text-center py-4'>Aucune réservation à venir.</td></tr>";
    }

    if (!hasPast) {
        pastBody.innerHTML = "<tr><td colspan='9' class='text-center py-4'>Aucune réservation passée.</td></tr>";
    }
}

document.getElementById('eval-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const availability_id = document.getElementById('eval-id').value;
    const rating = document.getElementById('rating').value;
    const comment = document.getElementById('comment').value;

    const res = await fetch(`${API_BASE}/client/evaluation`, {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ availability_id, rating, comment })
    });

    const data = await res.json();
    alert(data.message || "Évaluation enregistrée.");
    closeModal();
    loadReservations();
});

// Ajouter la fonction d'annulation
async function annulerReservation(availability_id, late) {
    if (late) {
        if (!confirm("Attention : la prestation sera quand même facturée. Confirmer l'annulation ?")) return;
    } else {
        if (!confirm("Confirmer l'annulation de cette réservation ?")) return;
    }
    try {
        const res = await fetch(`${API_BASE}/client/annuler-reservation/${availability_id}`, {
            method: 'DELETE',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        alert(data.message || "Réservation annulée.");
        loadReservations();
    } catch (err) {
        alert("Erreur lors de l'annulation.");
    }
}

loadReservations();
</script>

<?php include_once('../includes/footer.php'); ?>
