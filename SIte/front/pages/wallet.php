<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?>

<div class="max-w-4xl mx-auto py-10 px-4">
  <h1 class="text-3xl font-bold text-[#212121] mb-6 text-center">
    💼 Mon portefeuille
  </h1>

  <div id="wallet-balance" class="text-center text-xl font-semibold text-green-600 mb-6">Chargement du solde...</div>
  <div class="text-center mb-8">
  <a href="withdraw.php" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded shadow inline-block">Retirer</a>
</div>


  <h2 class="text-xl font-bold text-[#212121] mb-4">💰 Paiements reçus</h2>

  <div id="payments-message" class="text-center mt-6 text-red-600 font-medium"></div>

  <div class="overflow-x-auto mb-8">
    <table id="payments-table" class="min-w-full bg-white hidden">
      <thead>
        <tr>
          <th class="px-4 py-2 border">Date</th>
          <th class="px-4 py-2 border">Montant</th>
          <th class="px-4 py-2 border">Type</th>
        </tr>
      </thead>
      <tbody id="payments-tbody"></tbody>
    </table>
    <p id="no-payments" class="text-gray-500 hidden">Aucun paiement reçu pour le moment.</p>
  </div>

  <div class="mt-8 text-center">
    <a href="dashboard.php"
       class="bg-gray-300 hover:bg-gray-400 text-[#212121] font-medium px-5 py-2 rounded shadow">
      ⬅️ Retour
    </a>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
  const API_BASE = "<?= BASE_API ?>";

  const walletBalance = document.getElementById('wallet-balance');
  const msgEl = document.getElementById('payments-message');
  const table = document.getElementById('payments-table');
  const tbody = document.getElementById('payments-tbody');
  const noMsg = document.getElementById('no-payments');

  // Charger le solde
  try {
    const resWallet = await fetch(`${API_BASE}/wallet`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    });

    const walletData = await resWallet.json();
    if (walletData.success) {
      walletBalance.textContent = `Solde actuel : ${(walletData.balance_cent / 100).toFixed(2)} €`;
    } else {
      walletBalance.textContent = "Erreur de chargement du solde.";
    }
  } catch (err) {
    console.error(err);
    walletBalance.textContent = "Erreur lors du chargement du portefeuille.";
  }

  // Charger les paiements reçus
  try {
    const resPayments = await fetch(`${API_BASE}/wallet/received`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    });

    const paymentData = await resPayments.json();
    if (!paymentData.success || !Array.isArray(paymentData.payments)) {
      throw new Error(paymentData.message || 'Format inattendu');
    }

    if (paymentData.payments.length === 0) {
      noMsg.classList.remove('hidden');
      return;
    }

    paymentData.payments.forEach(p => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="px-4 py-2 border">${p.payment_date}</td>
        <td class="px-4 py-2 border">${(p.amount / 100).toFixed(2)} €</td>
        <td class="px-4 py-2 border">${p.payment_type}</td>
      `;
      tbody.appendChild(tr);
    });

    table.classList.remove('hidden');
  } catch (err) {
    console.error(err);
    msgEl.textContent = "Impossible de charger les paiements.";
  }
});
</script>

<?php include_once('../includes/footer.php'); ?>
