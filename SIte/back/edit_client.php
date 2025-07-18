<?php
include_once('includes/admin_header.php');
include_once('includes/api_path.php');

if (!isset($_GET['id'])) {
    echo "<p class='text-red-600 font-semibold'>ID client manquant</p>";
    include_once('includes/admin_footer.php');
    exit;
}

$id = intval($_GET['id']);
?>

<div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-2xl font-bold text-[#212121] mb-6">Modifier les informations du client</h2>

    <form id="edit-form" class="space-y-6 max-w-xl bg-white p-6 rounded shadow border border-gray-200">
        <input type="hidden" name="id_utilisateur" value="<?= $id ?>">

        <div>
            <label class="block font-medium text-[#212121] mb-1">Nom :</label>
            <input type="text" name="nom" required class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-[#1976D2]">
        </div>

        <div>
            <label class="block font-medium text-[#212121] mb-1">Prénom :</label>
            <input type="text" name="prenom" required class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-[#1976D2]">
        </div>

        <div>
            <label class="block font-medium text-[#212121] mb-1">Email :</label>
            <input type="email" name="email" required class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-[#1976D2]">
        </div>

        <button type="submit" class="bg-[#1976D2] hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded">
            Enregistrer
        </button>
    </form>

    <div id="message" class="mt-4 text-sm font-medium"></div>
</div>

<script>
const id = <?= $id ?>;
const API_BASE = "<?= BASE_API ?>";

async function chargerClient() {
    const res = await fetch(`${API_BASE}/admin/clients/${id}`, { credentials: 'include' });
    const data = await res.json();

    if (data.success && data.client) {
        document.querySelector('input[name=nom]').value = data.client.nom;
        document.querySelector('input[name=prenom]').value = data.client.prenom;
        document.querySelector('input[name=email]').value = data.client.email;
    } else {
        document.getElementById('message').textContent = "Erreur de chargement des informations.";
    }
}

document.getElementById('edit-form').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new URLSearchParams(new FormData(form));

    const res = await fetch(`${API_BASE}/admin/clients/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData,
        credentials: 'include'
    });

    const result = await res.json();
    const msg = document.getElementById('message');
    msg.textContent = result.message;
    msg.className = result.success ? 'text-green-600 mt-4' : 'text-red-600 mt-4';
});

chargerClient();
</script>

<?php include_once('includes/admin_footer.php'); ?>
