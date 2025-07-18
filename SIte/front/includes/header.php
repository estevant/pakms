<?php include_once('api_path.php'); ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_COOKIE['XSRF-TOKEN'] ?? '' ?>">
    <title>EcoDeli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
    </style>
    <meta name="csrf-token" content="<?= $_COOKIE['XSRF-TOKEN'] ?? '' ?>">
</head>

<body class="bg-[#F5F5F5]">
    <header class="bg-[#1976D2] shadow-md">
        <nav class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="../pages/dashboard.php" class="flex items-center space-x-2">
                <img src="../includes/logo.png" alt="Logo EcoDeli" class="w-8 h-8 object-contain">
                <span class="text-2xl font-bold text-[#4CAF50]">EcoDeli</span>
            </a>
            <div class="flex items-center space-x-6" id="nav-auth">
                <a href="../pages/login.php" class="text-white hover:text-[#4CAF50] transition-colors">Connexion</a>
                <a href="../pages/register.php"
                    class="text-white hover:text-[#4CAF50] transition-colors">Inscription</a>
            </div>
        </nav>
    </header>
    <main class="container mx-auto px-4 py-6">
        <script>
            document.addEventListener('DOMContentLoaded', async () => {
                const API_BASE = "<?= BASE_API ?>";
                const APP_BASE = API_BASE.replace(/\/api\/?$/, '');
                const nav = document.getElementById('nav-auth');
                try {
                    const res = await fetch(`${API_BASE}/get_session?ts=${Date.now()}`, {
                        credentials: 'include'
                    });
                    const {
                        user,
                        error
                    } = await res.json();
                    
                    // Vérifier si l'utilisateur est banni
                    if (error && error.includes('banni')) {
                        // Afficher la notification de bannissement
                        showBanNotification(error);
                        // Rediriger vers la page de connexion après 3 secondes
                        setTimeout(() => {
                            window.location.href = '../pages/login.php';
                        }, 3000);
                        return;
                    }
                    
                    if (user && user.user_id) {
                        // Vérifier si l'utilisateur est banni dans les données de session
                        if (user.banned) {
                            showBanNotification('Votre compte a été banni. Vous avez été déconnecté.');
                            setTimeout(() => {
                                window.location.href = '../pages/login.php';
                            }, 3000);
                            return;
                        }
                        
                        const avatarUrl = user.profile_picture ?
                            `${APP_BASE}/storage/${user.profile_picture}?v=${Date.now()}` :
                            `https://ui-avatars.com/api/?name=${encodeURIComponent(user.first_name+' '+user.last_name)}&background=4CAF50&color=fff&size=64`;
                        nav.innerHTML = `
                <a href="../pages/notifications.php"
                   class="relative text-white hover:text-[#4CAF50] transition-colors">
                    Notifications
                    <span id="notif-badge"
                          class="absolute -top-2 -right-2 bg-red-600 text-white
                                 text-xs rounded-full px-1 hidden">0</span>
                </a>

                <a href="../pages/profile.php"
                   class="flex items-center space-x-2 group">
                    <img src="${avatarUrl}"
                         alt="Profil"
                         class="w-8 h-8 rounded-full border-2 border-white
                                object-cover group-hover:border-[#4CAF50]">
                    <span class="hidden sm:inline text-white group-hover:text-[#4CAF50]">
                        ${user.first_name}
                    </span>
                </a>

                <a href="../pages/logout.php"
                   class="text-white hover:text-[#4CAF50] transition-colors">
                   Déconnexion
                </a>
            `;
                        verifierNotifications();
                    } else {
                        nav.innerHTML = `
                <a href="../pages/login.php"
                   class="text-white hover:text-[#4CAF50] transition-colors">
                   Connexion
                </a>
                <a href="../pages/register.php"
                   class="text-white hover:text-[#4CAF50] transition-colors">
                   Inscription
                </a>
            `;
                    }
                } catch (err) {
                    console.error('Erreur session :', err);
                }
            });
            
            // Fonction pour afficher la notification de bannissement
            function showBanNotification(message) {
                // Créer la notification
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-red-600 text-white px-6 py-4 rounded-lg shadow-lg z-50 max-w-md';
                notification.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <div>
                            <h3 class="font-semibold">Compte banni</h3>
                            <p class="text-sm">${message}</p>
                        </div>
                    </div>
                `;
                
                // Ajouter au body
                document.body.appendChild(notification);
                
                // Supprimer après 5 secondes
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 5000);
            }
            
            // Fonction utilitaire pour gérer les erreurs de bannissement dans les appels API
            window.handleApiResponse = async function(response) {
                if (response.status === 403) {
                    const data = await response.json();
                    if (data.error && data.error.includes('banni')) {
                        showBanNotification(data.error);
                        setTimeout(() => {
                            window.location.href = '../pages/login.php';
                        }, 3000);
                        return { banned: true };
                    }
                }
                return { banned: false, response };
            };
            
            async function verifierNotifications() {
                const API_BASE = "<?= BASE_API ?>";
                try {
                    const res = await fetch(`${API_BASE}/notifications`, {
                        credentials: 'include',
                        headers: {
                            Accept: 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (!data.success) return;
                    const nonLues = data.notifications.filter(n => !n.is_read).length;
                    const badge = document.getElementById('notif-badge');
                    badge.textContent = nonLues;
                    badge.classList.toggle('hidden', nonLues === 0);
                } catch (e) {
                    console.error('Erreur notifications :', e);
                }
            }
        </script>