<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?>

<h2 class="text-2xl font-bold mb-6">Mes factures</h2>

<table class="table-auto w-full border-collapse border border-gray-300">
    <thead class="bg-gray-100">
        <tr>
            <th class="border px-4 py-2">N° facture</th>
            <th class="border px-4 py-2">Date</th>
            <th class="border px-4 py-2">Montant (€)</th>
            <th class="border px-4 py-2">Action</th>
        </tr>
    </thead>
    <tbody id="facture-body">
        <tr><td colspan="4" class="text-center py-4">Chargement...</td></tr>
    </tbody>
</table>

<script>
const API_BASE = "<?= BASE_API ?>";

async function loadFactures() {
    try {
        const res = await fetch(`${API_BASE}/prestataire/factures`, {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });

        const factures = await res.json();
        const body = document.getElementById('facture-body');
        body.innerHTML = "";

        if (!Array.isArray(factures) || factures.length === 0) {
            body.innerHTML = "<tr><td colspan='4' class='text-center py-4'>Aucune facture disponible.</td></tr>";
            return;
        }

        factures.forEach(f => {
            const row = `
                <tr>
                    <td class="border px-4 py-2">${f.invoice_number || '—'}</td>
                    <td class="border px-4 py-2">${f.issue_date ? f.issue_date.split(' ')[0] : '—'}</td>
                    <td class="border px-4 py-2">${f.total_amount ? (Number(f.total_amount) / 100).toFixed(2) : '—'} €</td>
                    <td class="border px-4 py-2 text-center">
                        ${f.pdf_path ? `<a href="${API_BASE}/prestataire/facture/${f.invoice_id}" target="_blank" class="text-blue-600 underline">Télécharger</a>` : '—'}
                    </td>
                </tr>`;
            body.innerHTML += row;
        });

    } catch (err) {
        console.error(err);
        document.getElementById('facture-body').innerHTML = "<tr><td colspan='4' class='text-center text-red-600'>Erreur de chargement.</td></tr>";
    }
}

loadFactures();
</script>

<?php include_once('../includes/footer.php'); ?>
