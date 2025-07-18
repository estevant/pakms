<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?>

<div class="max-w-6xl mx-auto py-10 px-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Prestations disponibles</h2>
        <button onclick="voirMesReservations()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Mes réservations
        </button>
    </div>

    <div id="content-container">
        <div id="prestations-container">
            <table class="table-auto w-full border-collapse border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2">Type</th>
                        <th class="border px-4 py-2">Description</th>
                        <th class="border px-4 py-2">Adresse</th>
                        <th class="border px-4 py-2">Tarif (€)</th>
                        <th class="border px-4 py-2">Date</th>
                        <th class="border px-4 py-2">Heure</th>
                        <th class="border px-4 py-2">Action</th>
                    </tr>
                </thead>
                <tbody id="presta-body">
                    <tr><td colspan="7" class="text-center py-4">Chargement...</td></tr>
                </tbody>
            </table>
        </div>

        <div id="reservations-container" class="hidden">
            <h3 class="text-xl font-semibold mb-4">Mes réservations</h3>
            <table class="table-auto w-full border-collapse border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2">Type</th>
                        <th class="border px-4 py-2">Description</th>
                        <th class="border px-4 py-2">Adresse</th>
                        <th class="border px-4 py-2">Tarif (€)</th>
                        <th class="border px-4 py-2">Date</th>
                        <th class="border px-4 py-2">Heure</th>
                        <th class="border px-4 py-2">Statut</th>
                        <th class="border px-4 py-2">Paiement</th>
                    </tr>
                </thead>
                <tbody id="reservations-body">
                    <tr><td colspan="8" class="text-center py-4">Chargement...</td></tr>
                </tbody>
            </table>
            <button onclick="retourPrestations()" class="mt-4 bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                Retour aux prestations
            </button>
        </div>
    </div>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";

async function loadPrestations() {
    try {
        const res = await fetch(`${API_BASE}/client/prestations-disponibles`, {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });

        const data = await res.json();
        const body = document.getElementById('presta-body');
        body.innerHTML = "";

        if (!Array.isArray(data) || data.length === 0) {
            body.innerHTML = "<tr><td colspan='7' class='text-center py-4'>Aucune prestation disponible actuellement.</td></tr>";
            return;
        }

        data.forEach(p => {
            const start = new Date(p.date + 'T' + p.start_time);
            const end = new Date(p.date + 'T' + p.end_time);
            const durationMinutes = Math.max(0, (end - start) / (1000 * 60));
            
            let basePrice = 0;
            if (p.is_price_fixed) {
                basePrice = parseFloat(p.fixed_price) || 0;
            } else {
                basePrice = parseFloat(p.provider_price) || 0;
            }
            
            const pricePerMinute = basePrice / 60; // Prix par minute
            const totalPrice = (pricePerMinute * durationMinutes).toFixed(2);
            
            const basePriceDisplay = basePrice > 0 ? `${basePrice.toFixed(2)}€/h` : 'Gratuit';
            const totalDisplay = totalPrice > 0 ? `${totalPrice}€` : 'Gratuit';
            
            const row = `
                <tr>
                    <td class="border px-4 py-2">${p.service_type_name}</td>
                    <td class="border px-4 py-2">${p.details}</td>
                    <td class="border px-4 py-2">${p.address}</td>
                    <td class="border px-4 py-2">
                        <div class="text-sm">
                            <div class="font-semibold">${totalDisplay}</div>
                            <div class="text-gray-600">(${basePriceDisplay})</div>
                        </div>
                    </td>
                    <td class="border px-4 py-2">${p.date}</td>
                    <td class="border px-4 py-2">${p.start_time} - ${p.end_time}</td>
                    <td class="border px-4 py-2 text-center">
                        <button onclick="reserver(${p.availability_id})" class="bg-blue-600 text-white px-2 py-1 rounded">
                            Réserver
                        </button>
                    </td>
                </tr>`;
            body.innerHTML += row;
        });

    } catch (err) {
        console.error(err);
        document.getElementById('presta-body').innerHTML = "<tr><td colspan='7' class='text-red-600 text-center py-4'>Erreur de chargement.</td></tr>";
    }
}

