<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../front/includes/api_path.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin EcoDeli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
    body {
        font-family: 'Roboto', sans-serif;
    }

    .sidebar {
        min-height: calc(100vh - 4rem);
    }
    </style>
</head>

<body class="bg-[#F5F5F5]">
    <header class="bg-[#212121] shadow-md">
        <nav class="container mx-auto px-4 py-3 flex justify-between items-center">
            <span class="text-2xl font-bold text-[#4CAF50]">EcoDeli Admin</span>
            <a href="/PA2/Site/front/pages/logout.php"
                class="text-white hover:text-[#4CAF50] transition-colors">Déconnexion</a>
        </nav>
    </header>
    <div class="flex">
        <aside class="w-64 bg-[#1976D2] sidebar p-4">
            <nav class="space-y-2">
                <a href="dashboard_admin.php" class="block text-white p-2 hover:bg-[#1565c0] rounded">Dashboard</a>
                <a href="clients.php" class="block text-white p-2 hover:bg-[#1565c0] rounded">Clients</a>
                <a href="livreurs.php" class="block text-white p-2 hover:bg-[#1565c0] rounded">Livreurs</a>
                <a href="prestataires.php" class="block text-white p-2 hover:bg-[#1565c0] rounded">Prestataires</a>
                <a href="admin_types_prestations.php" class="block text-white p-2 hover:bg-[#1565c0] rounded">Validation types prestations</a>
                <a href="propositions_types_prestations.php" class="block text-white p-2 hover:bg-[#1565c0] rounded">Demandes d’ajout de prestations</a>
                <a href="commercants.php" class="block text-white p-2 hover:bg-[#1565c0] rounded">Commerçants</a>
                <a href="annonces.php" class="block text-white p-2 hover:bg-[#1565c0] rounded">Annonces</a>
                <a href="justificatifs.php" class="block text-white p-2 hover:bg-[#1565c0] rounded">Justificatifs</a>
                <a href="demandes_roles.php" class="block text-white p-2 hover:bg-[#1565c0] rounded">Demandes de rôles</a>
                <a href="contrats.php" class="block text-white p-2 hover:bg-[#1565c0] rounded">Contrats</a>
                <a href="reports.php" class="block text-white p-2 hover:bg-[#1565c0] rounded">Signalements</a>
                <a href="reviews.php" class="block text-white p-2 hover:bg-[#1565c0] rounded">Avis</a>
                <a href="admin_alerts.php" class="block text-white p-2 hover:bg-[#c62828] rounded font-bold">Alertes Modération</a>
                <a href="boxes.php" class="block text-white p-2 hover:bg-[#1565c0] rounded">Boxes de Stockage</a>
                <a href="ban_user.php" class="block text-white p-2 hover:bg-[#1565c0] rounded">Bannir un utilisateur</a>
            </nav>
        </aside>
        <main class="flex-1 p-8">