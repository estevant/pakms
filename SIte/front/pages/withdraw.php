<?php include_once('../includes/header.php'); ?>
<?php include_once('../includes/api_path.php'); ?>

<div class="max-w-2xl mx-auto py-10 px-4">
  <h1 class="text-3xl font-bold text-[#212121] mb-6 text-center">💼 Mon portefeuille</h1>

  <div id="wallet-balance" class="text-center text-xl font-semibold mb-4">Chargement...</div>

  <form id="withdraw-form" class="bg-white shadow p-4 rounded mb-8">
    <label class="block font-medium mb-2">Montant à retirer (€)</label>
    <input type="number" min="1" step="0.01" required
           class="w-full border px-3 py-2 rounded mb-4" id="withdraw-amount" />
    <button type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full">
      💸 Demander un retrait
    </button>
    <div id="withdraw-msg" class="mt-4 text-center font-medium"></div>
  </form>

  <h2 class="text-xl font-bold mb-2">📜 Mes retraits</h2>
  <table id="withdrawals-table" class="min-w-full bg-white border hidden">
    <thead>
      <tr>
        <th class="border px-4 py-2">Montant</th>
        <th class="border px-4 py-2">Statut</th>
        <th class="border px-4 py-2">Date</th>
      </tr>
    </thead>
    <tbody id="withdrawals-body"></tbody>
  </table>
  <p id="no-withdrawals" class="text-gray-500 hidden">Aucun retrait effectué.</p>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";

async function loadWallet() {
  try {
    const res = await fetch(`${API_BASE}/wallet`, { credentials: 'include' });
    const data = await res.json();
    document.getElementById('wallet-balance').textContent =
      `Solde disponible : ${(data.balance_cent / 100).toFixed(2)} €`;
  } catch (e) {
    document.getElementById('wallet-balance').textContent = "Erreur de chargement";
  }
}

async function loadWithdrawals() {
  try {
    const res = await fetch(`${API_BASE}/wallet/withdrawals`, { credentials: 'include' });
    const data = await res.json();

    const table = document.getElementById('withdrawals-table');
    const tbody = document.getElementById('withdrawals-body');
    const noData = document.getElementById('no-withdrawals');

    if (!data.success || !data.withdrawal.length) {
      noData.classList.remove('hidden');
      return;
    }

    data.withdrawal.forEach(w => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="border px-4 py-2">${(w.amount_cent / 100).toFixed(2)} €</td>
        <td class="border px-4 py-2">${w.status}</td>
        <td class="border px-4 py-2">${w.created_at}</td>
      `;
      tbody.appendChild(tr);
    });

    table.classList.remove('hidden');
  } catch (e) {
    console.error("Erreur chargement retraits", e);
  }
}

document.getElementById('withdraw-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const amount = parseFloat(document.getElementById('withdraw-amount').value || 0);
  const msg = document.getElementById('withdraw-msg');
  msg.textContent = "Traitement...";

  try {
    const res = await fetch(`${API_BASE}/wallet/withdraw`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ amount_cent: Math.round(amount * 100) })
    });

    const data = await res.json();
    msg.textContent = data.message || "Erreur";
    if (data.success) {
      msg.classList.remove('text-red-600');
      msg.classList.add('text-green-600');
      await loadWallet();
      location.reload();
    } else {
      msg.classList.remove('text-green-600');
      msg.classList.add('text-red-600');
    }
  } catch (e) {
    msg.textContent = "Erreur lors de la demande";
  }
});

loadWallet();
loadWithdrawals();
</script>

<?php include_once('../includes/footer.php'); ?>