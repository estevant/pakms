<?php
session_start();
include_once('../includes/header.php');
?>

<div class="max-w-xl mx-auto mt-10 text-center p-6 border border-yellow-400 bg-yellow-100 rounded shadow">
    <h1 class="text-2xl font-bold text-yellow-700 mb-4">Compte en attente de validation</h1>
    <p class="text-gray-700 mb-4">
        Merci pour votre inscription sur EcoDeli. Votre compte est actuellement en cours de vérification par notre équipe.
    </p>
    <p class="text-gray-700 mb-4">
        Vous recevrez une notification par email dès que votre compte aura été validé.
    </p>
    <p class="text-sm text-gray-500 mb-4">Si vous avez des questions, vous pouvez nous contacter via la page de contact ou consulter la <a href="#" class="underline text-blue-600">FAQ</a>.</p>
    <a href="index.php" class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Retour à l'accueil</a>
</div>

<?php include_once('../includes/footer.php'); ?>
