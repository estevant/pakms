<?php require_once('includes/admin_header.php'); ?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-8">Tableau de bord administrateur</h1>

    <div id="stats" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"></div>
</div>

<script>
const BASE_API = "<?= BASE_API ?>";

async function chargerStats() {
    try {
        const res = await fetch(`${BASE_API}/admin/stats`, { credentials: 'include' });
        const data = await res.json();

        const stats = [
            { label: "Clients",             count: data.clients              ?? 0, link: "clients.php",      bg: "bg-blue-50",      text: "text-blue-800"      },
            { label: "Livreurs",            count: data.livreurs             ?? 0, link: "livreurs.php",     bg: "bg-yellow-50",    text: "text-yellow-800"    },
            { label: "Prestataires",        count: data.prestataires         ?? 0, link: "prestataires.php", bg: "bg-green-50",     text: "text-green-800"     },
            { label: "Commerçants",         count: data.commercants          ?? 0, link: "commercants.php",  bg: "bg-orange-50",    text: "text-orange-800"    },
            { label: "Annonces",            count: data.annonces             ?? 0, link: "annonces.php",     bg: "bg-purple-50",    text: "text-purple-800"    },
            { label: "Justificatifs validés", count: data.justificatifs_valides ?? 0, link: "justificatifs.php", bg: "bg-indigo-50",    text: "text-indigo-800"    },
            { label: "Justificatifs en attente", count: data.justificatifs_en_attente ?? 0, link: "justificatifs.php", bg: "bg-cyan-50", text: "text-cyan-800" },
            { label: "Contrats",            count: data.contrats             ?? 0, link: "contrats.php",     bg: "bg-teal-50",      text: "text-teal-800"      },
            { label: "Contrats en attente", count: data.contrats_en_attente  ?? 0, link: "contrats.php",     bg: "bg-pink-50",      text: "text-pink-800"      },
            { label: "Signalements",        count: data.signalements         ?? 0, link: "reports.php",      bg: "bg-red-50",       text: "text-red-800"       },
            { label: "Avis",                count: data.avis                 ?? 0, link: "reviews.php",      bg: "bg-emerald-50",   text: "text-emerald-800"   },
            { label: "Boxes de Stockage",   count: data.boxes                ?? 0, link: "boxes.php",        bg: "bg-amber-50",     text: "text-amber-800"     },
            { label: "Utilisateurs bannis", count: data.bannis               ?? 0, link: "ban_user.php",     bg: "bg-gray-100",     text: "text-gray-800"     },
            { label: "Finances", count: data.finances ? (data.finances / 100).toFixed(2) + ' €' : '0.00 €', link: "finances.php", bg: "bg-gray-100", text: "text-gray-800" }
        ];

        const container = document.getElementById('stats');
        container.innerHTML = stats.map(s => `
            <a href="${s.link}" class="block p-6 rounded-lg shadow-md hover:shadow-lg transition-colors ${s.bg} hover:${s.bg.replace('-50','-100')}">
                <h2 class="text-xl font-semibold ${s.text}">${s.label}</h2>
                <p class="text-4xl mt-3 font-bold ${s.text}">${s.count}</p>
            </a>
        `).join('');
    } catch (e) {
        console.error(e);
        document.getElementById('stats').innerHTML = `
            <p class="col-span-full text-red-600 text-center py-8">
                Erreur de chargement des statistiques.
            </p>
        `;
    }
}

chargerStats();
</script>

<?php require_once('includes/admin_footer.php'); ?>
