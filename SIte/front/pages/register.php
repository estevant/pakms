<?php  
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

include_once('../includes/header.php');
include_once('../includes/api_path.php');
?> <h2 class="text-2xl font-bold mb-4">Inscription</h2>
<form id="register-form" enctype="multipart/form-data" class="space-y-4 max-w-lg mx-auto">
    <label class="block">Rôle : <select name="role" id="role-select" required class="input w-full">
            <option value="">-- Choisir un rôle --</option>
            <option value="Customer">Client</option>
            <option value="Seller">Commerçant</option>
            <option value="ServiceProvider">Prestataire</option>
            <option value="Deliverer">Livreur</option>
        </select>
    </label>
    <div id="extra-fields" class="space-y-2"></div>
    <label class="block">Nom : <input type="text" name="last_name" required class="input w-full">
    </label>
    <label class="block">Prénom : <input type="text" name="first_name" required class="input w-full">
    </label>
    <label class="block">Email : <input type="email" name="email" required class="input w-full">
    </label>
    <label class="block">Mot de passe : <input type="password" name="password" required class="input w-full">
    </label>
    <label class="block">Phone : <input type="text" name="phone" class="input w-full">
    </label>
    <label class="block">Photo de profil : <input type="file" name="profile_picture" accept=".jpg,.jpeg,.png"
            class="input w-full">
    </label>
    <label class="block" id="piece-identite-label">
        <span id="piece-identite-text">Pièce d'identité :</span>
        <input type="file" name="piece_identite" id="piece-identite" accept=".pdf,.jpg,.jpeg,.png" class="input w-full">
    </label>
    <div id="type-identite-container"></div>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">S'inscrire</button>
</form>
<div id="register-response" class="mt-4 text-sm font-medium text-center"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const API_BASE = "<?= BASE_API ?>";
    const extraFields = document.getElementById('extra-fields');
    const roleSelect = document.getElementById('role-select');
    let prestations = [];

    function updateFormFields(role) {
        extraFields.innerHTML = '';
        
        const pieceIdentiteInput = document.getElementById('piece-identite');
        const pieceIdentiteText = document.getElementById('piece-identite-text');
        const typeIdentiteContainer = document.getElementById('type-identite-container');
        
        if (role === 'Customer') {
            pieceIdentiteInput.removeAttribute('required');
            pieceIdentiteText.textContent = 'Pièce d\'identité (facultatif) :';
            typeIdentiteContainer.innerHTML = '';
        } else if (role && role !== '') {
            pieceIdentiteInput.setAttribute('required', 'required');
            pieceIdentiteText.textContent = 'Pièce d\'identité (obligatoire) :';
            if (role === 'ServiceProvider') {
                typeIdentiteContainer.innerHTML = `
                    <label class="block">Type de pièce d'identité :
                        <select name="type" id="type-identite-select" required class="input w-full">
                            <option value="CNI">Carte Nationale d'Identité</option>
                            <option value="Passeport">Passeport</option>
                        </select>
                    </label>
                `;
            } else {
                typeIdentiteContainer.innerHTML = '';
            }
        } else {
            pieceIdentiteInput.removeAttribute('required');
            pieceIdentiteText.textContent = 'Pièce d\'identité :';
            typeIdentiteContainer.innerHTML = '';
        }

        if (role === 'Seller') {
            extraFields.innerHTML = `
                <label class="block">Raison sociale :
                    <input type="text" name="business_name" required class="input w-full">
                </label>
                <label class="block">Adresse commerciale :
                    <input type="text" name="business_address" required class="input w-full">
                </label>
                <label class="block">Date de début de contrat :
                    <input type="date" name="start_date" required class="input w-full">
                </label>
                <label class="block">Date de fin de contrat :
                    <input type="date" name="end_date" required class="input w-full">
                </label>
                <label class="block">Conditions (facultatif) :
                    <textarea name="terms" class="input w-full"></textarea>
                </label>`;
        } else if (role === 'ServiceProvider') {
            let options = prestations.map(p =>
                `<option value="${p.service_type_id}">${p.name}</option>`).join('');
            extraFields.innerHTML = `
                <label class="block">Domaine d'activité :
                    <select name="service_type" id="service-type-select" required class="input w-full">
                        <option value="">-- Choisir une prestation --</option>
                        ${options}
                    </select>
                </label>
                <label class="block">Description :
                    <textarea name="description" required class="input w-full"></textarea>
                </label>`;
        } else if (role === 'Deliverer') {
            extraFields.innerHTML = `
                <label class="block">Spécialité :
                    <input type="text" name="specialty" required class="input w-full">
                </label>`;
        }
    }

    roleSelect.addEventListener('change', (e) => {
        updateFormFields(e.target.value);
    });

    // On charge les prestations depuis l'API
    fetch(`${API_BASE}/servicetypes`, {
        method: 'GET',
        credentials: 'include',
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        prestations = data.data || [];
        // Si un rôle était déjà sélectionné, on met à jour le formulaire
        if (roleSelect.value) {
            updateFormFields(roleSelect.value);
        }
    })
    .catch(e => console.error("Erreur lors du chargement des prestations :", e));

    // Gestion de la soumission du formulaire
    document.getElementById('register-form').addEventListener('submit', async e => {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const res = await fetch(`${API_BASE}/register`, {
                method: 'POST',
                body: formData,
                credentials: 'include',
                headers: { 'Accept': 'application/json' }
            });
            const result = await res.json();
            const msg = document.getElementById('register-response');
            msg.innerText = result.message || (result.success ? 'Inscription réussie.' : 'Erreur lors de l\'inscription.');
            msg.className = result.success ? 'text-green-600' : 'text-red-600';
            if (result.success) {
                setTimeout(() => location.href = 'login.php', 1500);
            }
        } catch (err) {
            console.error(err);
            const msg = document.getElementById('register-response');
            msg.innerText = 'Erreur lors de l\'envoi.';
            msg.className = 'text-red-600';
        }
    });
});
</script> <?php include_once('../includes/footer.php'); ?>