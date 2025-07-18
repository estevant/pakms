<?php
// resources/views/mes_contrats.php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?>

<div class="max-w-4xl mx-auto py-10 px-4">
  <h1 class="text-3xl font-bold text-[#212121] mb-6 text-center">
    📄 Mes contrats
  </h1>

  <div id="contracts-message" class="text-center mt-6 text-red-600 font-medium"></div>

  <!-- Contrats en cours -->
  <h2 class="text-2xl font-semibold mt-8 mb-4">Contrats en cours</h2>
  <div class="overflow-x-auto mb-8">
    <table id="current-contracts-table" class="min-w-full bg-white hidden">
      <thead>
        <tr>
          <th class="px-4 py-2 border">Début</th>
          <th class="px-4 py-2 border">Fin</th>
          <th class="px-4 py-2 border">Statut</th>
          <th class="px-4 py-2 border">Actions</th>
        </tr>
      </thead>
      <tbody id="current-contracts-tbody"></tbody>
    </table>
    <p id="no-current" class="text-gray-500 hidden">Vous n’avez aucun contrat en cours.</p>
  </div>

  <!-- Contrats expirés -->
  <h2 class="text-2xl font-semibold mt-8 mb-4">Contrats expirés</h2>
  <div class="overflow-x-auto mb-8">
    <table id="expired-contracts-table" class="min-w-full bg-white hidden">
      <thead>
        <tr>
          <th class="px-4 py-2 border">Début</th>
          <th class="px-4 py-2 border">Fin</th>
          <th class="px-4 py-2 border">Statut</th>
          <th class="px-4 py-2 border">Actions</th>
        </tr>
      </thead>
      <tbody id="expired-contracts-tbody"></tbody>
    </table>
    <p id="no-expired" class="text-gray-500 hidden">Vous n’avez aucun contrat expiré.</p>
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
  const msgEl    = document.getElementById('contracts-message');

  const curTable = document.getElementById('current-contracts-table');
  const curTbody = document.getElementById('current-contracts-tbody');
  const noCur    = document.getElementById('no-current');

  const expTable = document.getElementById('expired-contracts-table');
  const expTbody = document.getElementById('expired-contracts-tbody');
  const noExp    = document.getElementById('no-expired');

  try {
    const res    = await fetch(`${API_BASE}/contracts`, {
      method:      'GET',
      credentials: 'include',
      headers:     { 'Accept': 'application/json' }
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const result = await res.json();

    if (!result.success || !Array.isArray(result.contracts)) {
      throw new Error(result.message || 'Format inattendu');
    }

    // flag pour savoir si l'utilisateur a déjà une demande en cours
    const contracts       = result.contracts;
    const hasPendingReq   = contracts.some(c =>
      c.status === 'pending' || c.status === 'future'
    );

    const today = new Date();
    today.setHours(0,0,0,0);

    // séparer les contrats
    const current = contracts.filter(c => new Date(c.end_date) >= today);
    const expired = contracts.filter(c => new Date(c.end_date) < today);

    function fillRows(list, tbody, table, emptyMsg) {
      if (list.length === 0) {
        emptyMsg.classList.remove('hidden');
        return;
      }
      list.forEach(c => {
        const start   = c.start_date;
        const end     = c.end_date;
        const endDate = new Date(end);
        endDate.setHours(0,0,0,0);

        // calcule le nombre de jours restants
        const diffMs     = endDate - today;
        const daysRemain = Math.ceil(diffMs / (1000*60*60*24));

        // n'affiche le lien de renouvellement que si :
        // - on est dans les 30 derniers jours (0 ≤ daysRemain ≤ 30)
        // - ET qu'il n'existe pas déjà une demande (hasPendingReq)
        let requestLink = '';
        if (!hasPendingReq && daysRemain >= 0 && daysRemain <= 30) {
          requestLink = `
            <a href="create_contract.php?from=${encodeURIComponent(end)}"
               class="text-green-600 hover:underline ml-2">
              ➕ Renouveler le contrat
            </a>`;
        }

        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="px-4 py-2 border">${start}</td>
          <td class="px-4 py-2 border">${end}</td>
          <td class="px-4 py-2 border capitalize">${c.status}</td>
          <td class="px-4 py-2 border text-center">
            <a href="view_contract.php?id=${c.contract_id}"
               class="text-blue-600 hover:underline mr-2">🔍</a>
            <button onclick="deleteContract(${c.contract_id})"
                    class="text-red-600 hover:underline ml-2">🗑️</button>
            ${requestLink}
          </td>`;
        tbody.appendChild(tr);
      });
      table.classList.remove('hidden');
    }

    fillRows(current, curTbody, curTable, noCur);
    fillRows(expired, expTbody, expTable, noExp);

  } catch (err) {
    console.error(err);
    msgEl.textContent = "Impossible de charger vos contrats.";
  }
});

async function deleteContract(id) {
  if (!confirm("Confirmez-vous la suppression de ce contrat ?")) return;
  const API_BASE = "<?= BASE_API ?>";
  const msgEl    = document.getElementById('contracts-message');

  try {
    const res    = await fetch(`${API_BASE}/contracts/${id}`, {
      method:      'DELETE',
      credentials: 'include',
      headers:     {
        'Accept':       'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const result = await res.json();
    if (result.success) {
      window.location.reload();
    } else {
      msgEl.textContent = result.message || "Erreur lors de la suppression.";
    }
  } catch (err) {
    console.error(err);
    msgEl.textContent = "Erreur de connexion au serveur.";
  }
}
</script>

<?php include_once('../includes/footer.php'); ?>
