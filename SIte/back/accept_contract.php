<?php
include_once('includes/admin_header.php');
include_once('includes/api_path.php');
?>
<div class="max-w-4xl mx-auto py-10 px-4">
  <h1 class="text-3xl font-bold text-center mb-6">⚙️ Gestion des contrats en attente</h1>
  <div id="contracts-message" class="text-center mb-4 text-red-600"></div>

  
  <div class="overflow-x-auto">
    <table id="pending-table" class="min-w-full bg-white hidden">
      <thead>
        <tr>
          <th class="px-4 py-2 border">ID</th>
          <th class="px-4 py-2 border">Début</th>
          <th class="px-4 py-2 border">Fin</th>
          <th class="px-4 py-2 border">Actions</th>
        </tr>
      </thead>
      <tbody id="pending-body"></tbody>
    </table>
    <p id="no-pending" class="text-center text-gray-500 hidden">
      Il n'y a aucune demande de contrat en attente.
    </p>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {

  const API_BASE = "<?= rtrim(BASE_API, '/') ?>/admin/contracts";
  const msg      = document.getElementById('contracts-message');
  const tbl      = document.getElementById('pending-table');
  const body     = document.getElementById('pending-body');
  const none     = document.getElementById('no-pending');

  try {
    // 1) Charger les contrats pending
    const res  = await fetch(`${API_BASE}/pending`, {
      credentials: 'include',
      headers:     { 'Accept': 'application/json' }
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const json = await res.json();
    if (!json.success || !Array.isArray(json.contracts)) {
      throw new Error(json.message || 'Format inattendu');
    }

    // 2) Si aucun élément, afficher le message
    if (json.contracts.length === 0) {
      none.classList.remove('hidden');
      return;
    }

    // 3) Remplir la table
    json.contracts.forEach(c => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="px-4 py-2 border text-center">${c.contract_id}</td>
        <td class="px-4 py-2 border">${c.start_date}</td>
        <td class="px-4 py-2 border">${c.end_date}</td>
        <td class="px-4 py-2 border text-center">
          <button onclick="decision(${c.contract_id}, true)"
                  class="bg-green-600 text-white px-3 py-1 rounded mr-2">
            ✔️ Approuver
          </button>
          <button onclick="decision(${c.contract_id}, false)"
                  class="bg-red-600 text-white px-3 py-1 rounded">
            ❌ Refuser
          </button>
        </td>`;
      body.appendChild(tr);
    });
    tbl.classList.remove('hidden');

  } catch (err) {
    console.error(err);
    msg.textContent = "Impossible de récupérer les demandes.";
  }
});

async function decision(id, approve) {
  if (!confirm(approve
      ? "Approuver ce contrat ?"
      : "Refuser ce contrat ?")) return;

  const API_BASE = "<?= rtrim(BASE_API, '/') ?>/admin/contracts";
  const endpoint = approve ? 'approve' : 'reject';

  try {
    const res  = await fetch(`${API_BASE}/${id}/${endpoint}`, {
      method:      'POST',
      credentials: 'include',
      headers:     {
        'Accept':       'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    });
    const json = await res.json();
    if (res.ok && json.success) {
      alert(approve ? 'Contrat approuvé.' : 'Contrat refusé.');
      location.reload();
    } else {
      throw new Error(json.message || 'Échec de l\'opération');
    }
  } catch (err) {
    console.error(err);
    document.getElementById('contracts-message')
            .textContent = "Erreur lors de la décision.";
  }
}
</script>

<?php include_once('includes/admin_footer.php'); ?>