async function loadReservations() {
    try {
        const res = await fetch(`${API_BASE}/client/mes-reservations`, {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });

        const data = await res.json();
        const body = document.getElementById('reservations-body');
        body.innerHTML = "";

        if (!Array.isArray(data) || data.length === 0) {
            body.innerHTML = "<tr><td colspan='8' class='text-center py-4'>Aucune réservation trouvée.</td></tr>";
            return;
        }

        data.forEach(r => {
            const start = new Date(r.date + 'T' + r.start_time);
            const end = new Date(r.date + 'T' + r.end_time);
            const durationMinutes = Math.max(0, (end - start) / (1000 * 60));
            
            let basePrice = 0;
            if (r.is_price_fixed) {
                basePrice = parseFloat(r.fixed_price) || 0;
            } else {
                basePrice = parseFloat(r.provider_price) || 0;
            }
            
            const pricePerMinute = basePrice / 60; // Prix par minute
            const totalPrice = (pricePerMinute * durationMinutes).toFixed(2);
            
            const basePriceDisplay = basePrice > 0 ? `${basePrice.toFixed(2)}€/h` : 'Gratuit';
            const totalDisplay = totalPrice > 0 ? `${totalPrice}€` : 'Gratuit';
            
            const statusClass = r.status === 'validée' ? 'text-green-600' : 'text-orange-600';
            const paymentStatus = r.is_paid ? 
                '<span class="text-green-600 font-semibold">✓ Payé</span>' : 
                '<span class="text-red-600 font-semibold">✗ En attente</span>';
            
            const row = `
                <tr>
                    <td class="border px-4 py-2">${r.service_type_name}</td>
                    <td class="border px-4 py-2">${r.details}</td>
                    <td class="border px-4 py-2">${r.address}</td>
                    <td class="border px-4 py-2">
                        <div class="text-sm">
                            <div class="font-semibold">${totalDisplay}</div>
                            <div class="text-gray-600">(${basePriceDisplay})</div>
                        </div>
                    </td>
                    <td class="border px-4 py-2">${r.date}</td>
                    <td class="border px-4 py-2">${r.start_time} - ${r.end_time}</td>
                    <td class="border px-4 py-2 text-center ${statusClass}">${r.status}</td>
                    <td class="border px-4 py-2 text-center">${paymentStatus}</td>
                </tr>`;
            body.innerHTML += row;
        });

    } catch (err) {
        console.error(err);
        document.getElementById('reservations-body').innerHTML = "<tr><td colspan='8' class='text-red-600 text-center py-4'>Erreur de chargement.</td></tr>";
    }
}

function voirMesReservations() {
    document.getElementById('prestations-container').classList.add('hidden');
    document.getElementById('reservations-container').classList.remove('hidden');
    loadReservations();
}

function retourPrestations() {
    document.getElementById('reservations-container').classList.add('hidden');
    document.getElementById('prestations-container').classList.remove('hidden');
    loadPrestations();
}

async function reserver(availability_id) {
    const ok = confirm("Confirmer la réservation de ce créneau ?");
    if (!ok) return;

    try {
        const res = await fetch(`${API_BASE}/client/reserver`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ availability_id })
        });

        const result = await res.json();
        
        if (result.message) {
            alert(result.message);
            
            if (result.reservation_id) {
                try {
                    const stripeRes = await fetch(`${API_BASE}/stripe/create-session-service`, {
                        method: 'POST',
                        credentials: 'include',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ reservation_id: result.reservation_id })
                    });
                    
                    const stripeData = await stripeRes.json();
                    if (stripeData.url) {
                        window.location.href = stripeData.url;
                    } else {
                        console.error('Erreur Stripe:', stripeData.error);
                        alert('Erreur lors de la création de la session de paiement.');
                    }
                } catch (stripeErr) {
                    console.error('Erreur requête Stripe:', stripeErr);
                    alert('Erreur lors de la création de la session de paiement.');
                }
            }
        } else {
            alert("Réservation enregistrée.");
        }
        
        loadPrestations(); // refresh
    } catch (err) {
        alert("Erreur lors de la réservation.");
        console.error(err);
    }
}

loadPrestations();
</script>

<?php include_once('../includes/footer.php'); ?>
