<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role_1'] === 'admin' || $_SESSION['role_2'] === 'admin') {
        header("Location: /PA2/SIte/back/dashboard_admin.php");
        exit;
    }
    header("Location: pages/dashboard.php");
    exit;
}
include_once '../includes/header.php';
?>

<style>
    body {
        font-family: 'Montserrat', sans-serif;
    }
    h1, h2, h3 {
        font-family: 'Poppins', sans-serif;
    }
</style>

<section class="bg-[#F5F5F5] min-h-screen flex flex-col items-center text-center px-6 py-12">
    <img src="../includes/logo.png" alt="Logo EcoDeli" class="w-40 md:w-48 mb-8 drop-shadow-lg hover:scale-105 transition-transform duration-300 object-contain">

    <h1 class="text-4xl md:text-5xl font-bold text-[#212121] mb-4">Bienvenue sur <span class="text-[#4CAF50]">EcoDeli</span></h1>
    
    <p class="text-[#212121] max-w-2xl mb-8 text-lg">
        La plateforme de livraison éco-responsable pour tous vos besoins !<br>
        Faites livrer, transportez, ou proposez vos services simplement.
    </p>

    <div class="flex flex-col md:flex-row gap-4 mb-12">
        <a href="register.php" class="bg-[#4CAF50] hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium">
            🚀 Créer un compte
        </a>
        <a href="login.php" class="bg-[#1976D2] hover:bg-blue-800 text-white px-6 py-3 rounded-lg font-medium">
            🔐 Se connecter
        </a>
    </div>

    <div class="text-left max-w-3xl text-[#212121] bg-white rounded-lg shadow px-6 py-6">
        <h2 class="text-2xl font-semibold mb-3 text-[#1976D2]">💡 Comment ça marche ?</h2>
        <ul class="list-disc pl-5 space-y-2 text-md">
            <li>Inscrivez-vous en tant que client, livreur, commerçant ou prestataire</li>
            <li>Créez ou acceptez des annonces de livraison</li>
            <li>Collaborez, discutez, échangez les justificatifs</li>
            <li>Paiements sécurisés via votre espace EcoDeli</li>
        </ul>
    </div>
</section>

<?php include_once '../includes/footer.php'; ?>
