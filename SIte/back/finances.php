<?php include_once('includes/admin_header.php'); 
include_once('../front/includes/api_path.php');?>

<div class="container mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Finances Générales</h1>

  <!-- Chiffre d'affaires -->
  <div class="bg-white shadow rounded-lg p-6 mb-10">
    <h2 class="text-xl font-semibold mb-4"> Chiffre d'affaires total</h2>
    <p id="ca-total" class="text-2xl font-bold text-green-600">Chargement...</p>
  </div>

  <!-- Encaissements -->
  <div class="bg-white shadow rounded-lg p-6 mb-10">
    <h2 class="text-xl font-semibold mb-4"> Paiements reçus</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-left border" id="encaissements-table">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-4 py-2 border">Date</th>
            <th class="px-4 py-2 border">Montant</th>
            <th class="px-4 py-2 border">Type</th>
            <th class="px-4 py-2 border">Destinataire</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <!-- Retraits -->
  <div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-semibold mb-4"> Demandes de retrait</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-left border" id="retraits-table">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-4 py-2 border">Date</th>
            <th class="px-4 py-2 border">Montant</th>
            <th class="px-4 py-2 border">Utilisateur</th>
            <th class="px-4 py-2 border">Statut</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
  const API_BASE = 'http://localhost/PA2/api-ecodeli/public/api/admin';

  // Chiffre d'affaires total
  const caRes = await fetch(`${API_BASE}/ca-total`, { credentials: 'include' });
  const caData = await caRes.json();
  if (caData.success) {
    document.getElementById('ca-total').textContent = `${caData.ca_total_euros.toFixed(2)} €`;
  }

  // Encaissements
  const encaRes = await fetch(`${API_BASE}/encaissements`, { credentials: 'include' });
  const encaData = await encaRes.json();
  if (encaData.success) {
    const tbody = document.querySelector('#encaissements-table tbody');
    encaData.encaissements.forEach(e => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="border px-4 py-2">${e.payment_date}</td>
        <td class="border px-4 py-2">${(e.amount / 100).toFixed(2)} €</td>
        <td class="border px-4 py-2">${e.payment_type}</td>
        <td class="border px-4 py-2">${e.full_name}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  // Retraits
  const retRes = await fetch(`${API_BASE}/retraits`, { credentials: 'include' });
  const retData = await retRes.json();
  if (retData.success) {
    const tbody = document.querySelector('#retraits-table tbody');
    retData.retraits.forEach(r => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="border px-4 py-2">${r.created_at}</td>
        <td class="border px-4 py-2">${(r.amount_cent / 100).toFixed(2)} €</td>
        <td class="border px-4 py-2">${r.full_name}</td>
        <td class="border px-4 py-2">${r.status}</td>
      `;
      tbody.appendChild(tr);
    });
  }
});
</script>

<?php include_once('includes/admin_footer.php'); ?>
