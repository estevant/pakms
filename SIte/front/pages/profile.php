<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?>

<div id="unauth" class="text-red-600 text-center mt-12 hidden">Accès refusé.</div>

<form id="profile-form" enctype="multipart/form-data"
      class="hidden max-w-lg mx-auto py-10 px-4 space-y-4">

  <h1 class="text-2xl font-bold text-center mb-6">👤 Mon profil</h1>

  <div class="grid sm:grid-cols-2 gap-4">
      <input id="first_name"  name="first_name"  placeholder="Prénom"
             class="border rounded px-3 py-2">
      <input id="last_name"   name="last_name"   placeholder="Nom"
             class="border rounded px-3 py-2">
      <input id="email" name="email" placeholder="Email" disabled
             class="border bg-gray-100 rounded px-3 py-2">

      <input id="phone" name="phone" placeholder="Téléphone"
             class="border rounded px-3 py-2">
      <input id="preferred_city" name="preferred_city"
             placeholder="Ville préférée"
             class="border rounded px-3 py-2">

      <input id="business_name"  name="business_name"
             placeholder="Raison sociale"
             class="border rounded px-3 py-2 role-seller hidden"
             disabled>
      <input id="business_address" name="business_address"
             placeholder="Adresse entreprise"
             class="border rounded px-3 py-2 col-span-2 role-seller hidden"
             disabled>

      <input id="sector" name="sector" placeholder="Secteur"
             class="border rounded px-3 py-2 role-provider role-seller hidden"
             disabled>

      <input type="file" id="profile_picture" name="profile_picture"
             accept="image/*" class="col-span-2">
  </div>

  <textarea id="description" name="description" rows="3"
            placeholder="Description"
            class="border rounded px-3 py-2 w-full role-provider role-seller hidden"
            disabled></textarea>

  <div class="border-t pt-4">
      <h2 class="font-semibold mb-1">Changer de mot de passe</h2>
      <input type="password" id="password" name="password"
             placeholder="Nouveau mot de passe"
             class="border rounded px-3 py-2 w-full">
      <input type="password" id="password_confirmation"
             name="password_confirmation"
             placeholder="Confirmer le mot de passe"
             class="border rounded px-3 py-2 w-full mt-2">
  </div>

  <button id="save-btn"
          class="bg-blue-600 text-white px-5 py-2 rounded">Enregistrer</button>

  <div id="msg" class="text-sm mt-4"></div>
</form>

<script>
const API_BASE = "<?= BASE_API ?>";

function showRole(sel)  {
    document.querySelectorAll(sel).forEach(el => {
        el.classList.remove('hidden');
        el.disabled = false;
    });
}
function hideRole(sel)  {
    document.querySelectorAll(sel).forEach(el => {
        el.classList.add('hidden');
        el.disabled = true;
    });
}

(async function init () {
    try {
        const res = await fetch(`${API_BASE}/profile`, { credentials:'include' });
        if (!res.ok) {
            console.error('[profile] HTTP', res.status, await res.text());
            document.getElementById('unauth').classList.remove('hidden');
            return;
        }
        const json = await res.json();
        if (!json.success) {
            document.getElementById('unauth').classList.remove('hidden');
            return;
        }

        const form = document.getElementById('profile-form');
        form.classList.remove('hidden');

        const roles = json.roles || [];
        hideRole('.role-seller');
        hideRole('.role-provider');
        if (roles.includes('Seller'))          showRole('.role-seller');
        if (roles.includes('ServiceProvider')) showRole('.role-provider');

        const p = json.profile;
        for (const [k,v] of Object.entries(p)) {
            const el = document.getElementById(k);
            if (el && el.type !== 'file') el.value = v ?? '';
        }

    } catch (err) {
        console.error('Fetch error:', err);
        document.getElementById('unauth').classList.remove('hidden');
    }
})();

document.getElementById('profile-form').addEventListener('submit', async e => {
    e.preventDefault();
    const msg = document.getElementById('msg');
    const btn = document.getElementById('save-btn');
    msg.textContent = ''; msg.className = 'text-sm mt-4';
    btn.disabled = true;

    const formData = new FormData(e.target);

    try {
        const res = await fetch(`${API_BASE}/profile/update`, {
            method: 'POST',
            credentials: 'include',
            body: formData
        });

        let success = false, message = `HTTP ${res.status}`;
        if (res.ok) {
            try {
                const json = await res.json();
                success = !!json.success;
                message = json.message ?? message;
            } catch {
                message = 'Réponse invalide du serveur.';
            }
        } else {
            message = (await res.text()).slice(0,200) || message;
        }

        msg.textContent = message;
        msg.classList.add(success ? 'text-green-600' : 'text-red-600');

    } catch (err) {
        console.error('Fetch error:', err);
        msg.textContent = 'Erreur réseau.'; msg.classList.add('text-red-600');
    } finally {
        btn.disabled = false;
    }
});
</script>

<?php include_once('../includes/footer.php'); ?>
