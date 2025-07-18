<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');

$deliverer_id = intval($_GET['deliverer_id'] ?? 0);
$request_id   = intval($_GET['request_id']   ?? 0);

if (!$deliverer_id || !$request_id) {
  echo "<p class='text-red-600 text-center mt-12'>
            ID de livreur ou ID de la requête manquant.
          </p>";
  include_once('../includes/footer.php');
  exit;
}
?> <div class="max-w-md mx-auto py-10 px-4">
    <h1 class="text-2xl font-bold mb-6 text-center">⭐ Noter votre livreur</h1>
    <div id="feedback-message" class="text-center mb-4 text-sm"></div>
    <form id="review-form" class="space-y-4">
        <div>
            <label class="block font-semibold mb-1">Votre note :</label>
            <div id="star-container" class="flex space-x-1 text-3xl cursor-pointer">
                <?php for ($i = 1; $i <= 5; $i++): ?> <span data-value="<?= $i ?>"
                    class="star text-gray-300">&#9733;</span> <?php endfor; ?> </div>
            <input type="hidden" id="rating" name="rating" value="0">
        </div>
        <div>
            <label for="comment" class="block font-semibold mb-1">Votre avis (optionnel) :</label>
            <textarea id="comment" name="comment" rows="4" class="border border-gray-300 rounded w-full px-3 py-2"
                placeholder="Un petit mot…"></textarea>
        </div>
        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700"> Envoyer ma note
        </button>
    </form>
</div>
<script>
(() => {
    const API_BASE = "<?= BASE_API ?>";
    const delivererId = <?= $deliverer_id ?>;
    const requestId = <?= $request_id ?>;
    const stars = document.querySelectorAll('.star');
    const ratingInput = document.getElementById('rating');
    const feedback = document.getElementById('feedback-message');
    stars.forEach(s => {
        s.addEventListener('mouseenter', e => highlightStars(e.target.dataset.value));
        s.addEventListener('mouseleave', () => highlightStars(ratingInput.value));
        s.addEventListener('click', e => {
            ratingInput.value = e.target.dataset.value;
            highlightStars(ratingInput.value);
        });
    });

    function highlightStars(count) {
        stars.forEach(s => {
            s.classList.toggle('text-yellow-400', s.dataset.value <= count);
            s.classList.toggle('text-gray-300', s.dataset.value > count);
        });
    }
    document.getElementById('review-form').addEventListener('submit', async e => {
        e.preventDefault();
        const rating = parseInt(ratingInput.value, 10);
        const comment = document.getElementById('comment').value.trim();
        if (!rating || rating < 1 || rating > 5) {
            feedback.textContent = "Merci de sélectionner une note entre 1 et 5 étoiles.";
            feedback.className = "text-red-600 mb-4";
            return;
        }
        try {
            const res = await fetch(`${API_BASE}/orders/${requestId}/reviews`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    deliverer_id: delivererId,
                    request_id: requestId,
                    rating: rating,
                    comment: comment
                })
            });
            const json = await res.json();
            if (json.success) {
                feedback.textContent = "Merci pour votre note !";
                feedback.className = "text-green-600 mb-4";
                document.querySelectorAll(
                    '#review-form input, #review-form textarea, #review-form button').forEach(el =>
                    el.disabled = true);
            } else {
                feedback.textContent = json.message || "Erreur lors de l'envoi.";
                feedback.className = "text-red-600 mb-4";
            }
        } catch (err) {
            console.error(err);
            feedback.textContent = "Erreur réseau, veuillez réessayer.";
            feedback.className = "text-red-600 mb-4";
        }
    });
})();
</script> <?php include_once('../includes/footer.php'); ?>