<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');

if (!isset($_GET['id'])) {
    echo '<p class="text-red-600 text-center mt-10">ID manquant.</p>';
    include_once('../includes/footer.php');
    exit;
}
$id = intval($_GET['id']);
?>

<div class="max-w-3xl mx-auto py-10 px-6">
    <h2 class="text-2xl font-bold mb-6">
        🚚 Suivi de votre colis – annonce #<?= $id ?>
    </h2>

    <div id="timeline" class="space-y-3 border-l-2 pl-4">
        <p class="text-gray-500">Chargement…</p>
    </div>

    <div class="mt-8 text-center">
        <a href="dashboard.php"
           class="bg-gray-300 hover:bg-gray-400 text-[#212121] px-5 py-2 rounded shadow">
           ⬅️ Retour
        </a>
    </div>
</div>

<style>
.input-field { @apply border border-gray-300 rounded px-3 py-2 w-full; }
.step-bullet { @apply inline-block w-2 h-2 rounded-full mr-2 bg-[#1976D2]; }
</style>

<script>
const API_BASE = "<?= BASE_API ?>";
const annonceId = <?= $id ?>;

async function loadTracking() {
    const tl = document.getElementById('timeline');
    tl.innerHTML = '<p class="text-gray-500">Chargement…</p>';
    try {
        const res = await fetch(`${API_BASE}/annonce/suivi?id=${annonceId}`, {
            credentials: 'include'
        });
        const d = await res.json();
        if (!d.success) {
            tl.innerHTML = `<p class="text-red-600">${d.message}</p>`;
            return;
        }
        if (!d.events.length) {
            tl.innerHTML = "<p class='text-gray-500'>Aucun suivi pour le moment.</p>";
            return;
        }
        tl.innerHTML = d.events.map(e => `
            <div>
                <span class="step-bullet"></span>
                <span class="font-semibold">${e.status}</span>
                <span class="text-xs text-gray-500">(${e.created_at})</span><br>
                ${e.location_city ? `${e.location_city} (${e.location_code})<br>` : ''}
                ${e.description || ''}
            </div>
        `).join('');
    } catch (err) {
        console.error(err);
        tl.innerHTML = '<p class="text-red-600">Erreur réseau lors du chargement.</p>';
    }
}

document.addEventListener('DOMContentLoaded', loadTracking);
</script>

<?php include_once('../includes/footer.php'); ?>
