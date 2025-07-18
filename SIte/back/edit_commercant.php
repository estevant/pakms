<?php
include_once('includes/admin_header.php');
include_once('includes/api_path.php');

if (!isset($_GET['id'])) {
    echo "<p class='text-red-600 font-semibold'>ID commerçant manquant</p>";
    include_once('includes/admin_footer.php');
    exit;
}

$id = intval($_GET['id']);
?>

<div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-2xl font-bold text-[#212121] mb-6">Modifier le commerçant</h2>

    <form id="form-edit" class="space-y-6 max-w-xl bg-white p-6 rounded shadow border border-gray-200">
        <input type="hidden" name="id_utilisateur" value="<?= $id ?>">

        <div>
            <label class="block font-medium text-[#212121] mb-1">Nom :</label>
            <input type="text" name="nom" required class="border border-gray-300 rounded px-3 py-2 w-full">
        </div>

        <div>
            <label class="block font-medium text-[#212121] mb-1">Prénom :</label>
            <input type="text" name="prenom" required class="border border-gray-300 rounded px-3 py-2 w-full">
        </div>

        <div>
            <label class="block font-medium text-[#212121] mb-1">Email :</label>
            <input type="email" name="email" required class="border border-gray-300 rounded px-3 py-2 w-full">
        </div>

        <div>
            <label class="block font-medium text-[#212121] mb-1">Raison sociale :</label>
            <input type="text" name="raison_sociale" required class="border border-gray-300 rounded px-3 py-2 w-full">
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

async function chargerCommercant() {
    try {
        const res = await fetch(`${API_BASE}/admin/commercants/${id}`, { credentials: 'include' });
        const data = await res.json();

        if (data.success && data.commercant) {
            const c = data.commercant;
            document.querySelector('input[name=nom]').value = c.nom;
            document.querySelector('input[name=prenom]').value = c.prenom;
            document.querySelector('input[name=email]').value = c.email;
            document.querySelector('input[name=raison_sociale]').value = c.raison_sociale;
        } else {
            throw new Error('Commerçant introuvable');
        }
    } catch (err) {
        console.error(err);
        const msg = document.getElementById('message');
        msg.textContent = "Erreur de chargement.";
        msg.className = "text-red-600 mt-2";
    }
}

document.getElementById('form-edit').addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new URLSearchParams(new FormData(this));

    try {
        const res = await fetch(`${API_BASE}/admin/commercants/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData,
            credentials: 'include'
        });

        const result = await res.json();
        const msg = document.getElementById('message');
        msg.textContent = result.message;
        msg.className = result.success ? 'text-green-600 mt-2' : 'text-red-600 mt-2';
    } catch (err) {
        console.error(err);
        const msg = document.getElementById('message');
        msg.textContent = "Erreur lors de la mise à jour.";
        msg.className = "text-red-600 mt-2";
    }
});

chargerCommercant();
</script>

<?php include_once('includes/admin_footer.php'); ?>
