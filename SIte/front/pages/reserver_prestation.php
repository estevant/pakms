<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?>

<h2 class="text-2xl font-bold mb-4">Réserver une prestation</h2>

<div id="prestation-list" class="space-y-8">
    <p>Chargement des prestations...</p>
</div>

<div id="error-msg" class="text-red-600 text-center text-sm font-medium mt-6"></div>

<script>
const API_BASE = "<?= BASE_API ?>";

async function loadPrestations() {
    try {
        const res = await fetch(`${API_BASE}/client/prestations`, {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });
        const prestations = await res.json();

        const container = document.getElementById('prestation-list');
        container.innerHTML = "";

        if (!Array.isArray(prestations) || prestations.length === 0) {
            container.innerHTML = "<p>Aucune prestation disponible.</p>";
            return;
        }

        for (const p of prestations) {
            const section = document.createElement('div');
            section.className = "border border-gray-300 p-4 rounded shadow";

            section.innerHTML = `
                <h3 class="text-xl font-semibold">${p.service_type_name}</h3>
                <p class="text-sm mb-1"><strong>Description :</strong> ${p.details}</p>
                <p class="text-sm mb-1"><strong>Adresse :</strong> ${p.address}</p>
                <p class="text-sm mb-3"><strong>Tarif :</strong> ${p.price} €</p>
                <h4 class="text-md font-bold mb-2">Créneaux disponibles :</h4>
                <div id="dispos-${p.offered_service_id}"><p>Chargement...</p></div>
            `;

            container.appendChild(section);

            // Charger les créneaux liés à la prestation
            loadDispos(p.offered_service_id);
        }
    } catch (err) {
        document.getElementById('error-msg').innerText = "Erreur de chargement des prestations.";
        console.error(err);
    }
}

async function loadDispos(serviceId) {
    try {
        const res = await fetch(`${API_BASE}/prestataire/prestations/${serviceId}/disponibilites`, {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });

        const dispos = await res.json();
        const target = document.getElementById(`dispos-${serviceId}`);
        target.innerHTML = "";

        if (!Array.isArray(dispos) || dispos.length === 0) {
            target.innerHTML = "<p>Aucun créneau disponible.</p>";
            return;
        }

        dispos.forEach(d => {
            const div = document.createElement('div');
            div.className = "mb-2 flex justify-between items-center";

            div.innerHTML = `
                <span>${d.date} | ${d.start_time} - ${d.end_time}</span>
                <button onclick="reserver(${d.availability_id})" class="bg-green-600 text-white px-3 py-1 rounded text-sm">Réserver</button>
            `;

            target.appendChild(div);
        });

    } catch (err) {
        console.error("Erreur chargement créneaux :", err);
    }
}

async function reserver(availabilityId) {
    if (!confirm("Confirmer la réservation de ce créneau ?")) return;

    try {
        const res = await fetch(`${API_BASE}/client/reservations`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ availability_id: availabilityId })
        });

        const data = await res.json();
        if (data.error) {
            alert("Erreur : " + data.error);
        } else {
            alert(data.message || "Réservation effectuée !");
        }

    } catch (err) {
        console.error("Erreur réservation :", err);
        alert("Échec de la réservation.");
    }
}

loadPrestations();
</script>

<?php include_once('../includes/footer.php'); ?>
