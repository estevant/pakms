<?php include_once('includes/admin_header.php'); ?>
<?php include_once('includes/api_path.php'); ?>

<!-- Bootstrap 5 CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-body">
                    <button onclick="history.back()" class="btn btn-link mb-3 p-0"><i class="bi bi-arrow-left"></i> Retour</button>
                    <h1 class="h4 fw-bold mb-4">Modifier un prestataire</h1>
                    <div id="loader" class="text-center text-muted mb-3">Chargement…</div>
                    <div id="error-msg" class="alert alert-danger d-none"></div>
                    <form id="form-edit-presta" class="d-none">
                        <div class="mb-3">
                            <label class="form-label">Prénom</label>
                            <input type="text" name="prenom" id="input-prenom" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" name="nom" id="input-nom" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="input-email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="phone" id="input-phone" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Enregistrer</button>
                        <div id="edit-presta-msg" class="mt-3 text-center small"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const API_BASE = "<?php echo BASE_API; ?>";
const params = new URLSearchParams(window.location.search);
const id = params.get('id');
const errorMsg = document.getElementById('error-msg');
const form = document.getElementById('form-edit-presta');
const msg = document.getElementById('edit-presta-msg');
const loader = document.getElementById('loader');

async function chargerInfos() {
    if (!id) {
        loader.classList.add('d-none');
        errorMsg.textContent = "ID manquant";
        errorMsg.classList.remove('d-none');
        return;
    }
    try {
        // Utilise la route dédiée à l'édition
        const res = await fetch(`${API_BASE}/admin/prestataires/${id}/edit`, { credentials: 'include' });
        const data = await res.json();
        loader.classList.add('d-none');
        if (!data.success || !data.prestataire) {
            errorMsg.textContent = "Prestataire introuvable.";
            errorMsg.classList.remove('d-none');
            return;
        }
        const u = data.prestataire;
        document.getElementById('input-prenom').value = u.prenom;
        document.getElementById('input-nom').value = u.nom;
        document.getElementById('input-email').value = u.email;
        document.getElementById('input-phone').value = u.phone ?? '';
        form.classList.remove('d-none');
    } catch (err) {
        loader.classList.add('d-none');
        errorMsg.textContent = "Erreur de chargement.";
        errorMsg.classList.remove('d-none');
    }
}

form.onsubmit = async function(e) {
    e.preventDefault();
    msg.textContent = '';
    msg.className = 'mt-3 text-center small';
    const prenom = form.prenom.value;
    const nom = form.nom.value;
    const email = form.email.value;
    try {
        const res = await fetch(`${API_BASE}/admin/prestataires/${id}`, {
            method: 'PUT',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prenom, nom, email })
        });
        const data = await res.json();
        if (data.success) {
            msg.textContent = 'Modifié avec succès !';
            msg.className = 'mt-3 text-center text-success small';
        } else {
            msg.textContent = data.message || 'Erreur.';
            msg.className = 'mt-3 text-center text-danger small';
        }
    } catch (err) {
        msg.textContent = 'Erreur réseau.';
        msg.className = 'mt-3 text-center text-danger small';
    }
};

// Ajout des icônes Bootstrap Icons
const biLink = document.createElement('link');
biLink.rel = 'stylesheet';
biLink.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css';
document.head.appendChild(biLink);

chargerInfos();
</script>

<?php include_once('includes/admin_footer.php'); ?>
