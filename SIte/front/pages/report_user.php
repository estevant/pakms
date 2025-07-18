<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
if (!isset($_GET['user_id'])) {
    echo "<p class='text-red-600 text-center mt-12'>ID d’utilisateur manquant.</p>";
    include_once('../includes/footer.php');
    exit;
}
$userId = intval($_GET['user_id']);
?>
<div class="max-w-md mx-auto p-6">
  <h2 class="text-xl font-bold mb-4">🚩 Signaler l’utilisateur #<?= $userId ?></h2>
  <select id="reason" class="input-field mb-3">
    <option value="">Raison…</option>
    <option value="Harcèlement">Harcèlement</option>
    <option value="Spam">Spam</option>
    <option value="Comportement inapproprié">Comportement inapproprié</option>
    <option value="Autre">Autre</option>
  </select>
  <textarea id="desc" class="input-field mb-3" rows="4" placeholder="Détails (facultatif)"></textarea>
  <button id="btn-send" class="bg-red-600 text-white px-4 py-2 rounded">Envoyer le signalement</button>
  <p id="msg" class="mt-3 text-sm font-medium"></p>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";
const targetId = <?= $userId ?>;

document.getElementById('btn-send').onclick = async () => {
  const reason = document.getElementById('reason').value.trim();
  const desc   = document.getElementById('desc').value.trim();
  const msgEl  = document.getElementById('msg');

  if (!reason) {
    alert('Veuillez choisir une raison.');
    return;
  }

  try {
    const res = await fetch(`${API_BASE}/report`, {
      method:      'POST',
      credentials: 'include',
      headers: {
        'Content-Type':'application/json',
        'Accept':'application/json'
      },
      body: JSON.stringify({
        target_type: 'user',
        target_id:   targetId,
        reason,
        description: desc
      })
    });

    const data = await res.json();

    if (!res.ok) {
      throw new Error(data.message || `Erreur ${res.status}`);
    }

    msgEl.textContent = data.message || 'Signalement envoyé';
    msgEl.className   = 'text-green-600';

  } catch (err) {
    console.error("Erreur lors de l’envoi du signalement :", err);
    msgEl.textContent = err.message || "Erreur réseau lors de l’envoi";
    msgEl.className   = 'text-red-600';
  }
};
</script>

<?php include_once('../includes/footer.php'); ?>
