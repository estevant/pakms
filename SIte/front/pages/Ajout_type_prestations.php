<?php 
include_once('../includes/header.php');
// Suppression de l'inclusion redondante de api_path.php car déjà incluse dans header.php
?>

<!-- Section supprimée : Demander un nouveau type de prestation -->

<div class="mt-10">
    <h3 class="text-lg font-semibold mb-4 text-blue-700 text-center">Types de prestations existants</h3>
    <p class="text-gray-600 mb-4 text-center">Avant de faire une demande, vérifiez que le type que vous souhaitez n'existe pas déjà :</p>
    <div id="existing-types" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
</div>

<script>
const API_BASE = "<?= BASE_API ?>";

async function loadExistingTypes() {
    try {
        const res = await fetch(`${API_BASE}/servicetypes`, { credentials: 'include' });
        const data = await res.json();
        const container = document.getElementById('existing-types');
        
        if (data.data && data.data.length > 0) {
            container.innerHTML = data.data.map(type => `
                <div class="bg-gray-50 p-3 rounded border text-center">
                    <h4 class="font-semibold text-gray-800">${type.name}</h4>
                    <p class="text-sm text-gray-600">${type.description || 'Aucune description'}</p>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p class="text-gray-500">Aucun type de prestation disponible</p>';
        }
    } catch (error) {
        console.error('Erreur lors du chargement des types existants:', error);
        document.getElementById('existing-types').innerHTML = '<p class="text-red-500">Erreur de chargement</p>';
    }
}

loadExistingTypes();
</script>

<?php include_once('../includes/footer.php'); ?>
