<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?>

<div class="max-w-4xl mx-auto py-10 px-4">
  <h1 class="text-3xl font-bold text-[#212121] mb-6 text-center">
    📄 Mes factures
  </h1>

  <div id="invoices-message" class="text-center mt-6 text-red-600 font-medium"></div>

  <div class="overflow-x-auto mb-8">
    <table id="invoices-table" class="min-w-full bg-white hidden">
      <thead>
        <tr>
          <th class="px-4 py-2 border">Numéro</th>
          <th class="px-4 py-2 border">Date</th>
          <th class="px-4 py-2 border">Montant</th>
          <th class="px-4 py-2 border">Actions</th>
        </tr>
      </thead>
      <tbody id="invoices-tbody"></tbody>
    </table>
    <p id="no-invoices" class="text-gray-500 hidden">Vous n'avez encore aucune facture.</p>
  </div>

  <div class="mt-8 text-center">
    <a href="dashboard.php"
       class="bg-gray-300 hover:bg-gray-400 text-[#212121] font-medium
              px-5 py-2 rounded shadow">
      ⬅️ Retour
    </a>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
  const API_BASE = "<?= BASE_API ?>";
  const msgEl    = document.getElementById('invoices-message');
  const table    = document.getElementById('invoices-table');
  const tbody    = document.getElementById('invoices-tbody');
  const noMsg    = document.getElementById('no-invoices');

  try {
    const res = await fetch(`${API_BASE}/invoices`, {
      method: 'GET',
      credentials: 'include',
      headers: {
        'Accept': 'application/json'
      }
    });

    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const result = await res.json();

    if (!result.success || !Array.isArray(result.invoices)) {
      throw new Error(result.message || 'Format inattendu');
    }

    const invoices = result.invoices;

    if (invoices.length === 0) {
      noMsg.classList.remove('hidden');
      return;
    }

    invoices.forEach(inv => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="px-4 py-2 border">${inv.invoice_number}</td>
        <td class="px-4 py-2 border">${inv.issue_date}</td>
        <td class="px-4 py-2 border">${(inv.total_amount / 100).toFixed(2)} €</td>
        <td class="px-4 py-2 border text-center">
          <a href="view_invoice.php?id=${inv.invoice_id}"
             class="text-blue-600 hover:underline mr-2">🔍 Voir</a>
          <a href="<?= BASE_API ?>/invoices/download/${inv.invoice_id}"
             class="text-green-600 hover:underline ml-2" target="_blank">⬇️ Télécharger</a>
        </td>
      `;
      tbody.appendChild(tr);
    });

    table.classList.remove('hidden');

  } catch (err) {
    console.error(err);
    msgEl.textContent = "Impossible de charger vos factures.";
  }
});
</script>

<?php include_once('../includes/footer.php'); ?>
