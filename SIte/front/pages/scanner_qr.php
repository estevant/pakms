<?php
include_once('../includes/header.php');
include_once('../includes/api_path.php');

// Récupérer l'ID de l'annonce depuis l'URL
$requestId = $_GET['request_id'] ?? null;
if (!$requestId) {
    echo '<div class="max-w-md mx-auto mt-10 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">ID de livraison manquant</div>';
    include_once('../includes/footer.php');
    exit;
}
?>
<div class="max-w-md mx-auto mt-10 bg-white rounded shadow p-6 text-center">
    <h2 class="text-2xl font-bold mb-4">Scanner le QR code du livreur</h2>
    <p class="text-gray-600 mb-4">Scannez le QR code du livreur pour valider la livraison</p>
    
    <div id="qr-reader" class="mb-4"></div>
    
    <div id="livreur-info" class="hidden bg-green-50 border border-green-200 rounded p-4 mb-4">
        <h3 class="font-semibold text-green-800 mb-2">Livreur validé</h3>
        <p id="livreur-name" class="text-green-700"></p>
        <p id="livreur-phone" class="text-green-700"></p>
    </div>
    
    <button id="confirm-btn" class="hidden bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 mb-4">
        Confirmer la livraison
    </button>
    
    <div id="error-msg" class="text-red-600 text-center text-sm font-medium mt-4"></div>
    <div id="success-msg" class="text-green-600 text-center text-sm font-medium mt-4"></div>
    
    <button onclick="window.location.href='mes_annonces.php'" class="mt-4 bg-gray-600 text-white px-4 py-2 rounded">
        Retour à mes annonces
    </button>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
const API_BASE = "<?= BASE_API ?>";
const REQUEST_ID = <?= $requestId ?>;

let html5QrcodeScanner;
let currentQrCode = null;

function onScanSuccess(decodedText, decodedResult) {
    // Arrêter le scanner après un scan réussi
    html5QrcodeScanner.clear();
    
    currentQrCode = decodedText;
    validateLivreur(decodedText);
}

function onScanFailure(error) {
    // Gérer les erreurs de scan silencieusement
    console.warn(`Erreur de scan: ${error}`);
}

// Initialiser le scanner QR
html5QrcodeScanner = new Html5QrcodeScanner(
    "qr-reader",
    { fps: 10, qrbox: {width: 250, height: 250} },
    false
);
html5QrcodeScanner.render(onScanSuccess, onScanFailure);

async function validateLivreur(qrCode) {
    try {
        const res = await fetch(`${API_BASE}/qr/validate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                qr_code: qrCode,
                request_id: REQUEST_ID
            }),
            credentials: 'include'
        });
        
        const data = await res.json();
        
        if (data.success) {
            showLivreurInfo(data.livreur);
            document.getElementById('confirm-btn').classList.remove('hidden');
        } else {
            document.getElementById('error-msg').innerText = data.message || 'Erreur de validation';
        }
    } catch (e) {
        document.getElementById('error-msg').innerText = 'Erreur lors de la validation';
    }
}

function showLivreurInfo(livreur) {
    document.getElementById('livreur-name').innerText = `${livreur.first_name} ${livreur.last_name}`;
    document.getElementById('livreur-phone').innerText = livreur.phone || 'Téléphone non disponible';
    document.getElementById('livreur-info').classList.remove('hidden');
}

document.getElementById('confirm-btn').addEventListener('click', async function() {
    if (!currentQrCode) {
        document.getElementById('error-msg').innerText = 'QR code non scanné';
        return;
    }
    
    try {
        const res = await fetch(`${API_BASE}/qr/confirm-delivery`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                request_id: REQUEST_ID,
                qr_code: currentQrCode
            }),
            credentials: 'include'
        });
        
        const data = await res.json();
        
        if (data.success) {
            document.getElementById('success-msg').innerText = 'Livraison confirmée avec succès!';
            document.getElementById('confirm-btn').disabled = true;
            document.getElementById('confirm-btn').innerText = 'Livraison confirmée';
            document.getElementById('confirm-btn').classList.remove('bg-green-600', 'hover:bg-green-700');
            document.getElementById('confirm-btn').classList.add('bg-gray-400');
            
            // Rediriger après 3 secondes
            setTimeout(() => {
                window.location.href = 'mes_annonces.php';
            }, 3000);
        } else {
            document.getElementById('error-msg').innerText = data.message || 'Erreur de confirmation';
        }
    } catch (e) {
        document.getElementById('error-msg').innerText = 'Erreur lors de la confirmation';
    }
});
</script>

<?php include_once('../includes/footer.php'); ?> 