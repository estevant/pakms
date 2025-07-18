<?php require_once('includes/admin_header.php'); ?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Avis des clients</h1>

    <div class="mb-4">
        <input type="text" id="filtre" placeholder="Filtrer par nom ou commentaire..." class="w-full p-2 border rounded">
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded shadow">
            <thead class="bg-gray-100 text-gray-700 text-left">
                <tr>
                    <th class="p-3">Date</th>
                    <th class="p-3">Client</th>
                    <th class="p-3">Livreur</th>
                    <th class="p-3">Note</th>
                    <th class="p-3">Commentaire</th>
                    <th class="p-3">Annonce</th>
                    <th class="p-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody id="reviewBody" class="text-gray-800"></tbody>
        </table>
    </div>
</div>

<!-- Tableau des avis clients → prestataires -->
<h2 class="text-2xl font-bold mt-10 mb-4">Avis clients sur les prestataires</h2>
<div class="mb-4">
    <input type="text" id="filtre-prestataire" placeholder="Filtrer par client ou commentaire..." class="form-control w-full p-2 border rounded">
</div>
<div class="overflow-x-auto">
    <table class="table table-striped table-bordered align-middle bg-white rounded shadow">
        <thead class="table-light">
            <tr>
                <th class="p-3">Date</th>
                <th class="p-3">Client</th>
                <th class="p-3">Prestataire</th>
                <th class="p-3">Note</th>
                <th class="p-3">Commentaire</th>
            </tr>
        </thead>
        <tbody id="reviewBodyPrestataire">
        </tbody>
    </table>
</div>

<script>
const BASE_API = "<?= BASE_API ?>";
let reviews = [];

async function chargerReviews() {
    try {
        const res = await fetch(`${BASE_API}/admin/reviews`, { credentials: 'include' });
        const data = await res.json();

        if (!res.ok || !data.success) throw new Error("Erreur API");

        reviews = data.reviews ?? [];
        afficherReviews();
    } catch (e) {
        console.error(e);
        document.getElementById('reviewBody').innerHTML = `
            <tr><td colspan="7" class="p-4 text-red-600">Erreur de chargement des avis</td></tr>
        `;
    }
}

function afficherReviews() {
    const filtre = document.getElementById('filtre').value.toLowerCase();
    const body = document.getElementById('reviewBody');

    const filtrées = reviews.filter(r =>
        r.reviewer.toLowerCase().includes(filtre) ||
        r.comment?.toLowerCase().includes(filtre)
    );

    body.innerHTML = filtrées.map(r => `
        <tr class="border-b">
            <td class="p-3 whitespace-nowrap">${r.created_at}</td>
            <td class="p-3 whitespace-nowrap">
                ${r.reviewer}<br>
                <small class="text-gray-500">Moy. données : ${r.avg_reviewer.toFixed(2)} ★</small>
            </td>
            <td class="p-3 whitespace-nowrap">
                <a href="edit_livreur.php?id=${r.deliverer_id}" class="text-blue-600 underline">
                    Voir profil
                </a><br>
                <small class="text-gray-500">Moy. reçues : ${r.avg_deliverer.toFixed(2)} ★</small>
            </td>
            <td class="p-3 whitespace-nowrap font-bold">
                ${'★'.repeat(r.rating)}${'☆'.repeat(5 - r.rating)}
            </td>
            <td class="p-3">${r.comment || '<em>Aucun</em>'}</td>
            <td class="p-3 whitespace-nowrap">
                <a href="edit_annonce.php?id=${r.request_id}" class="text-blue-600 underline">Voir</a>
            </td>
            <td class="p-3 text-right">
                <button onclick="supprimerReview(${r.review_id})" class="text-red-600 hover:underline">Supprimer</button>
            </td>
        </tr>
    `).join('');
}

async function supprimerReview(id) {
    if (!confirm("Supprimer cet avis ?")) return;

    const res = await fetch(`${BASE_API}/admin/reviews/${id}`, {
        method: 'DELETE',
        credentials: 'include'
    });

    if (res.ok) {
        reviews = reviews.filter(r => r.review_id !== id);
        afficherReviews();
    } else {
        alert("Erreur lors de la suppression.");
    }
}

document.getElementById('filtre').addEventListener('input', afficherReviews);
chargerReviews();

const bodyPrestataire = document.getElementById('reviewBodyPrestataire');
const filtrePrestataire = document.getElementById('filtre-prestataire');
let avisPrestataires = [];

async function chargerAvisPrestataires() {
    try {
        const res = await fetch(`${BASE_API}/admin/reviews/prestataires`, { credentials: 'include' });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error("Erreur API");
        avisPrestataires = data.avis ?? [];
        afficherAvisPrestataires();
    } catch (e) {
        bodyPrestataire.innerHTML = `<tr><td colspan="4" class="p-4 text-red-600">Erreur de chargement des avis</td></tr>`;
    }
}

function afficherAvisPrestataires() {
    const filtre = filtrePrestataire.value.toLowerCase();
    bodyPrestataire.innerHTML = avisPrestataires.filter(r =>
        (r.client_prenom + ' ' + r.client_nom).toLowerCase().includes(filtre) ||
        (r.commentaire ?? '').toLowerCase().includes(filtre) ||
        (r.prestataire_prenom + ' ' + r.prestataire_nom).toLowerCase().includes(filtre)
    ).map(r => `
        <tr>
            <td class="p-3">${r.created_at}</td>
            <td class="p-3">${r.client_prenom} ${r.client_nom}</td>
            <td class="p-3">${r.prestataire_prenom} ${r.prestataire_nom}</td>
            <td class="p-3"><span class="badge bg-success fs-6">★ ${r.note}/5</span></td>
            <td class="p-3">${r.commentaire ? r.commentaire.replace(/</g, '&lt;').replace(/>/g, '&gt;') : '<em>Aucun</em>'}</td>
        </tr>
    `).join('');
}

filtrePrestataire.addEventListener('input', afficherAvisPrestataires);
chargerAvisPrestataires();
</script>

<?php require_once('includes/admin_footer.php'); ?>
