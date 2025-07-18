<?php
// resources/views/create_contract.php
include_once('../includes/header.php');
include_once('../includes/api_path.php');

$today   = date('Y-m-d');
$prefill = $_GET['from'] ?? $today;
?>

<div class="max-w-2xl mx-auto py-10 px-4">
  <h1 class="text-2xl font-bold mb-6 text-center">➕ Demande de contrat</h1>
  <div id="contract-message" class="text-center mb-4 text-red-600"></div>

  <form id="contract-form" enctype="multipart/form-data" class="space-y-4">
    <label class="block">
      Date de début :
      <input
        type="date"
        id="start_date"
        value="<?= htmlspecialchars($prefill) ?>"
        min="<?= $today ?>"
        required
        class="input-field"
      />
    </label>

    <label class="block">
      Date de fin :
      <input
        type="date"
        id="end_date"
        required
        class="input-field"
      />
    </label>

    <label class="block">
      Conditions (optionnel) :
      <textarea id="terms" class="input-field" rows="4"></textarea>
    </label>

    <label class="block">
      PDF (optionnel) :
      <input type="file" id="pdf" accept="application/pdf" class="mt-1" />
    </label>

    <div class="text-center">
      <button
        type="button"
        onclick="submitContract()"
        class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700"
      >
        Envoyer la demande
      </button>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const startEl = document.getElementById('start_date');
  const endEl   = document.getElementById('end_date');

  // 1) Initialize end_date.min to today or prefill
  const today = new Date().toISOString().split('T')[0];
  endEl.min = startEl.value || today;

  // 2) Whenever start_date changes, bump end_date.min up
  startEl.addEventListener('change', () => {
    endEl.min = startEl.value;
    // if the current end < new min, clear it
    if (endEl.value && endEl.value < endEl.min) {
      endEl.value = '';
    }
  });
});

async function submitContract() {
  const msgEl = document.getElementById('contract-message');
  msgEl.textContent = '';

  const start = document.getElementById('start_date').value;
  const end   = document.getElementById('end_date').value;
  const terms = document.getElementById('terms').value;
  const pdf   = document.getElementById('pdf').files[0];

  if (!start || !end) {
    msgEl.textContent = 'Veuillez renseigner les dates de début et de fin.';
    return;
  }

  const formData = new FormData();
  formData.append('start_date', start);
  formData.append('end_date',   end);
  formData.append('terms',      terms);
  if (pdf) formData.append('pdf', pdf);

  try {
    const res = await fetch(`<?= BASE_API ?>/contracts`, {
      method:      'POST',
      credentials: 'include',
      body:        formData,
      headers:     { 
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
      }
    });
    const json = await res.json();
    if (res.ok && json.success) {
      alert('Demande envoyée !');
      window.location.href = 'mes_contrats.php';
    } else {
      msgEl.textContent = json.message || 'Erreur lors de la création.';
    }
  } catch (err) {
    console.error(err);
    msgEl.textContent = 'Impossible de contacter le serveur.';
  }
}
</script>

<?php include_once('../includes/footer.php'); ?>
