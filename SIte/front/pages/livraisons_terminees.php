<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');
?>

<div id="unauthorized" class="text-red-600 text-center mt-12 hidden">
    Accès refusé. Réservé aux livreurs.
</div>

<div id="page-content" class="hidden max-w-5xl mx-auto py-10 px-6">
    <h2 class="text-2xl font-bold mb-6">✅ Livraisons terminées</h2>
    <div id="liste-livraisons-terminees" class="space-y-6"></div>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";

async function verifierAcces(){
    try{
        const res  = await fetch(`${API_BASE}/get_session`,{credentials:'include'});
        const data = await res.json();
        if(!data.user || !data.user.roles.includes('Deliverer')){
            document.getElementById('unauthorized').classList.remove('hidden');
        }else{
            document.getElementById('page-content').classList.remove('hidden');
            chargerLivraisonsTerminees();
        }
    }catch(e){
        console.error(e);
        document.getElementById('unauthorized').classList.remove('hidden');
    }
}

async function chargerLivraisonsTerminees(){
    try{
        const res  = await fetch(`${API_BASE}/livreur/terminees`,{credentials:'include'});
        const data = await res.json();
        const container = document.getElementById('liste-livraisons-terminees');
        container.innerHTML = '';

        if(data.success && data.livraisons.length){
            data.livraisons.forEach(l=>{
                container.insertAdjacentHTML('beforeend',`
                    <div class="border border-gray-300 bg-white rounded p-4 shadow">
                        <p><strong>Départ :</strong>  ${l.depart} (${l.depart_code})</p>
                        <p><strong>Arrivée :</strong> ${l.arrivee} (${l.arrivee_code})</p>
                        ${l.box_label ? `
                            <p><strong>Box :</strong> ${l.box_label} (${l.box_city})</p>` : ''}
                        <p><strong>Statut :</strong> ✅ ${l.statut}</p>
                        ${l.partial ? `<p class="text-yellow-600 font-semibold">⚠️ Livraison partielle</p>` : ''}
                        <div class="mt-4 flex flex-wrap gap-2">
<a href="tchat.php?id=${l.id_annonce}"
   class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
   💬 Voir le tchat
</a>
<a href="suivi_livreur.php?id=${l.id_annonce}"
   class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
   📍 Suivi colis
</a>

                        </div>
                    </div>
                `);
            });
        }else{
            container.innerHTML =
               "<p class='text-gray-500'>Aucune livraison terminée.</p>";
        }
    }catch(e){
        console.error(e);
        document.getElementById('liste-livraisons-terminees').innerHTML =
            "<p class='text-red-600'>Erreur lors du chargement des livraisons terminées.</p>";
    }
}

document.addEventListener('DOMContentLoaded',verifierAcces);
</script>

<style>
.input-field{
    @apply border border-gray-300 rounded px-3 py-2 w-full;
}
</style>

<?php include_once('../includes/footer.php'); ?>