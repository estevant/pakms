<?php
// resources/views/view_contract.php
include_once('../includes/header.php');
include_once('../includes/api_path.php');

$id = $_GET['id'] ?? null;
if (! $id) {
    echo "<p class='text-center text-red-600'>ID de contrat manquant.</p>";
    include_once('../includes/footer.php');
    exit;
}
?>

<div class="max-w-2xl mx-auto py-10 px-4">
  <h1 class="text-2xl font-bold mb-6 text-center">🔍 Contrat #<?= htmlspecialchars($id) ?></h1>
  <div id="contract-view" class="prose mx-auto"></div>
  <div id="view-message" class="text-center mt-4 text-red-600"></div>
  <div class="mt-8 text-center">
    <a href="mes_contrats.php" class="text-blue-600 hover:underline">⬅️ Retour aux contrats</a>
  </div>
</div>

<script>
;(async () => {
  const container = document.getElementById('contract-view');
  const msgEl     = document.getElementById('view-message');

  try {
    const res  = await fetch(`<?= BASE_API ?>/contracts/<?= $id ?>`, {
      method:      'GET',
      credentials: 'include',
      headers:     { 'Accept': 'application/json' }
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Erreur API');
    const c = json.contract;

    // Construction du HTML
    let html = `
      <p><strong>Début :</strong> ${c.start_date}</p>
      <p><strong>Fin :</strong> ${c.end_date}</p>
      <p><strong>Statut :</strong> ${c.status}</p>
      <p><strong>Conditions :</strong><br/>${c.terms || '<em>Aucune</em>'}</p>
    `;

    // Si on a un PDF, on génère le lien via FRONT_BASE
    if (c.pdf_path) {
      const pdfUrl = `<?= FRONT_BASE ?>/storage/${c.pdf_path}`;
      html += `
        <p>
          <a href="${pdfUrl}"
             target="_blank"
             class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 inline-block mt-4">
            📄 Télécharger le PDF
          </a>
        </p>
      `;
    }

    container.innerHTML = html;

  } catch (err) {
    console.error(err);
    msgEl.textContent = "Impossible de charger le contrat.";
  }
})();
</script>

<?php include_once('../includes/footer.php'); ?>
